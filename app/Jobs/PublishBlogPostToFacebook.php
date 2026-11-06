<?php

namespace App\Jobs;

use App\Exceptions\FacebookRateLimitException;
use App\Models\BlogFacebookShare;
use App\Services\BlogFacebookPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishBlogPostToFacebook implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(public readonly int $shareId) {}

    public function uniqueId(): string
    {
        return (string) $this->shareId;
    }

    public function handle(BlogFacebookPublisher $publisher): void
    {
        try {
            $publisher->publish($this->shareId);
        } catch (FacebookRateLimitException $exception) {
            BlogFacebookShare::query()->whereKey($this->shareId)->update([
                'status' => $this->attempts() >= $this->tries
                    ? BlogFacebookShare::STATUS_FAILED
                    : BlogFacebookShare::STATUS_QUEUED,
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);

            if ($this->attempts() < $this->tries) {
                throw $exception;
            }
        } catch (Throwable $exception) {
            BlogFacebookShare::query()->whereKey($this->shareId)->update([
                'status' => BlogFacebookShare::STATUS_FAILED,
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
            Log::error('Facebook blog-share delivery failed.', [
                'share_id' => $this->shareId,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }
}
