<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class WelcomePage extends Component
{
    public function render()
    {
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
