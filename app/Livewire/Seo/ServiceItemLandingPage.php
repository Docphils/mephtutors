<?php

namespace App\Livewire\Seo;

use App\Models\ServiceItem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class ServiceItemLandingPage extends Component
{
    public ServiceItem $serviceItem;

    public function mount(ServiceItem $serviceItem): void
    {
        abort_unless($serviceItem->is_active, 404);
        $this->serviceItem = $serviceItem->load('service');
    }

    public function getApplyUrlProperty(): string
    {
        if ($this->serviceItem->target === 'institutions') {
            return route('apply.crm', ['serviceItem' => $this->serviceItem->slug]);
        }

        if ($this->serviceItem->target === 'bootcamp') {
            return route('apply.bootcamp', ['serviceItem' => $this->serviceItem->slug]);
        }

        return route('apply.tutor', ['serviceItem' => $this->serviceItem->slug]);
    }

    public function render()
    {
        $relatedItems = ServiceItem::query()
            ->where('is_active', true)
            ->where('service_id', $this->serviceItem->service_id)
            ->where('id', '!=', $this->serviceItem->id)
            ->orderBy('display_position')
            ->limit(4)
            ->get();

        $title = sprintf(
            '%s in Nigeria | %s | MephEd',
            $this->serviceItem->name,
            $this->serviceItem->service?->name ?? 'Education Services'
        );

        $description = $this->serviceItem->description
            ? trim($this->serviceItem->description) . ' Book with MephEd today.'
            : 'Learn more about this MephEd service and get started today.';

        $structuredData = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $this->serviceItem->name,
            'description' => strip_tags((string) $this->serviceItem->description),
            'provider' => [
                '@type' => 'Organization',
                'name' => 'MephEd',
                'url' => url('/'),
            ],
            'serviceType' => $this->serviceItem->service?->name,
            'areaServed' => 'Nigeria',
            'url' => route('services.show', ['serviceItem' => $this->serviceItem->slug]),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('livewire.seo.service-item-landing-page', [
            'relatedItems' => $relatedItems,
        ])->title($title)->layoutData([
            'metaDescription' => $description,
            'canonicalUrl' => route('services.show', ['serviceItem' => $this->serviceItem->slug]),
            'ogType' => 'article',
            'ogImage' => $this->serviceItem->image_url,
            'structuredData' => $structuredData,
        ]);
    }
}
