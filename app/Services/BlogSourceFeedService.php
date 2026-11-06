<?php

namespace App\Services;

use App\Models\BlogSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class BlogSourceFeedService
{
    private const MAX_FEEDS = 15;

    private const MAX_FEED_BYTES = 1_000_000;

    private const MAX_ENTRIES_PER_FEED = 5;

    private const MAX_TOTAL_ENTRIES = 30;

    /**
     * @return array{items: list<array{title: string, summary: string, url: string, published_at: ?string, source_host: string}>, errors: list<string>}
     */
    public function fetch(): array
    {
        $domains = BlogSetting::getValue('source_domains', config('blog.source_domains', []));
        $configuredFeeds = BlogSetting::getValue('source_feed_urls');
        $feeds = is_array($configuredFeeds) ? $configuredFeeds : config('blog.source_feed_urls', []);
        $domains = is_array($domains) ? array_values(array_filter($domains, 'is_string')) : [];
        $feeds = is_array($feeds) ? array_values(array_filter($feeds, 'is_string')) : [];
        $items = [];
        $errors = [];

        foreach (array_slice($feeds, 0, self::MAX_FEEDS) as $index => $feedUrl) {
            try {
                $host = $this->approvedHostForUrl($feedUrl, $domains);
                $xml = $this->requestFeed($feedUrl, $host);
                array_push($items, ...$this->parseFeed($xml, $domains));
            } catch (Throwable $exception) {
                $errors[] = 'Feed '.($index + 1).': '.$exception->getMessage();
                Log::warning('A blog research feed could not be read.', [
                    'feed_number' => $index + 1,
                    'exception' => $exception::class,
                ]);
            }

            if (count($items) >= self::MAX_TOTAL_ENTRIES) {
                break;
            }
        }

        return [
            'items' => array_slice($items, 0, self::MAX_TOTAL_ENTRIES),
            'errors' => $errors,
        ];
    }

    /**
     * @param  list<string>  $domains
     */
    private function approvedHostForUrl(string $url, array $domains): string
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || $host === ''
            || str_ends_with($host, '.')
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['fragment'])
            || (isset($parts['port']) && $parts['port'] !== 443)
            || filter_var($host, FILTER_VALIDATE_IP)
            || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('The feed URL is not a valid HTTPS address.');
        }

        if ($this->hasSensitiveQuery($url)) {
            throw new RuntimeException('Feed URLs must not contain credentials or signed access tokens.');
        }

        if (! $this->hostIsApproved($host, $domains)) {
            throw new RuntimeException('The feed host is not on the approved source list.');
        }

        return $host;
    }

    /**
     * @param  list<string>  $domains
     */
    private function hostIsApproved(string $host, array $domains): bool
    {
        return in_array($host, array_map(fn (string $domain) => strtolower($domain), $domains), true);
    }

    private function requestFeed(string $url, string $host): string
    {
        if (! defined('CURLOPT_RESOLVE')) {
            throw new RuntimeException('The cURL extension is required for safe feed fetching.');
        }

        $records = dns_get_record($host, DNS_A | DNS_AAAA);
        $addresses = [];
        foreach (is_array($records) ? $records : [] as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;
            if (is_string($address) && filter_var($address, FILTER_VALIDATE_IP)) {
                $addresses[] = $address;
            }
        }
        $addresses = array_values(array_unique($addresses));

        if ($addresses === []) {
            throw new RuntimeException('The approved feed host did not resolve to a public address.');
        }

        foreach ($addresses as $address) {
            if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw new RuntimeException('The approved feed host resolves to a non-public address.');
            }
        }

        $address = $addresses[0];
        $pinnedAddress = str_contains($address, ':') ? '['.$address.']' : $address;
        $response = Http::accept('application/rss+xml, application/atom+xml, application/xml, text/xml')
            ->connectTimeout(3)
            ->timeout(5)
            ->withOptions([
                'allow_redirects' => false,
                'stream' => true,
                'curl' => [
                    CURLOPT_RESOLVE => [$host.':443:'.$pinnedAddress],
                ],
            ])
            ->get($url);

        if ($response->redirect()) {
            throw new RuntimeException('Feed redirects are not followed; add the final HTTPS URL instead.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('The feed server returned HTTP '.$response->status().'.');
        }

        $contentLength = $response->header('Content-Length');
        if (ctype_digit($contentLength) && (int) $contentLength > self::MAX_FEED_BYTES) {
            throw new RuntimeException('The feed exceeds the 1 MB size limit.');
        }

        $stream = $response->toPsrResponse()->getBody();
        $body = '';
        while (! $stream->eof()) {
            $body .= $stream->read(65536);
            if (strlen($body) > self::MAX_FEED_BYTES) {
                throw new RuntimeException('The feed exceeds the 1 MB size limit.');
            }
        }

        if ($body === '' || stripos($body, '<!DOCTYPE') !== false) {
            throw new RuntimeException('The feed is empty or contains a disallowed document type.');
        }

        return $body;
    }

    /**
     * @param  list<string>  $domains
     * @return list<array{title: string, summary: string, url: string, published_at: ?string, source_host: string}>
     */
    private function parseFeed(string $xml, array $domains): array
    {
        $previousErrorSetting = libxml_use_internal_errors(true);

        try {
            $document = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);
        }

        if (! $document instanceof SimpleXMLElement) {
            throw new RuntimeException('The feed did not contain valid XML.');
        }

        $entries = [];
        $rootName = strtolower($document->getName());
        if ($rootName === 'feed') {
            $atom = $document->children('http://www.w3.org/2005/Atom');
            foreach ($atom->entry ?? [] as $entry) {
                $entries[] = $this->atomEntry($entry);
            }
        } else {
            foreach ($document->channel->item ?? [] as $entry) {
                $entries[] = [
                    'title' => (string) ($entry->title ?? ''),
                    'summary' => (string) ($entry->description ?? ''),
                    'url' => (string) ($entry->link ?? ''),
                    'date' => (string) ($entry->pubDate ?? ''),
                ];
            }
        }

        $result = [];
        foreach (array_slice($entries, 0, self::MAX_ENTRIES_PER_FEED) as $entry) {
            $title = $this->plainText($entry['title'], 240);
            $summary = $this->plainText($entry['summary'], 1800);
            $url = trim($entry['url']);
            if ($title === '' || $summary === '' || ! $this->isApprovedArticleUrl($url, $domains)) {
                continue;
            }

            $parts = parse_url($url);
            $date = trim($entry['date']);
            $publishedAt = $date !== '' && strtotime($date) !== false
                ? gmdate('c', strtotime($date))
                : null;

            $result[] = [
                'title' => $title,
                'summary' => $summary,
                'url' => $url,
                'published_at' => $publishedAt,
                'source_host' => strtolower((string) ($parts['host'] ?? '')),
            ];
        }

        return $result;
    }

    /**
     * @return array{title: string, summary: string, url: string, date: string}
     */
    private function atomEntry(SimpleXMLElement $entry): array
    {
        $link = '';
        foreach ($entry->children('http://www.w3.org/2005/Atom')->link ?? [] as $candidate) {
            $attributes = $candidate->attributes();
            if ((string) ($attributes['rel'] ?? 'alternate') === 'alternate') {
                $link = (string) ($attributes['href'] ?? '');
                break;
            }
        }

        $atom = $entry->children('http://www.w3.org/2005/Atom');

        return [
            'title' => (string) ($atom->title ?? ''),
            'summary' => (string) ($atom->summary ?? $atom->content ?? ''),
            'url' => $link,
            'date' => (string) ($atom->published ?? $atom->updated ?? ''),
        ];
    }

    private function isApprovedArticleUrl(string $url, array $domains): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && $parts['port'] !== 443)
            || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        return ! str_ends_with($host, '.')
            && ! $this->hasSensitiveQuery($url)
            && $this->hostIsApproved($host, $domains);
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

    private function plainText(string $value, int $maxLength): string
    {
        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));

        return mb_substr($text, 0, $maxLength);
    }
}
