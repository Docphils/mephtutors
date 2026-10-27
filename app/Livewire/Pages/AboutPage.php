<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class AboutPage extends Component
{
    public function render()
    {
        $title = 'About MephEd | Learning, Software and Publishing';
        $description = 'Learn about MephEd, a Nigerian education, technology, and publishing company building learning services and practical digital products.';

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
