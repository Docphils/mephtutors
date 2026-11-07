<?php

namespace App\Services;

use App\Jobs\GenerateBlogDraft;
use App\Jobs\GenerateBlogTopicSuggestions;
use App\Models\BlogGenerationRun;
use App\Models\BlogSetting;
use App\Models\BlogTopic;
use App\Models\BlogTopicSuggestionBatch;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BlogGenerationWorkflow
{
    public function dispatchScheduled(): ?BlogGenerationRun
    {
        if (! config('blog.generation_enabled')) {
            return null;
        }

        $this->releaseAwaitingRuns();
        $this->queueTopicSuggestionsIfNeeded();
        $timezone = (string) config('blog.timezone', 'Africa/Lagos');
        $now = CarbonImmutable::now($timezone);
        $dailyTime = (string) BlogSetting::getValue('daily_time', '04:00');
        if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $dailyTime)
            || $now->format('H:i') < $dailyTime) {
            return null;
        }

        return $this->dispatchOnceForDay($now);
    }

    public function dispatchManual(?int $wordTarget = null): BlogGenerationRun
    {
        if (! config('blog.generation_enabled')) {
            throw new RuntimeException('Blog generation is disabled. Enable BLOG_GENERATION_ENABLED after deployment configuration is verified.');
        }

        $this->releaseAwaitingRuns();
        $run = DB::transaction(fn (): BlogGenerationRun => $this->createRun(
            $this->claimNextApprovedTopic(),
            $wordTarget,
            auth()->id(),
        ));
        $topic = $run->topic;
        if ($topic === null) {
            $this->requestTopicSuggestions(false);
            Log::info('A manual blog draft is waiting for an approved topic.', [
                'run_id' => $run->id,
                'admin_id' => auth()->id(),
            ]);

            return $run;
        }

        $this->queueRun($run);
        $this->queueTopicSuggestionsIfNeeded();
        Log::info('A manual blog draft generation run was queued.', [
            'run_id' => $run->id,
            'queue' => 'blog',
            'connection' => 'blog',
            'provider' => $run->provider,
        ]);

        return $run;
    }

    public function dispatchManualForTopic(int $topicId, ?int $wordTarget = null): BlogGenerationRun
    {
        if (! config('blog.generation_enabled')) {
            throw new RuntimeException('Blog generation is disabled. Enable BLOG_GENERATION_ENABLED after deployment configuration is verified.');
        }

        $topic = BlogTopic::query()->whereKey($topicId)->where('status', BlogTopic::STATUS_APPROVED)->firstOrFail();
        $run = $this->createRun($topic, $wordTarget, auth()->id());
        $this->queueRun($run);
        $this->queueTopicSuggestionsIfNeeded();

        return $run;
    }

    public function requestTopicSuggestions(bool $force = true): ?BlogTopicSuggestionBatch
    {
        if (! config('blog.generation_enabled')) {
            throw new RuntimeException('AI topic suggestions are disabled. Enable BLOG_GENERATION_ENABLED after provider configuration is verified.');
        }

        $batch = DB::transaction(function () use ($force): ?BlogTopicSuggestionBatch {
            $active = BlogTopicSuggestionBatch::query()
                ->whereIn('status', [BlogTopicSuggestionBatch::STATUS_QUEUED, BlogTopicSuggestionBatch::STATUS_RUNNING])
                ->latest('id')
                ->lockForUpdate()
                ->first();
            if ($active !== null) {
                return $active;
            }

            if (! $force && BlogTopic::query()->where('status', BlogTopic::STATUS_PENDING)->exists()) {
                return null;
            }

            return BlogTopicSuggestionBatch::query()->create([
                'batch_key' => ($force ? 'manual:' : 'auto:').Str::uuid(),
                'status' => BlogTopicSuggestionBatch::STATUS_QUEUED,
                'requested_count' => (int) BlogSetting::getValue('topic_batch_size', config('blog.default_topic_batch_size', 10)),
                'requested_by' => auth()->id(),
            ]);
        });

        if ($batch?->wasRecentlyCreated) {
            try {
                GenerateBlogTopicSuggestions::dispatch($batch->id)->onConnection('blog')->onQueue('blog');
            } catch (Throwable $exception) {
                $batch->update([
                    'status' => BlogTopicSuggestionBatch::STATUS_FAILED,
                    'error_message' => mb_substr($exception->getMessage(), 0, 1000),
                    'finished_at' => now(),
                ]);
                Log::error('A blog topic suggestion batch could not be added to the blog queue.', [
                    'batch_id' => $batch->id,
                    'exception' => $exception,
                ]);

                throw new RuntimeException('The topic suggestions could not be queued. Check the blog queue configuration and try again.', 0, $exception);
            }
            Log::info('A blog topic suggestion batch was queued.', [
                'batch_id' => $batch->id,
                'requested_by' => auth()->id(),
                'requested_count' => $batch->requested_count,
            ]);
        }

        return $batch;
    }

    public function approveTopic(int $topicId): void
    {
        $runToQueue = null;
        DB::transaction(function () use ($topicId, &$runToQueue): void {
            $topic = BlogTopic::query()->lockForUpdate()->findOrFail($topicId);
            if ($topic->status !== BlogTopic::STATUS_PENDING) {
                throw new RuntimeException('Only pending topic suggestions can be approved.');
            }

            $topic->update([
                'status' => BlogTopic::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $waitingRun = BlogGenerationRun::query()
                ->where('status', BlogGenerationRun::STATUS_AWAITING_TOPIC)
                ->whereNull('blog_topic_id')
                ->oldest('id')
                ->lockForUpdate()
                ->first();
            if ($waitingRun !== null && config('blog.generation_enabled')) {
                $waitingRun->update([
                    'blog_topic_id' => $topic->id,
                    'status' => BlogGenerationRun::STATUS_QUEUED,
                ]);
                $runToQueue = $waitingRun;
            }
        });

        if ($runToQueue !== null) {
            $this->queueRun($runToQueue);
        }
        if (config('blog.generation_enabled')) {
            $this->releaseAwaitingRuns();
        }
        $this->queueTopicSuggestionsIfNeeded();
    }

    public function rejectTopic(int $topicId): void
    {
        $topic = BlogTopic::query()->findOrFail($topicId);
        if ($topic->status !== BlogTopic::STATUS_PENDING) {
            throw new RuntimeException('Only pending topic suggestions can be rejected.');
        }
        $topic->update(['status' => BlogTopic::STATUS_REJECTED]);
    }

    public function retryFailed(int $runId): BlogGenerationRun
    {
        if (! config('blog.generation_enabled')) {
            throw new RuntimeException('Blog generation is disabled. Enable BLOG_GENERATION_ENABLED after deployment configuration is verified.');
        }

        $run = BlogGenerationRun::query()->findOrFail($runId);
        if ($run->status !== BlogGenerationRun::STATUS_FAILED || $run->blog_post_id !== null) {
            throw new RuntimeException('Only a failed run without a saved draft can be retried.');
        }
        if ($run->blog_topic_id === null || ! $run->topic()->where('status', BlogTopic::STATUS_APPROVED)->exists()) {
            throw new RuntimeException('This run has no approved topic to retry. Queue a new draft from an approved topic in Blog Studio.');
        }

        $run->update([
            'status' => BlogGenerationRun::STATUS_QUEUED,
            'error_message' => null,
            'finished_at' => null,
        ]);
        $this->queueRun($run);

        return $run->fresh();
    }

    private function dispatchOnceForDay(CarbonImmutable $now): BlogGenerationRun
    {
        $date = $now->toDateString();
        [$run, $topicWasAssigned] = DB::transaction(function () use ($date): array {
            $existing = BlogGenerationRun::query()->where('run_key', 'daily:'.$date)->lockForUpdate()->first();
            if ($existing !== null) {
                return [$existing, false];
            }

            $topic = $this->claimNextApprovedTopic();
            $run = BlogGenerationRun::query()->firstOrCreate(['run_key' => 'daily:'.$date], [
                'scheduled_for' => $date,
                'status' => $topic ? BlogGenerationRun::STATUS_QUEUED : BlogGenerationRun::STATUS_AWAITING_TOPIC,
                'blog_topic_id' => $topic?->id,
                'word_target' => $this->defaultWordTarget(),
                'author_id' => $this->defaultAuthorId(),
                'provider' => (string) config('blog.ai_provider'),
            ]);

            return [$run, $run->wasRecentlyCreated && $topic !== null];
        });

        if ($topicWasAssigned) {
            $this->queueRun($run);
        }

        return $run;
    }

    private function createRun(?BlogTopic $topic, ?int $wordTarget, ?int $authorId): BlogGenerationRun
    {
        return BlogGenerationRun::query()->create([
            'run_key' => 'manual:'.Str::uuid(),
            'status' => $topic ? BlogGenerationRun::STATUS_QUEUED : BlogGenerationRun::STATUS_AWAITING_TOPIC,
            'blog_topic_id' => $topic?->id,
            'word_target' => $this->wordTarget($wordTarget),
            'author_id' => $authorId,
            'provider' => (string) config('blog.ai_provider'),
        ]);
    }

    private function queueRun(BlogGenerationRun $run): void
    {
        try {
            GenerateBlogDraft::dispatch($run->id)->onConnection('blog')->onQueue('blog');
        } catch (Throwable $exception) {
            $run->refresh()->update([
                'status' => BlogGenerationRun::STATUS_FAILED,
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
                'finished_at' => now(),
            ]);
            Log::error('A blog draft run could not be added to the blog queue.', [
                'run_id' => $run->id,
                'exception' => $exception,
            ]);

            throw new RuntimeException('The draft could not be queued. It is marked failed; check the blog queue configuration and retry it.', 0, $exception);
        }
    }

    private function claimNextApprovedTopic(): ?BlogTopic
    {
        return BlogTopic::query()
            ->where('status', BlogTopic::STATUS_APPROVED)
            ->whereDoesntHave('generationRuns')
            ->orderBy('approved_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    private function releaseAwaitingRuns(): void
    {
        $runIds = DB::transaction(function (): array {
            $waitingRuns = BlogGenerationRun::query()
                ->where('status', BlogGenerationRun::STATUS_AWAITING_TOPIC)
                ->whereNull('blog_topic_id')
                ->oldest('id')
                ->lockForUpdate()
                ->get();
            $queuedRunIds = [];

            foreach ($waitingRuns as $waitingRun) {
                $topic = $this->claimNextApprovedTopic();
                if ($topic === null) {
                    break;
                }

                $waitingRun->update([
                    'blog_topic_id' => $topic->id,
                    'status' => BlogGenerationRun::STATUS_QUEUED,
                ]);
                $queuedRunIds[] = $waitingRun->id;
            }

            return $queuedRunIds;
        });

        foreach ($runIds as $runId) {
            $this->queueRun(BlogGenerationRun::query()->findOrFail($runId));
        }
    }

    private function queueTopicSuggestionsIfNeeded(): void
    {
        $lowWatermark = (int) BlogSetting::getValue('topic_low_watermark', config('blog.default_topic_low_watermark', 3));
        if ($lowWatermark < 1) {
            return;
        }

        $available = BlogTopic::query()
            ->where('status', BlogTopic::STATUS_APPROVED)
            ->whereDoesntHave('generationRuns')
            ->count();
        if ($available <= $lowWatermark) {
            try {
                $this->requestTopicSuggestions(false);
            } catch (Throwable $exception) {
                Log::error('Automatic blog topic suggestions could not be queued.', [
                    'available_topics' => $available,
                    'exception' => $exception,
                ]);
            }
        }
    }

    private function defaultWordTarget(): int
    {
        return $this->wordTarget(null);
    }

    private function wordTarget(?int $wordTarget): int
    {
        $target = $wordTarget ?? (int) BlogSetting::getValue(
            'content_word_target',
            config('blog.default_content_word_target', 700)
        );
        if ($target < 300 || $target > 2000) {
            throw new RuntimeException('Article word targets must be between 300 and 2,000 words.');
        }

        return $target;
    }

    private function defaultAuthorId(): int
    {
        $id = (int) BlogSetting::getValue('default_author_id', 0);
        $author = User::query()
            ->whereKey($id)
            ->where('role', 'admin')
            ->whereNotNull('email_verified_at')
            ->first();
        if ($author === null) {
            throw new RuntimeException('Choose a verified default blog author in Blog Settings before enabling scheduled generation.');
        }

        return $author->id;
    }
}
