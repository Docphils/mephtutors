<?php

namespace App\Services;

use App\Enums\EbookStatus;
use App\Models\BlogPost;
use App\Models\BlogTopic;
use App\Models\BlogTopicSuggestionBatch;
use App\Models\Ebook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class BlogTopicSuggestionService
{
    public function __construct(private readonly BlogDraftWriter $writer) {}

    public function generate(int $batchId): void
    {
        $batch = BlogTopicSuggestionBatch::query()->findOrFail($batchId);
        if ($batch->status === BlogTopicSuggestionBatch::STATUS_COMPLETED) {
            return;
        }

        $batch->update([
            'status' => BlogTopicSuggestionBatch::STATUS_RUNNING,
            'error_message' => null,
            'finished_at' => null,
        ]);

        try {
            $ebooks = Ebook::query()
                ->where('status', EbookStatus::Published->value)
                ->latest('updated_at')
                ->limit(30)
                ->get(['id', 'title', 'subtitle', 'description', 'audience', 'primary_keyword'])
                ->map(function (Ebook $ebook): array {
                    $context = implode(' ', array_filter([
                        $ebook->subtitle,
                        $ebook->description,
                        $ebook->audience,
                        $ebook->primary_keyword,
                    ]));

                    return [
                        'id' => $ebook->id,
                        'title' => $ebook->title,
                        'context' => mb_substr(trim(strip_tags($context)), 0, 700),
                    ];
                })->all();

            $existingTitles = [
                ...BlogTopic::query()->pluck('title')->all(),
                ...BlogPost::query()
                    ->whereIn('status', [BlogPost::STATUS_DRAFT, BlogPost::STATUS_PUBLISHED])
                    ->pluck('title')
                    ->all(),
            ];
            $ideas = $this->writer->suggestTopics($batch->requested_count, $ebooks, $existingTitles);
            $ebookIds = array_fill_keys(array_column($ebooks, 'id'), true);
            $seenTitles = array_fill_keys(array_map(fn (string $title): string => Str::lower(Str::squish($title)), $existingTitles), true);
            $accepted = [];

            foreach ($ideas as $idea) {
                if (! is_array($idea)
                    || ! is_string($idea['title'] ?? null)
                    || ! is_string($idea['topic_type'] ?? null)
                    || ! is_string($idea['angle'] ?? null)) {
                    continue;
                }

                $title = Str::squish($idea['title']);
                $angle = Str::squish($idea['angle']);
                $type = $idea['topic_type'];
                $ebookId = $idea['related_ebook_id'] ?? null;
                $normalizedTitle = Str::lower($title);

                if ($title === ''
                    || mb_strlen($title) > 200
                    || $angle === ''
                    || mb_strlen($angle) > 500
                    || ! in_array($type, BlogTopic::types(), true)
                    || isset($seenTitles[$normalizedTitle])
                    || ($ebookId !== null && (! is_int($ebookId) || ! isset($ebookIds[$ebookId])))
                    || ($type === BlogTopic::TYPE_EBOOK_INSPIRED && $ebookId === null)) {
                    continue;
                }

                $seenTitles[$normalizedTitle] = true;
                $accepted[] = [
                    'title' => $title,
                    'topic_type' => $type,
                    'angle' => $angle,
                    'related_ebook_id' => $ebookId,
                    'status' => BlogTopic::STATUS_PENDING,
                    'generated_by' => $batch->requested_by,
                ];
                if (count($accepted) >= $batch->requested_count) {
                    break;
                }
            }

            if ($accepted === []) {
                throw new RuntimeException('The topic provider returned no valid, non-duplicate suggestions. Review the provider response and try again.');
            }

            DB::transaction(function () use ($accepted, $batch): void {
                foreach ($accepted as $topic) {
                    BlogTopic::query()->create($topic);
                }
                $batch->update([
                    'status' => BlogTopicSuggestionBatch::STATUS_COMPLETED,
                    'finished_at' => now(),
                    'error_message' => null,
                ]);
            });

            Log::info('A blog topic suggestion batch was generated for approval.', [
                'batch_id' => $batch->id,
                'requested_count' => $batch->requested_count,
                'created_count' => count($accepted),
            ]);
        } catch (\Throwable $exception) {
            $batch->update([
                'status' => BlogTopicSuggestionBatch::STATUS_FAILED,
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
                'finished_at' => now(),
            ]);
            throw $exception;
        }
    }
}
