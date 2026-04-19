<?php

namespace App\Livewire\Pages;

use App\Models\AcademicProgramme;
use App\Models\SiteSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class ProgrammePage extends Component
{
    public AcademicProgramme $academicProgramme;

    public function mount(AcademicProgramme $academicProgramme): void
    {
        abort_unless($academicProgramme->is_active, 404);
        $this->academicProgramme = $academicProgramme;
    }

    public function render()
    {
        $programme = $this->academicProgramme;
        $title = $programme->meta_title ?: ($programme->name . ' Intervention | MephEd');
        $description = $programme->meta_description ?: ($programme->summary ?: $programme->tagline);

        $structuredData = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $programme->name,
            'description' => strip_tags((string) ($programme->summary ?: $programme->tagline)),
            'provider' => [
                '@type' => 'Organization',
                'name' => 'MephEd',
                'url' => url('/'),
            ],
            'areaServed' => 'Nigeria',
            'url' => route('programmes.show', ['academicProgramme' => $programme->slug]),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('livewire.pages.programme-page', [
            'programme' => $programme,
            'trialClassEnabled' => SiteSetting::getBoolean('programme_trial_class_enabled', true),
            'probationaryAllowed' => SiteSetting::getBoolean('programme_probationary_classes_allowed', true),
            'defaultPricingNote' => (string) SiteSetting::getValue(
                'programme_pricing_note_default',
                'Pricing depends on frequency, duration, mode, and location for physical lessons.'
            ),
        ])->title($title)->layoutData([
            'metaDescription' => $description,
            'canonicalUrl' => route('programmes.show', ['academicProgramme' => $programme->slug]),
            'ogType' => 'article',
            'ogImage' => $programme->og_image ? asset($programme->og_image) : asset('images/banner.jpg'),
            'structuredData' => $structuredData,
        ]);
    }
}
