<?php

namespace App\Services;

use App\Exceptions\BlogDraftFieldLimitException;
use App\Exceptions\BlogDraftRejectedException;
use App\Jobs\SendBlogDraftReviewEmail;
use App\Models\BlogCategory;
use App\Models\BlogGenerationRun;
use App\Models\BlogPost;
use App\Models\BlogSetting;
use App\Models\BlogTag;
use App\Models\BlogTopic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BlogDraftPipeline
{
    public function __construct(
        private readonly BlogResearchService $research,
        private readonly BlogDraftWriter $writer,
        private readonly BlogEditorialContext $editorialContext,
        private readonly BlogContentSanitizer $sanitizer,
        private readonly BlogArtworkGenerator $artwork,
        private readonly BlogDraftSimilarityGuard $similarityGuard,
    ) {}

    public function run(int $runId): void
    {
        $run = BlogGenerationRun::query()->findOrFail($runId);
        if ($run->status === BlogGenerationRun::STATUS_COMPLETED) {
            $this->queueReviewEmailIfNeeded($run);

            return;
        }

        $run->update([
            'status' => BlogGenerationRun::STATUS_RUNNING,
            'started_at' => now(),
            'finished_at' => null,
            'error_message' => null,
            'provider' => (string) config('blog.ai_provider'),
        ]);

        try {
            $topic = $run->topic()->with('relatedEbook')->first();
            if ($topic === null || $topic->status !== BlogTopic::STATUS_APPROVED) {
                throw new \RuntimeException('This run has no approved topic assigned. Approve a topic in the Blog Studio before retrying it.');
            }

            $researchResult = $this->research->fetch($topic);
            if ($researchResult['items'] === []) {
                $detail = $researchResult['errors'] !== [] ? ' '.implode(' ', array_slice($researchResult['errors'], 0, 3)) : '';
                throw new \RuntimeException('No usable web research items were found for the approved topic.'.$detail);
            }

            $resources = $this->editorialContext->internalResources();
            $existingPosts = BlogPost::query()
                ->whereIn('status', [BlogPost::STATUS_DRAFT, BlogPost::STATUS_PUBLISHED])
                ->latest('updated_at')
                ->limit(100)
                ->get(['id', 'title', 'excerpt', 'body'])
                ->map(fn (BlogPost $post) => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'excerpt' => $post->excerpt,
                    'body' => $post->body,
                ])->all();
            $recentTopics = array_map(fn (array $post) => [
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
            ], $existingPosts);

            $revisionInstruction = null;
            $draft = null;
            foreach ([1, 2] as $attempt) {
                $response = $this->writer->write(
                    $researchResult['items'],
                    $resources,
                    $recentTopics,
                    $revisionInstruction,
                    $topic,
                    (int) ($run->word_target ?: BlogSetting::getValue('content_word_target', config('blog.default_content_word_target', 700))),
                );
                try {
                    $candidate = $this->validateDraft($response, $researchResult['items'], $resources);
                } catch (BlogDraftFieldLimitException $exception) {
                    if ($attempt === 2) {
                        throw $exception;
                    }
                    $revisionInstruction = $exception->revisionInstructions();
                    Log::notice('The blog writer is revising metadata that exceeded field limits.', [
                        'run_id' => $run->id,
                        'issues' => $exception->issues,
                    ]);

                    continue;
                }

                $similarArticle = $this->similarityGuard->closestMatch($candidate['title'], $candidate['body'], $existingPosts);
                if ($similarArticle === null) {
                    $draft = $candidate;

                    break;
                }
                if ($attempt === 2) {
                    throw new BlogDraftRejectedException('The generated article was too similar to existing article "'.$similarArticle['title'].'". No duplicate draft was saved.');
                }

                $revisionInstruction = 'The candidate was too similar to an existing MephEd article titled "'.$similarArticle['title'].'". Choose a distinct question, audience need, or practical angle supported by the research. Do not paraphrase or reproduce that article.';
                Log::notice('The blog writer is revising a draft that was too similar to existing content.', [
                    'run_id' => $run->id,
                    'similar_post_id' => $similarArticle['id'],
                    'similarity' => round($similarArticle['score'], 3),
                ]);
            }

            $post = $this->saveDraft($run, $draft, $researchResult);
            $this->queueReviewEmailIfNeeded($run->fresh());
            Log::info('A blog draft was generated for editorial review.', [
                'run_id' => $run->id,
                'post_id' => $post->id,
                'provider' => $run->provider,
            ]);
        } catch (Throwable $exception) {
            $run->refresh()->update([
                'status' => BlogGenerationRun::STATUS_FAILED,
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
                'finished_at' => now(),
            ]);
            Log::error('The blog draft generation run failed.', [
                'run_id' => $run->id,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  list<array{title: string, summary: string, url: string, published_at: ?string, source_host: string}>  $sources
     * @param  list<array{type: string, title: string, summary: string, url: string}>  $resources
     * @return array{title: string, excerpt: string, body: string, category: string, tags: list<string>, meta_title: string, meta_description: string, sources: list<array{title: string, summary: string, url: string, published_at: ?string, source_host: string}>, internal_links: list<array{type: string, title: string, summary: string, url: string}>, word_count: int}
     */
    private function validateDraft(array $draft, array $sources, array $resources): array
    {
        foreach (['title', 'excerpt', 'body_html', 'category', 'meta_title', 'meta_description'] as $field) {
            if (! isset($draft[$field]) || ! is_string($draft[$field]) || trim($draft[$field]) === '') {
                throw new BlogDraftRejectedException('The generated article is missing a required text field: '.$field.'.');
            }
        }

        if (mb_strlen($draft['body_html']) > 100_000) {
            throw new BlogDraftRejectedException('The generated article exceeds the maximum content size.');
        }

        if (! isset($draft['tags']) || ! is_array($draft['tags']) || ! array_is_list($draft['tags'])
            || ! isset($draft['source_indices']) || ! is_array($draft['source_indices']) || ! array_is_list($draft['source_indices'])) {
            throw new BlogDraftRejectedException('The generated article contains invalid tags or source citations.');
        }
        $internalLinkIndices = $draft['internal_link_indices'] ?? [];
        if (! is_array($internalLinkIndices) || ! array_is_list($internalLinkIndices)
            || count(array_filter($internalLinkIndices, fn ($index) => ! is_int($index))) > 0) {
            throw new BlogDraftRejectedException('The generated article contains invalid internal resource links.');
        }

        $title = trim($draft['title']);
        $excerpt = trim($draft['excerpt']);
        $categoryName = trim($draft['category']);
        if (count(array_filter($draft['tags'], fn ($tag) => ! is_string($tag) || trim($tag) === '')) > 0) {
            throw new BlogDraftRejectedException('The generated article contains an invalid tag.');
        }

        if (count(array_filter($draft['source_indices'], fn ($index) => ! is_int($index))) > 0) {
            throw new BlogDraftRejectedException('The generated article contains an invalid source citation.');
        }

        if (Str::slug($categoryName) === '') {
            throw new BlogDraftRejectedException('The generated article contains an invalid category.');
        }

        $tags = collect($draft['tags'])
            ->map(fn (string $tag) => trim($tag))
            ->unique(fn (string $tag) => Str::slug($tag))
            ->values()
            ->all();

        $fieldLimits = [
            'title' => [$title, 200],
            'excerpt' => [$excerpt, 500],
            'category' => [$categoryName, 100],
            'meta_title' => [trim($draft['meta_title']), 70],
            'meta_description' => [trim($draft['meta_description']), 320],
        ];
        $fieldLimitIssues = [];
        foreach ($fieldLimits as $field => [$value, $limit]) {
            $length = mb_strlen($value);
            if ($length > $limit) {
                $fieldLimitIssues[] = $field.' is '.$length.' characters (maximum '.$limit.').';
            }
        }
        if ($fieldLimitIssues !== []) {
            throw new BlogDraftFieldLimitException($fieldLimitIssues);
        }
        if (count($tags) > 10 || count($draft['source_indices']) < 1 || count($draft['source_indices']) > 5) {
            throw new BlogDraftRejectedException('The generated article contains too many tags or an invalid number of citations.');
        }

        foreach ($tags as $tag) {
            if (mb_strlen($tag) > 100 || Str::slug($tag) === '') {
                throw new BlogDraftRejectedException('The generated article contains an invalid tag.');
            }
        }

        $selectedSources = [];
        foreach (array_unique($draft['source_indices']) as $sourceIndex) {
            if (! array_key_exists($sourceIndex - 1, $sources)) {
                throw new BlogDraftRejectedException('The generated article cites a source that was not provided.');
            }
            $selectedSources[] = $sources[$sourceIndex - 1];
        }
        if (count($internalLinkIndices) > 3) {
            throw new BlogDraftRejectedException('The generated article selected too many internal resources.');
        }
        $selectedResources = [];
        foreach (array_unique($internalLinkIndices) as $resourceIndex) {
            if (! array_key_exists($resourceIndex - 1, $resources)) {
                throw new BlogDraftRejectedException('The generated article selected a resource that is not available.');
            }
            $selectedResources[] = $resources[$resourceIndex - 1];
        }

        $sourceMarkup = '<h2>Sources</h2><ul>';
        foreach ($selectedSources as $source) {
            $label = htmlspecialchars($source['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $url = htmlspecialchars($source['url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $sourceMarkup .= '<li><a href="'.$url.'">'.$label.'</a></li>';
        }
        $sourceMarkup .= '</ul>';
        $resourceMarkup = '';
        if ($selectedResources !== []) {
            $resourceMarkup = '<h2>Related MephEd resources</h2><ul>';
            foreach ($selectedResources as $resource) {
                $label = htmlspecialchars($resource['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $type = htmlspecialchars($resource['type'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $url = htmlspecialchars($resource['url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $resourceMarkup .= '<li>'.$type.': <a href="'.$url.'">'.$label.'</a></li>';
            }
            $resourceMarkup .= '</ul>';
        }
        $body = $this->sanitizer->sanitize($draft['body_html']).$sourceMarkup.$resourceMarkup;
        $wordCount = str_word_count(strip_tags($body));
        if (mb_strlen(strip_tags($body)) < 1500 || $wordCount < 300) {
            throw new BlogDraftRejectedException('The generated article did not meet the minimum length for editorial review.');
        }

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'body' => $body,
            'category' => $categoryName,
            'tags' => $tags,
            'meta_title' => trim($draft['meta_title']),
            'meta_description' => trim($draft['meta_description']),
            'sources' => $selectedSources,
            'internal_links' => $selectedResources,
            'word_count' => $wordCount,
        ];
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  array{items: list<array{title: string, summary: string, url: string, published_at: ?string, source_host: string}>, errors: list<string>}  $researchResult
     */
    private function saveDraft(BlogGenerationRun $run, array $draft, array $researchResult): BlogPost
    {
        $existing = $run->fresh()->post;
        if ($existing !== null) {
            return $existing;
        }

        if (BlogPost::query()->whereRaw('LOWER(title) = ?', [mb_strtolower($draft['title'])])->exists()) {
            throw new BlogDraftRejectedException('A blog article with this title already exists. Review the existing article before generating another draft.');
        }

        $artworkPath = $this->artwork->store($draft['title'], $draft['category'], $run->id);

        try {
            return DB::transaction(function () use ($run, $draft, $researchResult, $artworkPath): BlogPost {
                $lockedRun = BlogGenerationRun::query()->lockForUpdate()->findOrFail($run->id);
                if ($lockedRun->blog_post_id !== null) {
                    return BlogPost::query()->findOrFail($lockedRun->blog_post_id);
                }

                $category = BlogCategory::query()->firstOrCreate(
                    ['slug' => Str::slug($draft['category'])],
                    ['name' => $draft['category']],
                );
                $post = BlogPost::query()->create([
                    'title' => $draft['title'],
                    'slug' => $this->uniqueSlug($draft['title']),
                    'excerpt' => $draft['excerpt'],
                    'body' => $draft['body'],
                    'status' => BlogPost::STATUS_DRAFT,
                    'author_id' => $run->author_id,
                    'category_id' => $category->id,
                    'meta_title' => $draft['meta_title'],
                    'meta_description' => $draft['meta_description'],
                    'hero_image_path' => $artworkPath,
                ]);
                $tagIds = collect($draft['tags'])->map(fn (string $tag) => BlogTag::query()->firstOrCreate(
                    ['slug' => Str::slug($tag)],
                    ['name' => $tag],
                )->id);
                $post->tags()->sync($tagIds);

                $recipients = BlogSetting::getValue('notification_emails', []);
                $hasRecipients = is_array($recipients) && collect($recipients)->contains(
                    fn ($email) => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)
                );
                $lockedRun->update([
                    'status' => BlogGenerationRun::STATUS_COMPLETED,
                    'blog_post_id' => $post->id,
                    'metadata' => [
                        'sources' => $draft['sources'],
                        'internal_links' => $draft['internal_links'],
                        'source_errors' => $researchResult['errors'],
                        'word_count' => $draft['word_count'],
                    ],
                    'notification_status' => $hasRecipients ? 'queued' : 'skipped',
                    'notification_error' => $hasRecipients ? null : 'No valid review recipients are configured.',
                    'error_message' => null,
                    'finished_at' => now(),
                ]);

                return $post;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($artworkPath);
            throw $exception;
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::limit(Str::slug($title), 165, '');
        if ($base === '') {
            $base = 'education-insight';
        }

        $slug = $base;
        $suffix = 2;
        while (BlogPost::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function queueReviewEmailIfNeeded(?BlogGenerationRun $run): void
    {
        if ($run === null || $run->status !== BlogGenerationRun::STATUS_COMPLETED || $run->notification_status !== 'queued') {
            return;
        }

        try {
            SendBlogDraftReviewEmail::dispatch($run->id)->onConnection('blog')->onQueue('blog');
        } catch (Throwable $exception) {
            $run->update([
                'notification_status' => 'failed',
                'notification_error' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
            Log::error('The blog draft review notification could not be queued.', [
                'run_id' => $run->id,
                'exception' => $exception::class,
            ]);
        }
    }
}
