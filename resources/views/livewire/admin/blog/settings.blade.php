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
                <p class="text-xs font-extrabold uppercase tracking-widest text-cyan-800">Editorial defaults</p>
                <h2 class="mt-2 text-xl font-black text-[#0B2A47]">Topics, content & author</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">AI suggestions enter the topic bank as pending and require approval. Draft generation uses an approved topic; the same approved topic can be selected again to regenerate a fresh draft.</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-bold text-slate-700">Default article length (words)
                    <input wire:model="contentWordTarget" type="number" min="300" max="2000" required class="mt-1.5 block w-full rounded-xl border-slate-300">
                    <span class="mt-1 block text-xs font-normal text-slate-500">Target for scheduled drafts and AI topic-batch drafts. You can override it for each manual generation.</span>
                    @error('contentWordTarget')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-bold text-slate-700">Default author for scheduled drafts
                    <select wire:model="defaultAuthorId" required class="mt-1.5 block w-full rounded-xl border-slate-300">
                        @foreach ($authors as $author)
                            <option value="{{ $author['id'] }}">{{ $author['label'] }}</option>
                        @endforeach
                    </select>
                    <span class="mt-1 block text-xs font-normal text-slate-500">Manual drafts use the admin who requested them. Scheduled drafts use this verified admin account.</span>
                    @error('defaultAuthorId')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-bold text-slate-700">AI topic suggestions per batch
                    <input wire:model="topicBatchSize" type="number" min="3" max="20" required class="mt-1.5 block w-full rounded-xl border-slate-300">
                    <span class="mt-1 block text-xs font-normal text-slate-500">Each suggestion is saved pending review; this does not create or publish an article.</span>
                    @error('topicBatchSize')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-bold text-slate-700">Suggest more when approved topics reach
                    <input wire:model="topicLowWatermark" type="number" min="0" max="10" required class="mt-1.5 block w-full rounded-xl border-slate-300">
                    <span class="mt-1 block text-xs font-normal text-slate-500">A pending batch is queued automatically at this level. Set to 0 to disable the low-watermark trigger.</span>
                    @error('topicLowWatermark')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
                </label>
            </div>
            <div>
                <p class="text-xs font-extrabold uppercase tracking-widest text-cyan-800">Flexible web research</p>
                <h2 class="mt-2 text-xl font-black text-[#0B2A47]">Tavily Search (RSS not used)</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Current-affairs topics use time-filtered news search. Other topics use general search for supporting evidence and ideas, including eBook-related context. Preferred domains guide ranking but never restrict results. The app uses search snippets/citations only and does not fetch arbitrary result pages.</p>
                <p class="mt-2 text-sm text-slate-700">API key: <strong class="font-bold {{ config('services.tavily.api_key') ? 'text-emerald-700' : 'text-amber-700' }}">{{ config('services.tavily.api_key') ? 'configured' : 'not configured' }}</strong>. Monthly credits reserved: <strong>{{ number_format($tavilyCreditsUsed) }}</strong>.</p>
            </div>
            <label class="block text-sm font-bold text-slate-700">Preferred source domains <span class="font-normal text-slate-500">(optional, not restrictive)</span>
                <textarea wire:model="preferredResearchDomains" rows="4" maxlength="5000" class="mt-1.5 w-full rounded-xl border-slate-300 font-mono text-sm" placeholder="education.gov.ng&#10;unesco.org&#10;unicef.org&#10;waecnigeria.org"></textarea>
                <span class="mt-1 block text-xs font-normal text-slate-500">Up to 30 domain names, one per line. Search can and should include other credible publishers when useful.</span>
                @error('preferredResearchDomains')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
            </label>
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-bold text-slate-700">Monthly Tavily credit cap
                    <input wire:model="tavilyMonthlyCreditLimit" type="number" min="1" max="1500" required class="mt-1.5 block w-full rounded-xl border-slate-300">
                    <span class="mt-1 block text-xs font-normal text-slate-500">Basic search costs 1 credit; advanced search costs 2. A search is counted before sending to avoid exceeding your quota on concurrent retries.</span>
                    @error('tavilyMonthlyCreditLimit')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-bold text-slate-700">Search depth
                    <select wire:model="tavilySearchDepth" class="mt-1.5 block w-full rounded-xl border-slate-300">
                        <option value="basic">Basic — 1 credit per search</option>
                        <option value="advanced">Advanced — 2 credits per search</option>
                    </select>
                    @error('tavilySearchDepth')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-bold text-slate-700">Maximum results per search
                    <input wire:model="tavilyMaxResults" type="number" min="1" max="20" required class="mt-1.5 block w-full rounded-xl border-slate-300">
                    <span class="mt-1 block text-xs font-normal text-slate-500">More results add breadth but can increase the context sent to the writing model.</span>
                    @error('tavilyMaxResults')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-bold text-slate-700">Current-affairs recency
                    <select wire:model="tavilyCurrentAffairsRange" class="mt-1.5 block w-full rounded-xl border-slate-300">
                        <option value="day">Past day</option>
                        <option value="week">Past week</option>
                        <option value="month">Past month</option>
                        <option value="year">Past year</option>
                    </select>
                    @error('tavilyCurrentAffairsRange')<span class="mt-1 block text-xs font-semibold text-rose-700">{{ $message }}</span>@enderror
                </label>
            </div>
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
