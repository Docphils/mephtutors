<?php

namespace App\Livewire\Ebooks;

use App\Enums\EbookOrderStatus;
use App\Models\EbookOrder;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class EbookThankYouPage extends Component
{
    public EbookOrder $order;

    public ?string $downloadUrl = null;

    public ?string $resendUrl = null;

    public function mount(string $reference): void
    {
        $this->order = EbookOrder::query()->with('ebook', 'download')->where('reference', $reference)->firstOrFail();
        abort_unless($this->order->status === EbookOrderStatus::Paid, 404);

        if ($this->order->fulfilled_at && $this->order->download
            && $this->order->download->expires_at->isFuture()
            && $this->order->download->download_count < $this->order->download->max_downloads) {
            $this->downloadUrl = URL::temporarySignedRoute(
                'ebooks.thank-you.download',
                min($this->order->download->expires_at, now()->addMinutes(config('ebooks.thank_you_url_minutes'))),
                ['reference' => $this->order->reference],
            );
        }

        if ($this->order->fulfilled_at && ! $this->downloadUrl) {
            $this->resendUrl = URL::temporarySignedRoute(
                'ebooks.thank-you.resend',
                now()->addMinutes(config('ebooks.thank_you_url_minutes')),
                ['reference' => $this->order->reference],
            );
        }
    }

    public function render()
    {
        return view('livewire.ebooks.thank-you')
            ->title('Thank you | MephEd eBooks')
            ->layoutData([
                'metaRobots' => 'noindex,nofollow',
                'metaDescription' => 'Your MephEd eBook order status.',
                'canonicalUrl' => route('welcome'),
            ]);
    }
}
