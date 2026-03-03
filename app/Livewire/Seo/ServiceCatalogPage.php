<?php

namespace App\Livewire\Seo;

use App\Models\Service;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class ServiceCatalogPage extends Component
{
    public function render()
    {
        $services = Service::query()
            ->where('is_active', true)
            ->with(['serviceItems' => fn($q) => $q
                ->where('is_active', true)
                ->orderBy('display_position')
                ->orderBy('name')])
            ->orderBy('name')
            ->get();

        $title = 'MephEd Services | Tutoring, Coding, Clubs and Consultation';
        $description = 'Explore MephEd services for tutoring, coding and IT training, school club management, and education consultation. Find the right service and get started.';

        return view('livewire.seo.service-catalog-page', [
            'services' => $services,
        ])->title($title)->layoutData([
            'metaDescription' => $description,
            'canonicalUrl' => route('services'),
            'ogType' => 'website',
        ]);
    }
}
