<?php

namespace App\Jobs;

use App\Mail\BlogDraftReviewMail;
use App\Models\BlogGenerationRun;
use App\Models\BlogSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendBlogDraftReviewEmail implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 86400;

    public function __construct(public readonly int $runId) {}

    public function uniqueId(): string
    {
        return (string) $this->runId;
    }

    public function handle(): void
    {
        $run = BlogGenerationRun::query()->with('post')->findOrFail($this->runId);
        if ($run->status !== BlogGenerationRun::STATUS_COMPLETED
            || $run->post === null
            || $run->notification_status === 'sent') {
            return;
        }

        $recipients = BlogSetting::getValue('notification_emails', []);
        $recipients = is_array($recipients)
            ? array_values(array_filter($recipients, fn ($email) => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)))
            : [];
        if ($recipients === []) {
            $run->update(['notification_status' => 'skipped', 'notification_error' => 'No valid review recipients are configured.']);

            return;
        }

        $reviewUrl = URL::temporarySignedRoute(
            'admin.blog.review',
            now()->addDays(3),
            ['postId' => $run->post->id],
        );

        try {
            Mail::to($recipients)->send(new BlogDraftReviewMail($run->post, $reviewUrl));
        } catch (Throwable $exception) {
            $run->update([
                'notification_status' => 'failed',
                'notification_error' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
            Log::error('The blog draft review email could not be sent.', [
                'run_id' => $run->id,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }

        $run->update(['notification_status' => 'sent', 'notification_error' => null]);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }
}
