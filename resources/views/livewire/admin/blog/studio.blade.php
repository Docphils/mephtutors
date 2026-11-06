<section class="mx-auto max-w-7xl space-y-6">
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Editorial workspace</p>
            <h1 class="text-2xl font-black text-slate-900">Blog Studio</h1>
            <p class="mt-1 text-sm text-slate-500">Create, review, preview, and publish MephEd articles.</p>
        </div>
    </x-slot>

    <section aria-label="Blog Studio actions" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex sm:items-center sm:justify-between sm:gap-6 sm:p-5">
        <div class="mb-4 sm:mb-0">
            <p class="text-sm font-bold text-slate-900">Quick actions</p>
            <p class="mt-1 text-xs leading-5 text-slate-500">Create an article or start an unpublished AI draft.</p>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-end">
            @if ($generationEnabled)
                <button type="button" wire:click="generateNow" wire:loading.attr="disabled" wire:target="generateNow" class="col-span-1 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-3 text-center text-sm font-bold text-cyan-900 transition hover:border-cyan-400 hover:bg-cyan-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 sm:order-2 sm:min-w-48 sm:px-4">
                    <span wire:loading.remove wire:target="generateNow"><i class="fas fa-wand-magic-sparkles mr-1" aria-hidden="true"></i> Generate draft</span>
                    <span wire:loading wire:target="generateNow" class="inline-flex items-center gap-2" role="status"><i class="fas fa-spinner animate-spin" aria-hidden="true"></i> Queueing…</span>
                </button>
            @else
                <span class="col-span-1 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-center text-xs font-bold text-amber-900 sm:order-2 sm:min-w-48 sm:px-4">
                    <i class="fas fa-circle-pause" aria-hidden="true"></i> Generation disabled
                </span>
            @endif
            <a wire:navigate href="{{ route('admin.blog.settings') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-center text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600 focus-visible:ring-offset-2 sm:order-1 sm:px-4">
                <i class="fas fa-sliders" aria-hidden="true"></i><span>Settings</span>
            </a>
            <a wire:navigate href="{{ route('admin.blog.create') }}" class="order-first col-span-2 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-cyan-800 px-4 py-3 text-center text-sm font-bold text-white shadow-sm transition hover:bg-cyan-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-700 focus-visible:ring-offset-2 sm:order-3 sm:px-5">
                <i class="fas fa-pen-to-square" aria-hidden="true"></i><span>Write an article</span>
            </a>
        </div>
    </section>

    @if ($statusMessage)
        <div @class(['flex items-start gap-3 rounded-2xl border p-4 shadow-sm', 'border-emerald-200 bg-emerald-50 text-emerald-950' => $statusType === 'success', 'border-rose-200 bg-rose-50 text-rose-950' => $statusType === 'error']) role="{{ $statusType === 'error' ? 'alert' : 'status' }}" aria-live="polite">
            <span @class(['mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full', 'bg-emerald-100 text-emerald-700' => $statusType === 'success', 'bg-rose-100 text-rose-700' => $statusType === 'error']) aria-hidden="true"><i class="fas {{ $statusType === 'success' ? 'fa-check' : 'fa-triangle-exclamation' }}"></i></span>
            <div class="min-w-0 flex-1"><p class="text-sm font-bold">{{ $statusHeading }}</p><p class="mt-1 text-sm leading-6 opacity-90">{{ $statusMessage }}</p></div>
            <button type="button" wire:click="dismissGenerationMessage" class="rounded-lg p-2 text-current/70 transition hover:bg-black/5 hover:text-current" aria-label="Dismiss notification"><i class="fas fa-xmark"></i></button>
        </div>
    @endif

    <section class="grid gap-4 sm:grid-cols-3" aria-label="Blog article totals">
        @foreach (['draft' => 'Drafts', 'published' => 'Published', 'archived' => 'Archived'] as $status => $label)
            <button type="button" wire:click="$set('statusFilter', '{{ $statusFilter === $status ? '' : $status }}')" class="rounded-2xl border bg-white p-5 text-left shadow-sm transition hover:border-cyan-300 {{ $statusFilter === $status ? 'border-cyan-700 ring-2 ring-cyan-100' : 'border-slate-200' }}">
                <span class="block text-xs font-bold uppercase tracking-widest text-slate-500">{{ $label }}</span>
                <span class="mt-2 block text-3xl font-black text-[#0B2A47]">{{ $statusCounts[$status] ?? 0 }}</span>
            </button>
        @endforeach
    </section>

    <section wire:poll.10s class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
            <h2 class="text-lg font-black text-slate-900">Draft generation activity</h2>
            <p class="mt-1 text-sm text-slate-500">Each request queues a separate draft.</p>
        </div>
        @if ($generationRuns->isEmpty())
            <p class="p-5 text-sm text-slate-500">No generation runs yet. Configure approved feeds and recipients in Blog Settings.</p>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($generationRuns as $run)
                    <article wire:key="blog-run-{{ $run['id'] }}" class="flex flex-col gap-3 p-5 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold capitalize text-slate-800">{{ $run['status'] }}</span>
                                <span class="text-xs text-slate-500">{{ $run['run_key'] }}</span>
                                @if ($run['provider'])<span class="text-xs text-slate-500">Provider: {{ $run['provider'] }}</span>@endif
                            </div>
                            @if ($run['status_message'])
                                <p @class(['mt-2 text-sm', 'text-emerald-800' => $run['status'] === 'completed', 'text-cyan-800' => $run['status'] !== 'completed']) role="status" aria-live="polite">
                                    @if ($run['status'] === 'running')<i class="fas fa-spinner animate-spin" aria-hidden="true"></i> @endif{{ $run['status_message'] }}
                                </p>
                            @endif
                            @if ($run['post_title'])
                                <p class="mt-2 font-semibold text-slate-900">{{ $run['post_title'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">Review email: {{ $run['notification_status'] ?: 'pending' }}</p>
                            @endif
                            @if ($run['error_message'])
                                <p role="alert" class="mt-2 text-sm text-rose-700">{{ $run['error_message'] }}</p>
                            @endif
                            @if ($run['notification_error'])
                                <p role="alert" class="mt-1 text-sm text-amber-800">{{ $run['notification_error'] }}</p>
                            @endif
                        </div>
                        @if ($run['preview_url'])
                            <a wire:navigate href="{{ $run['preview_url'] }}" class="shrink-0 rounded-lg border border-cyan-200 px-3 py-2 text-sm font-bold text-cyan-900 hover:bg-cyan-50">Review draft</a>
                        @elseif ($run['can_retry'])
                            <button type="button" wire:click="retryGeneration({{ $run['id'] }})" wire:confirm="Retry this failed generation run?" wire:loading.attr="disabled" class="shrink-0 rounded-lg border border-amber-300 px-3 py-2 text-sm font-bold text-amber-900 hover:bg-amber-50">Retry generation</button>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="text-lg font-black text-slate-900">{{ $statusFilter === '' ? 'All articles' : ucfirst($statusFilter).' articles' }}</h2><p class="mt-1 text-sm text-slate-500">Only published articles appear in public pages and feeds.</p></div>
            <label class="text-sm font-semibold text-slate-700">Filter status
                <select wire:model.live="statusFilter" class="ml-2 rounded-lg border-slate-300 py-2 text-sm">
                    <option value="">All statuses</option><option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option>
                </select>
            </label>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($posts as $post)
                <article wire:key="studio-post-{{ $post['id'] }}" class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $post['status_class'] }}">{{ $post['status'] }}</span>
                            <span class="text-xs text-slate-500">{{ $post['category_name'] }}</span>
                            <span class="text-xs text-slate-400">Updated {{ $post['updated_at_label'] }}</span>
                        </div>
                        <h3 class="mt-2 truncate text-lg font-bold text-[#0B2A47]">{{ $post['title'] }}</h3>
                        <p class="mt-1 truncate text-sm text-slate-500">/blog/{{ $post['slug'] }}</p>
                        @if ($post['is_published'])
                            <p class="mt-2 text-xs text-slate-600">Facebook: <span class="font-semibold capitalize">{{ $post['facebook_status'] }}</span>
                                @if ($post['facebook_error'])
                                    <span class="ml-1 text-amber-800">{{ $post['facebook_error'] }}</span>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a wire:navigate href="{{ $post['edit_url'] }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Edit</a>
                        <a wire:navigate href="{{ $post['preview_url'] }}" class="rounded-lg border border-cyan-200 px-3 py-2 text-sm font-bold text-cyan-900 hover:bg-cyan-50">Preview</a>
                        @if ($post['is_published'])
                            <a target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post['manual_share_url']) }}" class="rounded-lg border border-blue-200 px-3 py-2 text-sm font-bold text-blue-900 hover:bg-blue-50">Manual Facebook share</a>
                            @if ($post['can_retry_facebook'])
                                <button type="button" wire:click="retryFacebook({{ $post['id'] }})" wire:confirm="Retry automatic Facebook publishing? This article is already public." wire:loading.attr="disabled" class="rounded-lg border border-blue-300 px-3 py-2 text-sm font-bold text-blue-900 hover:bg-blue-50">Retry Facebook API</button>
                            @endif
                            <button type="button" wire:click="unpublish({{ $post['id'] }})" wire:confirm="Return this article to drafts? It will disappear from public pages and feeds." wire:loading.attr="disabled" class="rounded-lg border border-amber-200 px-3 py-2 text-sm font-bold text-amber-900 hover:bg-amber-50">Unpublish</button>
                        @else
                            <button type="button" wire:click="publish({{ $post['id'] }})" wire:confirm="Publish this article now?" wire:loading.attr="disabled" class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-800">Publish</button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-cyan-50 text-cyan-800" aria-hidden="true"><i class="fas fa-file-pen"></i></span>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">No {{ $statusFilter ?: '' }} articles yet</h3>
                    <p class="mt-2 text-sm text-slate-600">Create a draft to start the editorial workflow.</p>
                    <a wire:navigate href="{{ route('admin.blog.create') }}" class="mt-4 inline-flex rounded-lg bg-cyan-800 px-4 py-2 font-bold text-white hover:bg-cyan-900">Create a draft</a>
                </div>
            @endforelse
        </div>
        <div class="border-t border-slate-200 p-4">{{ $posts->links() }}</div>
    </section>
</section>
