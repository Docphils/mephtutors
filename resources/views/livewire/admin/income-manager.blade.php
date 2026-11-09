<div class="min-h-screen rounded-3xl bg-slate-50 p-4 sm:p-6 lg:p-8">
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-cyan-700 shadow-sm transition hover:bg-cyan-700 hover:text-white"
                    aria-label="Back to dashboard">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Income</h1>
                    <p class="mt-1 text-sm text-slate-500">Incoming gateway receipts and manually recorded income.</p>
                </div>
            </div>
            <button type="button" wire:click="openForm"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-700 px-5 py-3 font-bold text-white shadow-lg shadow-cyan-900/10 transition hover:bg-cyan-800 focus:outline-none focus:ring-4 focus:ring-cyan-700/20">
                <i class="fa-solid fa-plus"></i><span>Record income</span>
            </button>
        </div>
    </x-slot>

    @if (session()->has('message'))
        <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('message') }}
        </div>
    @endif

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Income summary">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Income this month</p>
            <p class="mt-2 text-2xl font-black text-slate-900">₦{{ number_format((float) $monthNairaTotal, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Based on recorded receipt dates</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">All-time recorded</p>
            <p class="mt-2 text-2xl font-black text-slate-900">₦{{ number_format((float) $allTimeNairaTotal, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">NGN entries, including records without dates</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Income records</p>
            <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($recordCount) }}</p>
            <p class="mt-1 text-xs text-slate-500">Gateway and manual receipts</p>
        </article>
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Historical method unknown</p>
            <p class="mt-2 text-2xl font-black text-amber-950">{{ number_format($unrecordedCount) }}</p>
            <p class="mt-1 text-xs text-amber-800">Legacy records are not classified as gateway or cash</p>
        </article>
    </section>

    <section class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-5" aria-label="Filter income">
        <label class="relative md:col-span-2 xl:col-span-2">
            <span class="sr-only">Search income</span>
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search payer, description, or reference"
                class="w-full rounded-xl border-slate-200 py-3 pl-11 pr-4 text-sm focus:border-cyan-600 focus:ring-cyan-600">
        </label>
        <label>
            <span class="sr-only">Filter by payment method</span>
            <select wire:model.live="methodFilter" class="w-full rounded-xl border-slate-200 py-3 text-sm focus:border-cyan-600 focus:ring-cyan-600">
                <option value="">All methods</option>
                <option value="paystack">Paystack</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank transfer</option>
                <option value="other">Other</option>
                <option value="unrecorded">Historical method unknown</option>
            </select>
        </label>
        <label>
            <span class="sr-only">From date</span>
            <input type="date" wire:model.live="dateFrom" aria-label="From date"
                class="w-full rounded-xl border-slate-200 py-3 text-sm focus:border-cyan-600 focus:ring-cyan-600">
        </label>
        <label>
            <span class="sr-only">To date</span>
            <input type="date" wire:model.live="dateTo" aria-label="To date"
                class="w-full rounded-xl border-slate-200 py-3 text-sm focus:border-cyan-600 focus:ring-cyan-600">
        </label>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="font-black text-slate-900">Incoming transactions</h2>
                <p class="mt-1 text-xs text-slate-500">Outgoing tutor payouts remain in Manage Payments.</p>
            </div>
            <span wire:loading class="text-xs font-semibold text-cyan-700">Updating…</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Received</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Income / payer</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Method</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">Reference</th>
                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        <tr wire:key="income-{{ $transaction->id }}" class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                @if ($transaction->received_at)
                                    {{ $transaction->received_at->format('M j, Y') }}
                                    <span class="block text-xs text-slate-400">{{ $transaction->received_at->format('g:i A') }}</span>
                                @else
                                    <span class="text-xs font-semibold text-amber-700">Date not recorded</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-900">{{ $transaction->description }}</p>
                                @if ($transaction->payer_name)
                                    <p class="mt-1 text-xs text-slate-500">{{ $transaction->payer_name }}</p>
                                @endif
                                @if ($transaction->notes)
                                    <p class="mt-1 max-w-md truncate text-xs text-slate-400" title="{{ $transaction->notes }}">{{ $transaction->notes }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-bold',
                                    'bg-emerald-100 text-emerald-800' => $transaction->method === 'paystack',
                                    'bg-blue-100 text-blue-800' => $transaction->method === 'bank_transfer',
                                    'bg-violet-100 text-violet-800' => $transaction->method === 'cash',
                                    'bg-slate-100 text-slate-700' => in_array($transaction->method, ['other', 'unrecorded'], true),
                                ])>
                                    {{ match ($transaction->method) {
                                        'bank_transfer' => 'Bank transfer',
                                        'unrecorded' => 'Historical · unknown',
                                        default => ucfirst($transaction->method),
                                    } }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-500">{{ $transaction->reference ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-black text-slate-900">
                                {{ $transaction->currency }} {{ number_format((float) $transaction->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <i class="fa-solid fa-arrow-down-to-line"></i>
                                </div>
                                <p class="mt-3 font-bold text-slate-700">No income records found</p>
                                <p class="mt-1 text-sm text-slate-500">Try changing your filters or add a manual receipt.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $transactions->links() }}</div>
    </section>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" role="dialog" aria-modal="true" aria-labelledby="income-form-title">
            <button type="button" class="fixed inset-0 cursor-default bg-slate-950/60 backdrop-blur-sm" wire:click="closeForm" aria-label="Close dialog"></button>
            <form wire:submit="store" class="relative my-auto w-full max-w-2xl rounded-3xl bg-white p-5 shadow-2xl sm:p-8">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="income-form-title" class="text-xl font-black text-slate-900">Record manual income</h2>
                        <p class="mt-1 text-sm text-slate-500">For cash, bank transfers, and other receipts not automatically recorded by Paystack.</p>
                    </div>
                    <button type="button" wire:click="closeForm" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="mb-1.5 block text-sm font-bold text-slate-700">Description <span class="text-rose-600">*</span></span>
                        <input wire:model="description" type="text" maxlength="255" required placeholder="e.g. Programme tuition payment"
                            class="w-full rounded-xl border-slate-200 focus:border-cyan-600 focus:ring-cyan-600">
                        @error('description') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>
                    <label>
                        <span class="mb-1.5 block text-sm font-bold text-slate-700">Amount (NGN) <span class="text-rose-600">*</span></span>
                        <input wire:model="amount" type="number" step="0.01" min="0.01" required
                            class="w-full rounded-xl border-slate-200 focus:border-cyan-600 focus:ring-cyan-600">
                        @error('amount') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>
                    <label>
                        <span class="mb-1.5 block text-sm font-bold text-slate-700">Payment method <span class="text-rose-600">*</span></span>
                        <select wire:model="manualMethod" required class="w-full rounded-xl border-slate-200 focus:border-cyan-600 focus:ring-cyan-600">
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="cash">Cash</option>
                            <option value="other">Other</option>
                        </select>
                        @error('manualMethod') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>
                    <label>
                        <span class="mb-1.5 block text-sm font-bold text-slate-700">Payer name</span>
                        <input wire:model="payerName" type="text" maxlength="160" class="w-full rounded-xl border-slate-200 focus:border-cyan-600 focus:ring-cyan-600">
                        @error('payerName') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>
                    <label>
                        <span class="mb-1.5 block text-sm font-bold text-slate-700">Received date and time <span class="text-rose-600">*</span></span>
                        <input wire:model="receivedAt" type="datetime-local" required class="w-full rounded-xl border-slate-200 focus:border-cyan-600 focus:ring-cyan-600">
                        @error('receivedAt') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="sm:col-span-2">
                        <span class="mb-1.5 block text-sm font-bold text-slate-700">Reference</span>
                        <input wire:model="reference" type="text" maxlength="120" placeholder="Bank transaction or receipt reference"
                            class="w-full rounded-xl border-slate-200 focus:border-cyan-600 focus:ring-cyan-600">
                        @error('reference') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="sm:col-span-2">
                        <span class="mb-1.5 block text-sm font-bold text-slate-700">Notes</span>
                        <textarea wire:model="notes" rows="3" maxlength="5000" class="w-full rounded-xl border-slate-200 focus:border-cyan-600 focus:ring-cyan-600"></textarea>
                        @error('notes') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>
                </div>
                <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="closeForm" class="rounded-xl border border-slate-200 px-5 py-3 font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-cyan-700 px-5 py-3 font-bold text-white hover:bg-cyan-800 disabled:opacity-60">
                        <span wire:loading.remove wire:target="store">Save income record</span>
                        <span wire:loading wire:target="store">Saving…</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
