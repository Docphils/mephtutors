<?php

namespace App\Livewire\Ebooks;

use App\Models\EbookDownload;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class EbookDownloadExpiredPage extends Component
{
    public string $token;

    public function mount(string $token): void
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token), 404);
        abort_unless(EbookDownload::query()->where('token_hash', hash('sha256', $token))->exists(), 404);
        $this->token = $token;
    }

    public function render()
    {
        return view('livewire.ebooks.download-expired')
            ->title('Download link unavailable | MephEd')
            ->layoutData([
                'metaRobots' => 'noindex,nofollow',
                'canonicalUrl' => route('welcome'),
            ]);
    }
}
