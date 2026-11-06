<?php

namespace App\Services;

use App\Enums\EbookStatus;
use App\Models\Ebook;
use App\Models\ServiceItem;

class BlogEditorialContext
{
    /**
     * @return list<array{type: string, title: string, summary: string, url: string}>
     */
    public function internalResources(): array
    {
        $ebooks = Ebook::query()
            ->where('status', EbookStatus::Published->value)
            ->latest('updated_at')
            ->limit(10)
            ->get(['title', 'slug', 'subtitle', 'description', 'audience', 'primary_keyword']);
        $services = ServiceItem::query()
            ->where('is_active', true)
            ->orderBy('display_position')
            ->limit(10)
            ->get(['name', 'slug', 'description']);

        $resources = [];
        foreach ($ebooks as $ebook) {
            $resources[] = [
                'type' => 'eBook',
                'title' => $ebook->title,
                'summary' => trim(implode(' ', array_filter([
                    $ebook->subtitle,
                    $ebook->description,
                    $ebook->audience,
                    $ebook->primary_keyword,
                ]))),
                'url' => route('ebooks.show', ['slug' => $ebook->slug]),
            ];
        }
        foreach ($services as $service) {
            $resources[] = [
                'type' => 'service',
                'title' => $service->name,
                'summary' => (string) $service->description,
                'url' => route('services.show', ['serviceItem' => $service->slug]),
            ];
        }

        return array_map(fn (array $resource) => [
            ...$resource,
            'summary' => mb_substr(strip_tags($resource['summary']), 0, 700),
        ], $resources);
    }
}
