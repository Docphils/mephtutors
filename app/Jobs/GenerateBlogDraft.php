<?php

namespace App\Jobs;

use App\Exceptions\BlogDraftRejectedException;
use App\Services\BlogDraftPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateBlogDraft implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(public readonly int $runId) {}

    public function uniqueId(): string
    {
        return (string) $this->runId;
    }

    /**
     * @throws \Throwable
     */
    public function handle(BlogDraftPipeline $pipeline): void
    {
        try {
            $pipeline->run($this->runId);
        } catch (BlogDraftRejectedException $exception) {
            // The pipeline has recorded the failed run and its editorial reason. Retrying
            // the same unsuitable content automatically wastes provider calls and can
            // produce a near-duplicate, so retries are reserved for transient failures.
            return;
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
