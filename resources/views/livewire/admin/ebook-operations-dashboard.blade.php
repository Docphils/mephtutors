<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div><h1 class="text-2xl font-bold">eBook operations</h1><p class="text-sm text-slate-600">Sales, payment, delivery and acquisition health.</p></div>
    <section class="grid gap-4 md:grid-cols-3">
        @foreach ([1 => 'Today', 7 => '7 days', 30 => '30 days'] as $days => $label)
            <article class="rounded-lg border bg-white p-4"><h2 class="font-semibold">{{ $label }}</h2><p>Paid orders: {{ $sales[$days]['count'] }}</p><p>Revenue: ₦{{ number_format($sales[$days]['revenue'] / 100, 2) }}</p><p>Average order: ₦{{ number_format($sales[$days]['average'] / 100, 2) }}</p></article>
        @endforeach
    </section>
    <section class="grid gap-4 md:grid-cols-4">
        <article class="rounded-lg border bg-white p-4"><h2 class="font-semibold">Order states</h2>@foreach (['pending','paid','failed','refunded'] as $state)<p>{{ ucfirst($state) }}: {{ $counts[$state] ?? 0 }}</p>@endforeach</article>
        <article class="rounded-lg border bg-white p-4"><h2 class="font-semibold">Payment health</h2><p>Overdue pending: {{ $pending }}</p><p>Transaction mismatches (30d): {{ $paymentMismatches }}</p><p>Webhook rejections (30d): {{ $webhookRejected }}</p><p>Last webhook: {{ $lastWebhook ? \Illuminate\Support\Carbon::parse($lastWebhook)->diffForHumans() : 'Never' }}</p></article>
        <article class="rounded-lg border bg-white p-4"><h2 class="font-semibold">Delivery health</h2><p>Paid, unfulfilled: {{ $unfulfilled }}</p><p>Oldest unpaid delivery: {{ $oldestUnfulfilled ? \Illuminate\Support\Carbon::parse($oldestUnfulfilled)->diffForHumans() : 'None' }}</p><p>Failed delivery jobs/events (30d): {{ $deliveryFailures }}</p><p>Queue: {{ config('ebooks.queue_connection') }} · backlog: {{ $queueBacklog ?? 'not exposed' }}</p></article>
        <article class="rounded-lg border bg-white p-4"><h2 class="font-semibold">Scheduler</h2><p>Last run: {{ $lastRun?->finished_at?->diffForHumans() ?? 'Never' }}</p><p>Status: {{ $lastRun?->status ?? 'never_run' }}</p><p>Recovered: {{ $lastRun?->recovered_count ?? 0 }}</p></article>
    </section>
    <section class="grid gap-4 md:grid-cols-2">
        <article class="rounded-lg border bg-white p-4"><h2 class="mb-2 font-semibold">Sales by eBook (30 days)</h2>@forelse ($perBook as $row)<p>{{ $row->ebook?->title ?? 'Deleted eBook' }} — {{ $row->paid_orders }} paid / {{ $row->orders }} orders, ₦{{ number_format($row->revenue / 100, 2) }}</p>@empty<p>No orders yet.</p>@endforelse</article>
        <article class="rounded-lg border bg-white p-4"><h2 class="mb-2 font-semibold">Funnel (30 days)</h2>@foreach ($funnel->groupBy('ebook_id') as $rows)@php $views = (int) ($rows->firstWhere('type', 'landing_view')?->aggregate ?? 0); $checkouts = (int) ($rows->firstWhere('type', 'checkout_started')?->aggregate ?? 0); $paid = (int) ($rows->firstWhere('type', 'payment_succeeded')?->aggregate ?? 0); @endphp<h3 class="mt-2 font-medium">{{ $rows->first()->ebook?->title ?? 'eBook' }}</h3><p>Landing views: {{ $views }}</p><p>Checkout started: {{ $checkouts }} ({{ $views ? number_format($checkouts / $views * 100, 1) : '0.0' }}%)</p><p>Paid: {{ $paid }} ({{ $checkouts ? number_format($paid / $checkouts * 100, 1) : '0.0' }}%)</p>@endforeach</article>
    </section>
    <section class="grid gap-4 md:grid-cols-2">
        <article class="rounded-lg border bg-white p-4"><h2 class="font-semibold">Downloads</h2><p>Download events (30d): {{ $downloads }} · Resends: {{ $resends }}</p><p>Expired or exhausted links: {{ $expiredLinks }}</p></article>
        <article class="rounded-lg border bg-white p-4"><h2 class="font-semibold">Traffic sources (30d)</h2>@forelse ($traffic as $source => $numbers)<p>{{ $source }}: {{ $numbers['orders'] }} orders · {{ $numbers['paid'] }} paid ({{ $numbers['orders'] ? number_format($numbers['paid'] / $numbers['orders'] * 100, 1) : '0.0' }}%)</p>@empty<p>No orders yet.</p>@endforelse</article>
    </section>
</div>
