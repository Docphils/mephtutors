<?php

namespace App\Jobs;

use App\Models\BlogTopicSuggestionBatch;
use App\Services\BlogTopicSuggestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateBlogTopicSuggestions implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $uniqueFor = 1800;

    public function __construct(public readonly int $batchId) {}

    public function uniqueId(): string
    {
        return (string) $this->batchId;
    }

    public function handle(BlogTopicSuggestionService $suggestions): void
    {
        $suggestions->generate($this->batchId);
    }

    public function failed(Throwable $exception): void
    {
        BlogTopicSuggestionBatch::query()->whereKey($this->batchId)->update([
            'status' => BlogTopicSuggestionBatch::STATUS_FAILED,
            'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            'finished_at' => now(),
        ]);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60];
    }
}
