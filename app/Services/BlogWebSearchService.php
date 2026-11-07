<?php

namespace App\Services;

use App\Models\BlogSetting;
use App\Models\BlogTopic;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlogWebSearchService
{
    private const MAX_RESULTS = 10;

    public function __construct(private readonly BlogTavilyCreditBudget $budget) {}

    /**
     * @return array{items: list<array{title: string, summary: string, url: string, published_at: ?string, source_host: string}>, errors: list<string>}
     */
    public function fetch(BlogTopic $topic): array
    {
        $apiKey = trim((string) config('services.tavily.api_key', ''));
        if ($apiKey === '') {
            return ['items' => [], 'errors' => ['Tavily search is not configured. Set TAVILY_API_KEY on the server.']];
        }

        $depth = BlogSetting::getValue('tavily_search_depth', 'basic');
        $depth = in_array($depth, ['basic', 'advanced'], true) ? $depth : 'basic';
        $credits = $depth === 'advanced' ? 2 : 1;
        $maxResults = (int) BlogSetting::getValue('tavily_max_results', self::MAX_RESULTS);
        $maxResults = max(1, min(20, $maxResults));
        $query = $this->buildQuery($topic);

        try {
            $this->budget->reserve($credits);
        } catch (\RuntimeException $exception) {
            Log::notice('The Tavily search was blocked by the monthly credit limit.', [
                'topic_id' => $topic->id,
                'credits_requested' => $credits,
            ]);

            return ['items' => [], 'errors' => [$exception->getMessage()]];
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->post('https://api.tavily.com/search', [
                    'query' => $query,
                    'search_depth' => $depth,
                    'max_results' => $maxResults,
                    'topic' => $topic->topic_type === BlogTopic::TYPE_CURRENT_AFFAIRS ? 'news' : 'general',
                    'include_answer' => false,
                    'include_raw_content' => false,
                    'include_images' => false,
                ] + $this->currentAffairsTimeRange($topic));
        } catch (ConnectionException) {
            Log::warning('The Tavily blog research search could not connect.');

            return ['items' => [], 'errors' => ['Tavily search could not connect. Check server network access and try again.']];
        }

        if (! $response->successful()) {
            Log::warning('Tavily rejected a blog research search.', ['status' => $response->status()]);

            return ['items' => [], 'errors' => ['Tavily search returned HTTP '.$response->status().'. Check the API key, account quota, and search configuration.']];
        }

        $results = $response->json('results');
        if (! is_array($results)) {
            Log::warning('Tavily returned a malformed blog research response.');

            return ['items' => [], 'errors' => ['Tavily returned an invalid search response.']];
        }

        $items = [];
        foreach (array_slice($results, 0, $maxResults) as $result) {
            if (! is_array($result)) {
                continue;
            }

            $title = $this->plainText($result['title'] ?? null, 240);
            $summary = $this->plainText($result['content'] ?? null, 1800);
            $url = trim(is_string($result['url'] ?? null) ? $result['url'] : '');
            if ($title === '' || $summary === '' || ! $this->isValidSourceUrl($url)) {
                continue;
            }

            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $date = is_string($result['published_date'] ?? null) ? trim($result['published_date']) : '';
            $timestamp = $date !== '' ? strtotime($date) : false;

            $items[] = [
                'title' => $title,
                'summary' => $summary,
                'url' => $url,
                'published_at' => $timestamp !== false ? gmdate('c', $timestamp) : null,
                'source_host' => $host,
            ];
        }

        return ['items' => $items, 'errors' => []];
    }

    private function buildQuery(BlogTopic $topic): string
    {
        $query = trim($topic->title.' '.$topic->angle);
        if ($topic->topic_type === BlogTopic::TYPE_CURRENT_AFFAIRS) {
            $query .= ' latest current affairs update; verify dates and details from primary/official sources and reputable reporting';
        } else {
            $query .= ' evidence, supporting ideas, examples, research, and practical perspectives';
            if ($topic->relatedEbook !== null) {
                $query .= ' inspired by the MephEd eBook '.$topic->relatedEbook->title;
            }
        }

        $preferredDomains = BlogSetting::getValue('preferred_research_domains', []);
        if (is_array($preferredDomains)) {
            $domains = array_values(array_filter($preferredDomains, fn (mixed $domain): bool => is_string($domain) && $domain !== ''));
            if ($domains !== []) {
                $query .= '. Prefer these sources where relevant, but include other credible sources too: '.implode(', ', array_slice($domains, 0, 15));
            }
        }

        return mb_substr($query, 0, 1000);
    }

    /**
     * @return array{time_range?: string}
     */
    private function currentAffairsTimeRange(BlogTopic $topic): array
    {
        if ($topic->topic_type !== BlogTopic::TYPE_CURRENT_AFFAIRS) {
            return [];
        }

        $range = BlogSetting::getValue('tavily_current_affairs_time_range', 'month');

        return in_array($range, ['day', 'week', 'month', 'year'], true)
            ? ['time_range' => $range]
            : ['time_range' => 'month'];
    }

    private function isValidSourceUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['fragment'])
            || (isset($parts['port']) && $parts['port'] !== 443)
            || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        return $host !== ''
            && str_contains($host, '.')
            && ! str_ends_with($host, '.')
            && ! str_ends_with($host, '.local')
            && ! str_ends_with($host, '.localhost')
            && ! filter_var($host, FILTER_VALIDATE_IP)
            && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false
            && ! $this->hasSensitiveQuery($url);
    }

    private function hasSensitiveQuery(string $url): bool
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        foreach (array_keys($query) as $name) {
            if (preg_match('/token|secret|signature|auth|password|credential|api[_-]?key/i', (string) $name)) {
                return true;
            }
        }

        return false;
    }

    private function plainText(mixed $value, int $maxLength): string
    {
        if (! is_string($value)) {
            return '';
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));

        return mb_substr($text, 0, $maxLength);
    }
}
