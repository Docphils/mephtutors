<?php

namespace App\Livewire\Ebooks;

use App\Enums\EbookStatus;
use App\Models\Ebook;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class EbookHubPage extends Component
{
    public function render()
    {
        return view('livewire.ebooks.hub', [
            'ebooks' => Ebook::query()->where('status', EbookStatus::Published)->orderBy('title')->get(),
        ])->title('eBooks | MephEd')->layoutData([
            'metaDescription' => 'Explore practical eBooks published by MephEd.',
            'canonicalUrl' => route('ebooks.index'),
        ]);
    }
}
