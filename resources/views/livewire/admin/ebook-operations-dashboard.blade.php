<div class="min-h-full bg-slate-50/70">
    <main class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:space-y-8 sm:px-6 sm:py-8 lg:px-8">
        <section
            class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-cyan-950 to-cyan-800 px-5 py-7 text-white shadow-xl shadow-cyan-950/10 sm:px-8 sm:py-9">
            <div class="absolute -right-12 -top-20 -z-10 h-64 w-64 rounded-full bg-cyan-400/20 blur-3xl"
                aria-hidden="true"></div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-200">Digital publishing</p>
            <div class="mt-2 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight sm:text-3xl">eBook operations</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-cyan-50/80">Sales, payments, delivery and acquisition
                        health at a glance.</p>
                </div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-cyan-50">
                    <span class="font-bold text-white">30 day view</span><span
                        class="mx-2 text-cyan-300">·</span>Updated with the latest activity
                </div>
            </div>
        </section>

        <section aria-label="Sales summary" class="grid gap-4 sm:grid-cols-3">
            @foreach ([1 => 'Today', 7 => 'Last 7 days', 30 => 'Last 30 days'] as $days => $label)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-cyan-50 text-cyan-800"><i
                                class="fas fa-chart-line" aria-hidden="true"></i></span>
                    </div>
                    <p class="mt-3 text-3xl font-black tracking-tight text-slate-950">
                        ₦{{ number_format($sales[$days]['revenue'] / 100, 2) }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-500">{{ number_format($sales[$days]['count']) }} paid
                        orders</p>
                    <div class="mt-4 flex justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                        <span>Average order</span><span
                            class="font-bold text-slate-800">₦{{ number_format($sales[$days]['average'] / 100, 2) }}</span>
                    </div>
                </article>
            @endforeach
        </section>

        <section aria-label="Operations health" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-3"><span
                        class="grid h-10 w-10 place-items-center rounded-xl bg-violet-50 text-violet-700"><i
                            class="fas fa-receipt" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="font-bold text-slate-900">Order states</h2>
                        <p class="text-xs text-slate-500">All recorded orders</p>
                    </div>
                </div>
                <div class="space-y-2.5">
                    @foreach (['pending' => 'bg-amber-500', 'paid' => 'bg-emerald-500', 'failed' => 'bg-rose-500', 'refunded' => 'bg-slate-400'] as $state => $dotClass)
                        <div class="flex items-center justify-between text-sm"><span
                                class="flex items-center gap-2 text-slate-600"><span
                                    class="h-2 w-2 rounded-full {{ $dotClass }}"></span>{{ ucfirst($state) }}</span><span
                                class="font-bold tabular-nums text-slate-900">{{ number_format($counts[$state] ?? 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-3"><span
                        class="grid h-10 w-10 place-items-center rounded-xl bg-sky-50 text-sky-700"><i
                            class="fas fa-shield-alt" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="font-bold text-slate-900">Payment health</h2>
                        <p class="text-xs text-slate-500">Gateway and webhook checks</p>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Overdue pending</span><strong
                            class="text-slate-900">{{ number_format($pending) }}</strong></div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Transaction mismatches
                            <small>(30d)</small></span><strong
                            class="text-slate-900">{{ number_format($paymentMismatches) }}</strong></div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Webhook rejections
                            <small>(30d)</small></span><strong
                            class="text-slate-900">{{ number_format($webhookRejected) }}</strong></div>
                    <div class="border-t border-slate-100 pt-3 text-xs text-slate-500">Last webhook <strong
                            class="text-slate-700">{{ $lastWebhook ? \Illuminate\Support\Carbon::parse($lastWebhook)->diffForHumans() : 'Never' }}</strong>
                    </div>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-3"><span
                        class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-amber-700"><i
                            class="fas fa-paper-plane" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="font-bold text-slate-900">Delivery health</h2>
                        <p class="text-xs text-slate-500">Fulfilment and queue</p>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Paid, unfulfilled</span><strong
                            class="text-slate-900">{{ number_format($unfulfilled) }}</strong></div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Oldest waiting</span><strong
                            class="text-slate-900">{{ $oldestUnfulfilled ? \Illuminate\Support\Carbon::parse($oldestUnfulfilled)->diffForHumans() : 'None' }}</strong>
                    </div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Failed jobs/events
                            <small>(30d)</small></span><strong
                            class="text-slate-900">{{ number_format($deliveryFailures) }}</strong></div>
                    <div class="border-t border-slate-100 pt-3 text-xs text-slate-500">Queue <strong
                            class="text-slate-700">{{ config('ebooks.queue_connection') }}</strong><span
                            class="mx-1">·</span>backlog <strong
                            class="text-slate-700">{{ $queueBacklog ?? 'not exposed' }}</strong></div>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-3"><span
                        class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-50 text-indigo-700"><i
                            class="fas fa-clock" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="font-bold text-slate-900">Scheduler</h2>
                        <p class="text-xs text-slate-500">Recovery task status</p>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Last run</span><strong
                            class="text-right text-slate-900">{{ $lastRun?->finished_at?->diffForHumans() ?? 'Never' }}</strong>
                    </div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Status</span><span
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $lastRun?->status ?? 'never_run' }}</span>
                    </div>
                    <div class="flex justify-between gap-3"><span class="text-slate-500">Recovered</span><strong
                            class="text-slate-900">{{ number_format($lastRun?->recovered_count ?? 0) }}</strong></div>
                </div>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-900">Sales by eBook</h2>
                    <p class="mt-1 text-xs text-slate-500">Performance over the last 30 days</p>
                </div>
                @forelse ($perBook as $row)
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 last:border-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">
                                {{ $row->ebook?->title ?? 'Deleted eBook' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ number_format($row->paid_orders) }} paid /
                                {{ number_format($row->orders) }} orders</p>
                        </div>
                        <p class="shrink-0 text-sm font-bold text-slate-900">
                            ₦{{ number_format($row->revenue / 100, 2) }}</p>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">No orders yet.</p>
                @endforelse
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h2 class="font-bold text-slate-900">Conversion funnel</h2>
                    <p class="mt-1 text-xs text-slate-500">Landing page to successful payment · last 30 days</p>
                </div>
                <div class="space-y-5">
                    @forelse ($funnel->groupBy('ebook_id') as $rows)
                        @php
                            $views = (int) ($rows->firstWhere('type', 'landing_view')?->aggregate ?? 0);
                            $checkouts = (int) ($rows->firstWhere('type', 'checkout_started')?->aggregate ?? 0);
                            $paid = (int) ($rows->firstWhere('type', 'payment_succeeded')?->aggregate ?? 0);
                            $checkoutRate = $views ? ($checkouts / $views) * 100 : 0;
                            $paidRate = $checkouts ? ($paid / $checkouts) * 100 : 0;
                        @endphp
                        <div class="rounded-xl bg-slate-50 p-4">
                            <h3 class="truncate text-sm font-bold text-slate-800">
                                {{ $rows->first()->ebook?->title ?? 'eBook' }}</h3>
                            <div class="mt-3 grid grid-cols-3 gap-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Views
                                    </p>
                                    <p class="mt-1 text-lg font-black tabular-nums text-slate-900">
                                        {{ number_format($views) }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Checkout</p>
                                    <p class="mt-1 text-lg font-black tabular-nums text-slate-900">
                                        {{ number_format($checkouts) }}</p>
                                    <p class="text-xs font-semibold text-cyan-700">
                                        {{ number_format($checkoutRate, 1) }}% of views</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Paid
                                    </p>
                                    <p class="mt-1 text-lg font-black tabular-nums text-slate-900">
                                        {{ number_format($paid) }}</p>
                                    <p class="text-xs font-semibold text-emerald-700">
                                        {{ number_format($paidRate, 1) }}% of checkout</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-slate-500">No funnel activity yet.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center gap-3"><span
                        class="grid h-10 w-10 place-items-center rounded-xl bg-rose-50 text-rose-700"><i
                            class="fas fa-download" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="font-bold text-slate-900">Downloads</h2>
                        <p class="text-xs text-slate-500">Access and resend activity</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <p class="text-xs text-slate-500">Events <small>(30d)</small></p>
                        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($downloads) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Resends</p>
                        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($resends) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Expired links</p>
                        <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($expiredLinks) }}</p>
                    </div>
                </div>
            </article>
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-900">Traffic sources</h2>
                    <p class="mt-1 text-xs text-slate-500">Order attribution · last 30 days</p>
                </div>
                @forelse ($traffic as $source => $numbers)
                    @php $conversion = $numbers['orders'] ? ($numbers['paid'] / $numbers['orders']) * 100 : 0; @endphp
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-3.5 last:border-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $source }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ number_format($numbers['paid']) }} paid of
                                {{ number_format($numbers['orders']) }} orders</p>
                        </div>
                        <span
                            class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ number_format($conversion, 1) }}%</span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">No attributed orders yet.</p>
                @endforelse
            </article>
        </section>
    </main>
</div>
