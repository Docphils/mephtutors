<?php

namespace App\Services;

use App\Models\BlogTopic;

class BlogResearchService
{
    public function __construct(private readonly BlogWebSearchService $search) {}

    /**
     * @return array{items: list<array{title: string, summary: string, url: string, published_at: ?string, source_host: string}>, errors: list<string>}
     */
    public function fetch(BlogTopic $topic): array
    {
        return $this->search->fetch($topic);
    }
}
