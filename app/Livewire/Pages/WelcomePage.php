<?php

namespace App\Livewire\Pages;

use App\Models\AcademicProgramme;
use App\Models\SiteSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class WelcomePage extends Component
{
    public function render()
    {
        $mode = (string) SiteSetting::getValue('homepage_mode', 'default');

        if ($mode === 'academic') {
            $title = 'Private Academic Support That Improves Results | MephEd';
            $description = 'Exam preparation, term-long support, and focused subject intervention through online or home lessons.';
            $programmes = AcademicProgramme::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $structuredData = json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'description' => $description,
                'url' => route('welcome'),
                'mainEntity' => $programmes->map(fn ($programme) => [
                    '@type' => 'Service',
                    'name' => $programme->name,
                    'description' => $programme->summary ?: $programme->tagline,
                    'url' => route('programmes.show', ['academicProgramme' => $programme->slug]),
                ])->values()->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return view('livewire.pages.welcome-page-academic', [
                'programmes' => $programmes,
                'trialClassEnabled' => SiteSetting::getBoolean('programme_trial_class_enabled', true),
                'probationaryAllowed' => SiteSetting::getBoolean('programme_probationary_classes_allowed', true),
                'pricingDefaultNote' => (string) SiteSetting::getValue(
                    'programme_pricing_note_default',
                    'Pricing depends on frequency, session duration, mode, and location.'
                ),
            ])->title($title)->layoutData([
                'metaDescription' => $description,
                'canonicalUrl' => route('welcome'),
                'ogType' => 'website',
                'ogImage' => asset('images/banner.jpg'),
                'structuredData' => $structuredData,
            ]);
        }

        $title = 'MephEd - Get Tutors Online';
        $description = "Welcome to Nigeria's foremost tutor matching platform. We match learners with exceptional tutors for all subjects and levels, coding classes, and co-curricular clubs.";

        return view('livewire.pages.welcome-page')
            ->title($title)
            ->layoutData([
                'metaDescription' => $description,
                'canonicalUrl' => route('welcome'),
                'ogType' => 'website',
                'ogImage' => asset('images/banner.jpg'),
            ]);
    }
}
