<?php

namespace App\Livewire\Admin;

use App\Models\BlogFacebookShare;
use App\Models\BlogGenerationRun;
use App\Models\BlogPost;
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

    public ?string $statusMessage = null;

    public string $statusType = 'success';

    public string $statusHeading = 'Status update';

    public function mount(): void
    {
        Gate::authorize('Admin');
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
            $run = $workflow->dispatchManual();
            $this->notify('success', 'Draft generation was queued. Run #'.$run->id.' will appear here as it progresses.', 'Draft generation queued');
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
        $run = $workflow->retryFailed($runId);
        $this->notify('success', 'Generation run #'.$run->id.' was queued for retry.', 'Generation retry queued');
    }

    public function updatedStatusFilter(): void
    {
        Gate::authorize('Admin');
        abort_unless(in_array($this->statusFilter, ['', 'draft', 'published', 'archived'], true), 422);
        $this->resetPage();
    }

    public function render()
    {
        Gate::authorize('Admin');

        $posts = BlogPost::query()
            ->with(['category', 'facebookShare'])
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest('updated_at')
            ->paginate(15)
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
            ->with('post')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (BlogGenerationRun $run): array => [
                'id' => $run->id,
                'status' => $run->status,
                'status_message' => match ($run->status) {
                    BlogGenerationRun::STATUS_QUEUED => 'Queued and waiting for the blog worker to start.',
                    BlogGenerationRun::STATUS_RUNNING => 'Generating draft…',
                    BlogGenerationRun::STATUS_COMPLETED => 'Draft generated. Review it before publishing.',
                    default => null,
                },
                'run_key' => $run->run_key,
                'provider' => $run->provider,
                'post_title' => $run->post?->title,
                'preview_url' => $run->post ? route('admin.blog.preview', $run->post->id) : null,
                'notification_status' => $run->notification_status,
                'error_message' => $run->error_message,
                'notification_error' => $run->notification_error,
                'can_retry' => config('blog.generation_enabled')
                    && $run->status === BlogGenerationRun::STATUS_FAILED,
            ]);

        return view('livewire.admin.blog.studio', [
            'posts' => $posts,
            'statusCounts' => $statusCounts,
            'generationRuns' => $generationRuns,
            'generationEnabled' => (bool) config('blog.generation_enabled'),
        ]);
    }

    private function notify(string $type, string $message, string $heading = 'Status update'): void
    {
        $this->statusType = $type;
        $this->statusMessage = $message;
        $this->statusHeading = $heading;
    }
}
