<section class="mx-auto max-w-4xl space-y-6">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Editorial workspace</p><h1 class="text-2xl font-black text-slate-900">Blog Settings</h1></div>
            <a wire:navigate href="{{ route('admin.blog.index') }}" class="text-sm font-bold text-cyan-800 underline">Back to Blog Studio</a>
        </div>
    </x-slot>

    @if (session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-900">
            <p class="font-bold">Please review the settings below.</p>
            <ul class="mt-2 list-inside list-disc text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form wire:submit="save" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-6 sm:p-8">
            <p class="text-xs font-extrabold uppercase tracking-widest text-cyan-800">Draft review workflow</p>
            <h2 class="mt-2 text-xl font-black text-[#0B2A47]">Schedule & notifications</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">These preferences will be used by the daily draft workflow. All times use Africa/Lagos. Generation remains disabled until its server-side feature flag is enabled.</p>
        </div>
        <div class="space-y-6 p-6 sm:p-8">
            <label class="block max-w-sm text-sm font-bold text-slate-700">Preferred daily draft time
                <input type="time" wire:model="dailyTime" required class="mt-1.5 block w-full rounded-xl border-slate-300 text-base">
                <span class="mt-1 block text-xs font-normal text-slate-500">Africa/Lagos time. The cPanel scheduler checks each minute and queues at or after this time.</span>
                @error('dailyTime')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-bold text-slate-700">Draft review email recipients
                <textarea wire:model="notificationEmails" rows="3" maxlength="2000" class="mt-1.5 w-full rounded-xl border-slate-300" placeholder="editor@example.com, reviewer@example.com"></textarea>
                <span class="mt-1 block text-xs font-normal text-slate-500">Add up to 10 addresses separated by commas, semicolons, or new lines. Review notifications require a configured mail transport.</span>
                @error('notificationEmails')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
            </label>
        </div>
        <div class="space-y-6 border-t border-slate-200 p-6 sm:p-8">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-widest text-cyan-800">Research sources</p>
                <h2 class="mt-2 text-xl font-black text-[#0B2A47]">Approved domains & feeds</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">The approved-domain list is a security allowlist: it permits feed fetches only from these exact hosts; it does not discover feeds automatically. Two verified feeds are included by default (Federal Ministry of Education and National Universities Commission). Add a feed URL below for any other approved source; requests reject redirects and private-network IPs.</p>
            </div>
            <label class="block text-sm font-bold text-slate-700">Approved source domains
                <textarea wire:model="approvedSourceDomains" rows="8" maxlength="5000" class="mt-1.5 w-full rounded-xl border-slate-300 font-mono text-sm" placeholder="education.gov.ng&#10;waecnigeria.org&#10;unesco.org"></textarea>
                <span class="mt-1 block text-xs font-normal text-slate-500">One exact hostname per line; add www or a feed subdomain separately. No scheme, path, wildcard, or IP address. State ministries and other sources can be added here.</span>
                @error('approvedSourceDomains')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-bold text-slate-700">RSS / Atom feed URLs
                <textarea wire:model="sourceFeedUrls" rows="6" maxlength="10000" class="mt-1.5 w-full rounded-xl border-slate-300 font-mono text-sm" placeholder="https://education.gov.ng/feed/"></textarea>
                <span class="mt-1 block text-xs font-normal text-slate-500">Add up to 15 HTTPS URLs, one per line. Use feeds from approved institutions; entries from other hosts are ignored. X is not scraped; it requires a separately configured official API integration.</span>
                @error('sourceFeedUrls')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
            </label>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:px-8">
            <p class="text-xs text-slate-500"><span wire:loading.remove wire:target="save">Settings are saved securely in the blog configuration store.</span><span wire:loading wire:target="save">Saving settings…</span></p>
            <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-cyan-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-cyan-900 disabled:opacity-60">Save settings</button>
        </div>
    </form>

    <aside class="flex flex-col gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-widest text-blue-800">Facebook destination</p>
            <p class="mt-1 font-bold text-slate-900">{{ config('blog.facebook_page_name') }}</p>
            <p class="mt-1 text-sm text-slate-600">Automatic API publishing is {{ config('blog.facebook_enabled') ? 'enabled' : 'disabled' }}. </p>
        </div>
        <a href="{{ config('blog.facebook_page_url') }}" target="_blank" rel="noopener noreferrer" class="shrink-0 rounded-lg border border-blue-300 bg-white px-4 py-2 text-sm font-bold text-blue-900 hover:bg-blue-100">Open Facebook Page</a>
    </aside>
</section>
