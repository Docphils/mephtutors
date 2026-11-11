<?php

namespace App\Livewire\Admin;

use App\Models\BlogFacebookShare;
use App\Models\BlogGenerationRun;
use App\Models\BlogPost;
use App\Models\BlogSetting;
use App\Models\BlogTopic;
use App\Models\BlogTopicSuggestionBatch;
use App\Services\BlogFacebookWorkflow;
use App\Services\BlogGenerationWorkflow;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Throwable;

#[Layout('layouts.app')]
#[Title('Blog Studio | MephEd')]
class BlogStudio extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $operationsTab = 'articles';

    public int $articlesPerPage = 10;

    public int $activityPerPage = 10;

    public int $topicsPerPage = 10;

    public ?string $statusMessage = null;

    public string $statusType = 'success';

    public string $statusHeading = 'Status update';

    public string $wordTarget = '700';

    public function mount(): void
    {
        Gate::authorize('Admin');
        $this->wordTarget = (string) BlogSetting::getValue('content_word_target', config('blog.default_content_word_target', 700));
    }

    public function publish(int $postId, BlogFacebookWorkflow $facebook): void
    {
        Gate::authorize('Admin');
        $post = BlogPost::query()->findOrFail($postId);
        abort_if(trim($post->title) === '' || trim($post->body) === '', 422, 'An article needs a title and body before publication.');

        $post->update([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => $post->published_at?->isPast() ? $post->published_at : now(),
            'editor_id' => auth()->id(),
        ]);
        $facebook->queueAfterPublication($post->fresh());
        $this->notify('success', 'Article published.', 'Article published');
    }

    public function unpublish(int $postId): void
    {
        Gate::authorize('Admin');
        DB::transaction(function () use ($postId): void {
            $post = BlogPost::query()->lockForUpdate()->findOrFail($postId);
            $share = $post->facebookShare()->lockForUpdate()->first();
            abort_if(
                $share?->status === BlogFacebookShare::STATUS_PUBLISHING,
                409,
                'Facebook is currently processing this post. Wait for its status to settle before unpublishing.',
            );
            $post->update([
                'status' => BlogPost::STATUS_DRAFT,
                'published_at' => null,
                'editor_id' => auth()->id(),
            ]);
            if ($share?->status === BlogFacebookShare::STATUS_QUEUED) {
                $share->update([
                    'status' => BlogFacebookShare::STATUS_CANCELLED,
                    'error_message' => 'The article was unpublished before Facebook delivery.',
                ]);
            }
        });
        $this->notify('success', 'Article returned to drafts.', 'Article returned to drafts');
    }

    public function retryFacebook(int $postId, BlogFacebookWorkflow $facebook): void
    {
        Gate::authorize('Admin');
        abort_unless(config('blog.facebook_enabled'), 403, 'Facebook API publishing is disabled.');

        $post = BlogPost::query()->findOrFail($postId);
        abort_unless($post->status === BlogPost::STATUS_PUBLISHED, 422, 'Only published articles can be shared.');
        $share = $post->facebookShare;
        abort_unless(
            $share === null || in_array($share->status, [
                BlogFacebookShare::STATUS_FAILED,
                BlogFacebookShare::STATUS_MANUAL,
                BlogFacebookShare::STATUS_CANCELLED,
            ], true),
            409,
            'This Facebook share is already active, published, or requires manual verification.',
        );

        $facebook->queueAfterPublication($post);
        $this->notify('success', 'Facebook publishing was queued. Check its status before using the manual fallback.', 'Facebook publishing queued');
    }

    public function generateNow(BlogGenerationWorkflow $workflow): void
    {
        Gate::authorize('Admin');
        $this->statusMessage = null;

        try {
            $run = $workflow->dispatchManual((int) $this->wordTarget);
            if ($run->status === BlogGenerationRun::STATUS_AWAITING_TOPIC) {
                $this->notify('success', 'Run #'.$run->id.' is waiting for an administrator to approve a topic. It will start automatically after approval.', 'Waiting for an approved topic');
            } else {
                $this->notify('success', 'Draft generation was queued. Run #'.$run->id.' will appear here as it progresses.', 'Draft generation queued');
            }
        } catch (QueryException $exception) {
            Log::error('A manual blog draft generation run could not be queued because of a database error.', [
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The draft could not be queued because the database rejected the request. Check the Laravel log and blog queue configuration.', 'Draft generation could not be queued');
        } catch (RuntimeException $exception) {
            Log::warning('A manual blog draft generation request was rejected.', [
                'admin_id' => auth()->id(),
                'reason' => $exception->getMessage(),
            ]);
            $this->notify('error', $exception->getMessage(), 'Draft generation is unavailable');
        } catch (Throwable $exception) {
            Log::error('A manual blog draft generation request failed unexpectedly.', [
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The draft could not be queued because of an unexpected server error. Check the Laravel log.', 'Draft generation could not be queued');
        }
    }

    public function dismissGenerationMessage(): void
    {
        Gate::authorize('Admin');
        $this->statusMessage = null;
    }

    public function retryGeneration(int $runId, BlogGenerationWorkflow $workflow): void
    {
        Gate::authorize('Admin');
        try {
            $run = $workflow->retryFailed($runId);
            $this->notify('success', 'Generation run #'.$run->id.' was queued for retry.', 'Generation retry queued');
        } catch (QueryException $exception) {
            Log::error('A failed blog generation run could not be retried because of a database error.', [
                'run_id' => $runId,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The generation run could not be retried because of a database error. Check the Laravel log.', 'Generation retry failed');
        } catch (RuntimeException $exception) {
            $this->notify('error', $exception->getMessage(), 'Generation retry failed');
        } catch (Throwable $exception) {
            Log::error('A failed blog generation run could not be retried.', [
                'run_id' => $runId,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The generation run could not be retried because of an unexpected server error. Check the Laravel log.', 'Generation retry failed');
        }
    }

    public function generateTopicSuggestions(BlogGenerationWorkflow $workflow): void
    {
        Gate::authorize('Admin');
        try {
            $batch = $workflow->requestTopicSuggestions();
            $this->notify(
                'success',
                $batch?->status === BlogTopicSuggestionBatch::STATUS_QUEUED || $batch?->status === BlogTopicSuggestionBatch::STATUS_RUNNING
                    ? 'Topic suggestions are being generated. They will appear as pending for your approval.'
                    : 'Review pending suggestions before requesting another batch.',
                'Topic suggestions requested',
            );
        } catch (QueryException $exception) {
            Log::error('A blog topic suggestion batch could not be queued because of a database error.', [
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'Topic suggestions could not be queued because of a database error. Check the Laravel log.', 'Topic suggestions are unavailable');
        } catch (RuntimeException $exception) {
            $this->notify('error', $exception->getMessage(), 'Topic suggestions are unavailable');
        } catch (Throwable $exception) {
            Log::error('A blog topic suggestion request failed unexpectedly.', [
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'Topic suggestions could not be queued because of an unexpected server error. Check the Laravel log.', 'Topic suggestions are unavailable');
        }
    }

    public function approveTopic(int $topicId, BlogGenerationWorkflow $workflow): void
    {
        Gate::authorize('Admin');
        $this->resetPage('topicsPage');
        try {
            $workflow->approveTopic($topicId);
            $message = config('blog.generation_enabled')
                ? 'The topic was approved. Any draft waiting for a topic has now been queued.'
                : 'The topic was approved. Generation is disabled, so any waiting draft remains paused until it is enabled.';
            $this->notify('success', $message, 'Topic approved');
        } catch (QueryException $exception) {
            Log::error('A blog topic could not be approved because of a database error.', [
                'topic_id' => $topicId,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The topic could not be approved because of a database error. Check the Laravel log.', 'Topic could not be approved');
        } catch (RuntimeException $exception) {
            $this->notify('error', $exception->getMessage(), 'Topic could not be approved');
        } catch (Throwable $exception) {
            Log::error('A blog topic could not be approved.', [
                'topic_id' => $topicId,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The topic could not be approved because of an unexpected server error. Check the Laravel log.', 'Topic could not be approved');
        }
    }

    public function rejectTopic(int $topicId, BlogGenerationWorkflow $workflow): void
    {
        Gate::authorize('Admin');
        $this->resetPage('topicsPage');
        try {
            $workflow->rejectTopic($topicId);
            $this->notify('success', 'The topic was removed from the approval queue.', 'Topic rejected');
        } catch (QueryException $exception) {
            Log::error('A blog topic could not be rejected because of a database error.', [
                'topic_id' => $topicId,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The topic could not be rejected because of a database error. Check the Laravel log.', 'Topic could not be rejected');
        } catch (RuntimeException $exception) {
            $this->notify('error', $exception->getMessage(), 'Topic could not be rejected');
        } catch (Throwable $exception) {
            Log::error('A blog topic could not be rejected.', [
                'topic_id' => $topicId,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The topic could not be rejected because of an unexpected server error. Check the Laravel log.', 'Topic could not be rejected');
        }
    }

    public function generateFromTopic(int $topicId, BlogGenerationWorkflow $workflow): void
    {
        Gate::authorize('Admin');
        try {
            $run = $workflow->dispatchManualForTopic($topicId, (int) $this->wordTarget);
            $this->notify('success', 'A new draft for the approved topic was queued as run #'.$run->id.'.', 'Topic draft queued');
        } catch (QueryException $exception) {
            Log::error('A draft for an approved topic could not be queued because of a database error.', [
                'topic_id' => $topicId,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The topic draft could not be queued because of a database error. Check the Laravel log.', 'Topic draft could not be queued');
        } catch (RuntimeException $exception) {
            $this->notify('error', $exception->getMessage(), 'Topic draft could not be queued');
        } catch (Throwable $exception) {
            Log::error('A draft for an approved topic could not be queued.', [
                'topic_id' => $topicId,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->notify('error', 'The topic draft could not be queued because of an unexpected server error. Check the Laravel log.', 'Topic draft could not be queued');
        }
    }

    public function updatedStatusFilter(): void
    {
        Gate::authorize('Admin');
        abort_unless(in_array($this->statusFilter, ['', 'draft', 'published', 'archived'], true), 422);
        $this->resetPage();
    }

    public function setOperationsTab(string $tab): void
    {
        Gate::authorize('Admin');
        abort_unless(in_array($tab, ['articles', 'activity', 'topics'], true), 422);

        $this->operationsTab = $tab;
    }

    public function updatedArticlesPerPage(): void
    {
        Gate::authorize('Admin');
        abort_unless(in_array($this->articlesPerPage, [5, 10, 25, 50, 100], true), 422);
        $this->resetPage();
    }

    public function updatedActivityPerPage(): void
    {
        Gate::authorize('Admin');
        abort_unless(in_array($this->activityPerPage, [5, 10, 25, 50, 100], true), 422);
        $this->resetPage('generationRunsPage');
    }

    public function updatedTopicsPerPage(): void
    {
        Gate::authorize('Admin');
        abort_unless(in_array($this->topicsPerPage, [5, 10, 25, 50, 100], true), 422);
        $this->resetPage('topicsPage');
    }

    public function render()
    {
        Gate::authorize('Admin');

        $posts = BlogPost::query()
            ->with(['category', 'facebookShare'])
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest('updated_at')
            ->paginate($this->articlesPerPage)
            ->through(fn (BlogPost $post): array => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'status_class' => match ($post->status) {
                    BlogPost::STATUS_DRAFT => 'bg-amber-50 text-amber-800',
                    BlogPost::STATUS_PUBLISHED => 'bg-emerald-50 text-emerald-800',
                    default => 'bg-slate-100 text-slate-700',
                },
                'is_published' => $post->status === BlogPost::STATUS_PUBLISHED,
                'category_name' => $post->category?->name ?: 'Uncategorized',
                'updated_at_label' => $post->updated_at->format('M j, Y'),
                'facebook_status' => $post->facebookShare?->status ?: 'manual share available',
                'facebook_error' => $post->facebookShare?->error_message,
                'edit_url' => route('admin.blog.edit', $post->id),
                'preview_url' => route('admin.blog.preview', $post->id),
                'manual_share_url' => route('blog.show', $post->slug),
                'can_retry_facebook' => config('blog.facebook_enabled')
                    && in_array($post->facebookShare?->status, [
                        BlogFacebookShare::STATUS_FAILED,
                        BlogFacebookShare::STATUS_MANUAL,
                        BlogFacebookShare::STATUS_CANCELLED,
                    ], true),
            ]);
        $statusCounts = BlogPost::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $generationRuns = BlogGenerationRun::query()
            ->with(['post', 'topic'])
            ->latest('id')
            ->paginate($this->activityPerPage, pageName: 'generationRunsPage')
            ->through(fn (BlogGenerationRun $run): array => [
                'id' => $run->id,
                'status' => $run->status,
                'status_message' => match ($run->status) {
                    BlogGenerationRun::STATUS_QUEUED => 'Queued and waiting for the blog worker to start.',
                    BlogGenerationRun::STATUS_RUNNING => 'Generating draft…',
                    BlogGenerationRun::STATUS_COMPLETED => 'Draft generated. Review it before publishing.',
                    BlogGenerationRun::STATUS_AWAITING_TOPIC => 'Waiting for an administrator to approve a topic.',
                    default => null,
                },
                'run_key' => $run->run_key,
                'provider' => $run->provider,
                'topic_title' => $run->topic?->title,
                'word_target' => $run->word_target,
                'post_title' => $run->post?->title,
                'preview_url' => $run->post ? route('admin.blog.preview', $run->post->id) : null,
                'notification_status' => $run->notification_status,
                'error_message' => $run->error_message,
                'notification_error' => $run->notification_error,
                'can_retry' => config('blog.generation_enabled')
                    && $run->status === BlogGenerationRun::STATUS_FAILED
                    && $run->blog_topic_id !== null,
            ]);
        $topics = BlogTopic::query()
            ->with('relatedEbook')
            ->withCount('generationRuns')
            ->whereIn('status', [BlogTopic::STATUS_PENDING, BlogTopic::STATUS_APPROVED])
            ->latest('created_at')
            ->paginate($this->topicsPerPage, pageName: 'topicsPage')
            ->through(fn (BlogTopic $topic): array => [
                'id' => $topic->id,
                'title' => $topic->title,
                'type' => str($topic->topic_type)->replace('_', ' ')->title()->toString(),
                'angle' => $topic->angle,
                'status' => $topic->status,
                'ebook_title' => $topic->relatedEbook?->title,
                'uses' => $topic->generation_runs_count,
                'uses_label' => str('time')->plural($topic->generation_runs_count)->toString(),
            ]);
        $topicCounts = [
            'pending' => BlogTopic::query()->where('status', BlogTopic::STATUS_PENDING)->count(),
            'available' => BlogTopic::query()
                ->where('status', BlogTopic::STATUS_APPROVED)
                ->whereDoesntHave('generationRuns')
                ->count(),
        ];
        $topicSuggestionBatchStatus = BlogTopicSuggestionBatch::query()->latest('id')->value('status');

        return view('livewire.admin.blog.studio', [
            'posts' => $posts,
            'statusCounts' => $statusCounts,
            'generationRuns' => $generationRuns,
            'generationEnabled' => (bool) config('blog.generation_enabled'),
            'topics' => $topics,
            'topicCounts' => $topicCounts,
            'topicSuggestionBatchStatus' => $topicSuggestionBatchStatus,
        ]);
    }

    private function notify(string $type, string $message, string $heading = 'Status update'): void
    {
        $this->statusType = $type;
        $this->statusMessage = $message;
        $this->statusHeading = $heading;
    }
}
