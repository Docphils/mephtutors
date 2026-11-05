<div class="min-h-screen bg-slate-100 p-4 sm:p-6 lg:p-8">
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Digital publishing</p>
            <h1 class="text-2xl font-black text-slate-900">eBook legal pages</h1>
            <p class="mt-1 text-sm text-slate-500">Edit the live policy pages and control whether they satisfy the eBook publishing gate.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="rounded-xl border {{ $publishingReady ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-amber-200 bg-amber-50 text-amber-950' }} p-4 text-sm">
            @if ($publishingReady)
                <p class="font-bold">eBook publishing is enabled.</p>
                <p class="mt-1">All three policy pages are public. Checkout remains controlled separately by the eBook sales setting.</p>
            @else
                <p class="font-bold">eBook publishing is paused.</p>
                <p class="mt-1">Publish the refund, terms, and privacy pages to enable eBook publishing. Sales remain controlled separately.</p>
            @endif
        </div>

        <form wire:submit="savePolicies" class="space-y-6">
            @foreach (['refund' => 'Refund policy', 'terms' => 'Terms of sale', 'privacy' => 'Privacy notice'] as $slug => $label)
                <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">{{ $label }}</h2>
                            @if ($policies[$slug]['is_published'])
                                <a href="{{ route('ebooks.policy', ['policy' => $slug]) }}" target="_blank" rel="noopener" class="mt-1 inline-block text-sm font-semibold text-cyan-800 underline">View published page</a>
                            @else
                                <p class="mt-1 text-sm text-slate-500">Draft page is hidden from visitors.</p>
                            @endif
                        </div>
                        <label class="text-sm font-semibold text-slate-700">
                            Publication status
                            <select wire:model="policies.{{ $slug }}.is_published" class="mt-1 block rounded-lg border-slate-300">
                                <option value="1">Published</option>
                                <option value="0">Draft (hidden)</option>
                            </select>
                        </label>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700">
                        Page title
                        <input wire:model="policies.{{ $slug }}.title" maxlength="180" required class="mt-1 w-full rounded-xl border-slate-300">
                        @error("policies.$slug.title")<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Page content <span class="font-normal text-slate-500">(Markdown supported; raw HTML is removed)</span>
                        <textarea wire:model="policies.{{ $slug }}.content" rows="16" maxlength="30000" required class="mt-1 w-full rounded-xl border-slate-300 font-normal"></textarea>
                        @error("policies.$slug.content")<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>
                </section>
            @endforeach

            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <input type="checkbox" wire:model="confirmPublication" class="mt-1 rounded border-slate-300 text-cyan-700">
                <span>I have reviewed and approve any draft policy pages I am publishing. I understand this enables eBook publishing, not payment checkout.</span>
            </label>
            @error('confirmPublication')<p role="alert" class="text-sm text-rose-700">{{ $message }}</p>@enderror

            <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-cyan-700 px-5 py-3 text-sm font-bold text-white disabled:opacity-60">Save policy pages</button>
        </form>

        <p class="text-xs leading-5 text-slate-500">These live policy pages contain draft wording prepared for MephEd Educational Services Ltd, using the refund approach supplied by the owner. Obtain qualified Nigerian legal review; update the public text here as needed.</p>
    </div>
</div>
