<div class="min-h-screen bg-slate-100 p-4 sm:p-6 lg:p-8">
    <x-slot name="header"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Digital publishing</p><h1 class="text-2xl font-black text-slate-900">eBook Orders</h1></div></x-slot>
    <div class="mx-auto max-w-7xl space-y-5">
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4"><p class="text-sm text-slate-600">Order history is retained for reconciliation.</p><label class="text-sm font-semibold text-slate-700">Filter status<select wire:model.live="statusFilter" class="ml-2 rounded-lg border border-slate-300"><option value="">All</option>@foreach($statuses as $status)<option value="{{ $status->value }}">{{ ucfirst($status->value) }}</option>@endforeach</select></label></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[800px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Reference</th><th class="px-4 py-3">eBook</th><th class="px-4 py-3">Buyer</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Created</th><th class="px-4 py-3">Action</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr><td class="px-4 py-3 font-mono text-xs">{{ $order->reference }}</td><td class="px-4 py-3">{{ $order->ebook->title }}</td><td class="px-4 py-3">{{ $order->buyer_name }}<span class="block text-xs text-slate-500">{{ $order->buyer_email }}</span></td><td class="px-4 py-3">₦{{ number_format($order->amount_kobo / 100) }}</td><td class="px-4 py-3">{{ ucfirst($order->status->value) }}</td><td class="px-4 py-3">{{ $order->created_at->format('d M Y H:i') }}</td><td class="space-y-2 px-4 py-3">@if($order->status === \App\Enums\EbookOrderStatus::Paid)@if($order->fulfilled_at)<button wire:click="resendDownloadLink({{ $order->id }})" wire:confirm="Queue a fresh link to this buyer's order email?" class="block font-bold text-cyan-800 hover:underline">Resend link</button>@endif<button wire:click="markRefunded({{ $order->id }})" wire:confirm="Record this order as refunded? Issue the actual refund in Paystack first." class="block font-bold text-rose-700 hover:underline">Mark refunded</button>@else<span class="text-slate-400">—</span>@endif</td></tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">No orders found.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="p-4">{{ $orders->links() }}</div>
        </section>
    </div>
</div>
