<?php

namespace App\Livewire\Pages;

use App\Models\ServiceItem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class BootcampPage extends Component
{
    public ?string $serviceItemSlug = null;

    public function mount(?ServiceItem $serviceItem = null): void
    {
        if ($serviceItem) {
            abort_unless($serviceItem->target === 'bootcamp', 404);
            $this->serviceItemSlug = $serviceItem->slug;
        }
    }

    public function render()
    {
        $title = 'MephEd Learning - Enroll';
        $description = 'Enroll to learn in-demand educational and technology skills with MephEd Learning bootcamps.';

        return view('livewire.pages.bootcamp-page', [
            'serviceItemSlug' => $this->serviceItemSlug,
        ])->title($title)->layoutData([
            'metaDescription' => $description,
            'canonicalUrl' => $this->serviceItemSlug
                ? route('apply.bootcamp', ['serviceItem' => $this->serviceItemSlug])
                : route('bootcamp'),
            'ogType' => 'website',
            'ogImage' => asset('images/banner.jpg'),
        ]);
    }
}
