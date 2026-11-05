<?php

namespace App\Livewire\Admin;

use App\Enums\EbookOrderStatus;
use App\Jobs\ResendEbookDownloadLink;
use App\Models\EbookOrder;
use App\Services\EbookDownloadLinkService;
use App\Services\EbookOrderLifecycle;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('eBook Orders - MephEd Admin')]
class EbookOrdersManager extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function markRefunded(int $orderId, EbookOrderLifecycle $lifecycle): void
    {
        Gate::authorize('Admin');
        $order = EbookOrder::query()->findOrFail($orderId);
        $lifecycle->transition($order, EbookOrderStatus::Refunded);
        session()->flash('success', 'Order marked as refunded. This records the status only; issue any refund through Paystack separately.');
    }

    public function resendDownloadLink(int $orderId, EbookDownloadLinkService $links): void
    {
        Gate::authorize('Admin');
        $generation = $links->rotateForOrder($orderId);
        if ($generation === null) {
            session()->flash('success', 'This paid order is not ready for a download link.');

            return;
        }

        ResendEbookDownloadLink::dispatch($orderId, $generation)
            ->onConnection(config('ebooks.queue_connection'))
            ->onQueue('ebooks')
            ->afterCommit();
        session()->flash('success', 'A fresh download link has been queued for the buyer email on this order.');
    }

    public function render()
    {
        Gate::authorize('Admin');

        $query = EbookOrder::query()->with('ebook')->latest();
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.admin.ebook-orders-manager', [
            'orders' => $query->paginate(25),
            'statuses' => EbookOrderStatus::cases(),
        ]);
    }
}
