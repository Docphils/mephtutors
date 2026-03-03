<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class PrivacyPolicyPage extends Component
{
    public function render()
    {
        $title = 'Privacy Policy | MephEd';
        $description = 'Read MephEd privacy policy, including what information we collect, how we use it, and your data rights.';

        return view('livewire.pages.privacy-policy-page')
            ->title($title)
            ->layoutData([
                'metaDescription' => $description,
                'canonicalUrl' => route('privacy-policy'),
                'ogType' => 'article',
                'ogImage' => asset('images/MephEd.png'),
            ]);
    }
}
