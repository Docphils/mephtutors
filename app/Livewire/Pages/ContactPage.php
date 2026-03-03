<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class ContactPage extends Component
{
    public function render()
    {
        $title = "Contact MephEd - Nigeria's Best Tutoring Company";
        $description = 'Contact MephEd for tutoring, coding classes, school partnerships, and support. Our team will respond promptly.';

        return view('livewire.pages.contact-page')
            ->title($title)
            ->layoutData([
                'metaDescription' => $description,
                'canonicalUrl' => route('contact'),
                'ogType' => 'website',
                'ogImage' => asset('images/MephEd.png'),
            ]);
    }
}
