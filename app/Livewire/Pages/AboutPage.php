<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class AboutPage extends Component
{
    public function render()
    {
        $title = 'About MephEd | Vision, Mission and Team';
        $description = 'Learn about MephEd, our education vision, mission, commitments, and the team transforming learning outcomes.';

        return view('livewire.pages.about-page')
            ->title($title)
            ->layoutData([
                'metaDescription' => $description,
                'canonicalUrl' => route('about'),
                'ogType' => 'article',
                'ogImage' => asset('images/teacher.png'),
            ]);
    }
}
