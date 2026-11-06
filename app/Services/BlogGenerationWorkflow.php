<?php

namespace App\Services;

use App\Jobs\GenerateBlogDraft;
use App\Models\BlogGenerationRun;
use App\Models\BlogSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class BlogGenerationWorkflow
{
    public function dispatchScheduled(): ?BlogGenerationRun
    {
        if (! config('blog.generation_enabled')) {
            return null;
        }

        $timezone = (string) config('blog.timezone', 'Africa/Lagos');
        $now = CarbonImmutable::now($timezone);
        $dailyTime = (string) BlogSetting::getValue('daily_time', '04:00');
        if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $dailyTime)
            || $now->format('H:i') < $dailyTime) {
            return null;
        }

        return $this->dispatchOnceForDay($now);
    }

    public function dispatchManual(): BlogGenerationRun
    {
        if (! config('blog.generation_enabled')) {
            throw new RuntimeException('Blog generation is disabled. Enable BLOG_GENERATION_ENABLED after deployment configuration is verified.');
        }

        $run = BlogGenerationRun::query()->create([
            'run_key' => 'manual:'.Str::uuid(),
            'status' => BlogGenerationRun::STATUS_QUEUED,
            'provider' => (string) config('blog.ai_provider'),
        ]);
        GenerateBlogDraft::dispatch($run->id)->onConnection('blog')->onQueue('blog');
        Log::info('A manual blog draft generation run was queued.', [
            'run_id' => $run->id,
            'queue' => 'blog',
            'connection' => 'blog',
            'provider' => $run->provider,
        ]);

        return $run;
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

        $run->update([
            'status' => BlogGenerationRun::STATUS_QUEUED,
            'error_message' => null,
            'finished_at' => null,
        ]);
        GenerateBlogDraft::dispatch($run->id)->onConnection('blog')->onQueue('blog');

        return $run->fresh();
    }

    private function dispatchOnceForDay(CarbonImmutable $now): BlogGenerationRun
    {
        $date = $now->toDateString();
        $run = BlogGenerationRun::query()->firstOrCreate(
            ['run_key' => 'daily:'.$date],
            [
                'scheduled_for' => $date,
                'status' => BlogGenerationRun::STATUS_QUEUED,
                'provider' => (string) config('blog.ai_provider'),
            ],
        );

        if ($run->wasRecentlyCreated) {
            GenerateBlogDraft::dispatch($run->id)->onConnection('blog')->onQueue('blog');
        }

        return $run;
    }
}
