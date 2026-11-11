<section class="mx-auto max-w-7xl space-y-6">
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Editorial workspace</p>
            <h1 class="text-2xl font-black text-slate-900">Blog Studio</h1>
            <p class="mt-1 text-sm text-slate-500">Create, review, preview, and publish MephEd articles.</p>
        </div>
    </x-slot>

    <section aria-label="Blog Studio actions"
        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex sm:items-center sm:justify-between sm:gap-6 sm:p-5">
        <div class="mb-4 sm:mb-0">
            <p class="text-sm font-bold text-slate-900">Quick actions</p>
            <p class="mt-1 text-xs leading-5 text-slate-500">Approve a topic before creating a research-backed,
                unpublished draft.</p>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-end">
            @if ($generationEnabled)
                <button type="button" wire:click="generateNow" wire:loading.attr="disabled" wire:target="generateNow"
                    class="col-span-1 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-3 text-center text-sm font-bold text-cyan-900 transition hover:border-cyan-400 hover:bg-cyan-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 sm:order-2 sm:min-w-48 sm:px-4">
                    <span wire:loading.remove wire:target="generateNow"><i class="fas fa-wand-magic-sparkles mr-1"
                            aria-hidden="true"></i> Generate draft</span>
                    <span wire:loading wire:target="generateNow" class="inline-flex items-center gap-2"
                        role="status"><i class="fas fa-spinner animate-spin" aria-hidden="true"></i> Queueing…</span>
                </button>
            @else
                <span
                    class="col-span-1 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-center text-xs font-bold text-amber-900 sm:order-2 sm:min-w-48 sm:px-4">
                    <i class="fas fa-circle-pause" aria-hidden="true"></i> Generation disabled
                </span>
            @endif
            <a wire:navigate href="{{ route('admin.blog.settings') }}"
                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-center text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600 focus-visible:ring-offset-2 sm:order-1 sm:px-4">
                <i class="fas fa-sliders" aria-hidden="true"></i><span>Settings</span>
            </a>
            @if ($generationEnabled)
                <button type="button" wire:click="generateTopicSuggestions" wire:loading.attr="disabled"
                    wire:target="generateTopicSuggestions"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-3 py-3 text-center text-sm font-bold text-violet-900 transition hover:bg-violet-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-600 focus-visible:ring-offset-2 disabled:opacity-60 sm:order-2">
                    <span wire:loading.remove wire:target="generateTopicSuggestions"><i class="fas fa-lightbulb mr-1"
                            aria-hidden="true"></i>Suggest topics</span>
                    <span wire:loading wire:target="generateTopicSuggestions" role="status"><i
                            class="fas fa-spinner animate-spin" aria-hidden="true"></i> Queueing…</span>
                </button>
            @endif
            <a wire:navigate href="{{ route('admin.blog.create') }}"
                class="order-first col-span-2 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-cyan-800 px-4 py-3 text-center text-sm font-bold text-white shadow-sm transition hover:bg-cyan-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-700 focus-visible:ring-offset-2 sm:order-3 sm:px-5">
                <i class="fas fa-pen-to-square" aria-hidden="true"></i><span>Write an article</span>
            </a>
        </div>
    </section>

    @if ($generationEnabled)
        <div class="grid gap-3 sm:grid-cols-3">
            <label class="block rounded-xl border border-slate-200 bg-white p-3 text-xs font-bold text-slate-600">Target
                article length
                <div class="mt-1 flex items-center gap-2">
                    <input wire:model="wordTarget" type="number" min="300" max="2000"
                        class="w-28 rounded-lg border-slate-300 text-sm font-semibold text-slate-900">
                    <span>words for this draft</span>
                </div>
            </label>
            <div class="rounded-xl border border-slate-200 bg-white p-3 text-xs font-bold text-slate-600"><span
                    class="block">Approved topics available</span><strong
                    class="mt-1 block text-2xl text-[#0B2A47]">{{ $topicCounts['available'] }}</strong></div>
            <div class="rounded-xl border border-slate-200 bg-white p-3 text-xs font-bold text-slate-600"><span
                    class="block">Pending topic review</span><strong
                    class="mt-1 block text-2xl text-[#0B2A47]">{{ $topicCounts['pending'] }}</strong></div>
        </div>
    @endif

    @if ($statusMessage)
        <div @class([
            'flex items-start gap-3 rounded-2xl border p-4 shadow-sm',
            'border-emerald-200 bg-emerald-50 text-emerald-950' =>
                $statusType === 'success',
            'border-rose-200 bg-rose-50 text-rose-950' => $statusType === 'error',
        ]) role="{{ $statusType === 'error' ? 'alert' : 'status' }}" aria-live="polite">
            <span @class([
                'mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
                'bg-emerald-100 text-emerald-700' => $statusType === 'success',
                'bg-rose-100 text-rose-700' => $statusType === 'error',
            ]) aria-hidden="true"><i
                    class="fas {{ $statusType === 'success' ? 'fa-check' : 'fa-triangle-exclamation' }}"></i></span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold">{{ $statusHeading }}</p>
                <p class="mt-1 text-sm leading-6 opacity-90">{{ $statusMessage }}</p>
            </div>
            <button type="button" wire:click="dismissGenerationMessage"
                class="rounded-lg p-2 text-current/70 transition hover:bg-black/5 hover:text-current"
                aria-label="Dismiss notification"><i class="fas fa-xmark"></i></button>
        </div>
    @endif

    <section class="grid gap-4 sm:grid-cols-3" aria-label="Blog article totals">
        @foreach (['draft' => 'Drafts', 'published' => 'Published', 'archived' => 'Archived'] as $status => $label)
            <button type="button" wire:click="$set('statusFilter', '{{ $statusFilter === $status ? '' : $status }}')"
                class="rounded-2xl border bg-white p-5 text-left shadow-sm transition hover:border-cyan-300 {{ $statusFilter === $status ? 'border-cyan-700 ring-2 ring-cyan-100' : 'border-slate-200' }}">
                <span
                    class="block text-xs font-bold uppercase tracking-widest text-slate-500">{{ $label }}</span>
                <span class="mt-2 block text-3xl font-black text-[#0B2A47]">{{ $statusCounts[$status] ?? 0 }}</span>
            </button>
        @endforeach
    </section>

    <section aria-label="Generation workspace"
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex gap-2 border-b border-slate-200 p-3 sm:p-4" role="tablist"
            aria-label="Generation workspace tabs">
            <button type="button" role="tab"
                aria-selected="{{ $operationsTab === 'articles' ? 'true' : 'false' }}"
                wire:click="setOperationsTab('articles')" @class([
                    'rounded-xl px-4 py-2.5 text-sm font-bold transition',
                    'bg-cyan-800 text-white' => $operationsTab === 'articles',
                    'text-slate-600 hover:bg-slate-100' => $operationsTab !== 'articles',
                ])>
                <i class="fas fa-newspaper mr-1.5" aria-hidden="true"></i>Articles <span
                    class="ml-1 rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ $statusCounts->sum() }}</span>
            </button>
            <button type="button" role="tab"
                aria-selected="{{ $operationsTab === 'activity' ? 'true' : 'false' }}"
                wire:click="setOperationsTab('activity')" @class([
                    'rounded-xl px-4 py-2.5 text-sm font-bold transition',
                    'bg-cyan-800 text-white' => $operationsTab === 'activity',
                    'text-slate-600 hover:bg-slate-100' => $operationsTab !== 'activity',
                ])>
                <i class="fas fa-list-check mr-1.5" aria-hidden="true"></i>Generation activity <span
                    class="ml-1 rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ $generationRuns->total() }}</span>
            </button>
            <button type="button" role="tab"
                aria-selected="{{ $operationsTab === 'topics' ? 'true' : 'false' }}"
                wire:click="setOperationsTab('topics')" @class([
                    'rounded-xl px-4 py-2.5 text-sm font-bold transition',
                    'bg-cyan-800 text-white' => $operationsTab === 'topics',
                    'text-slate-600 hover:bg-slate-100' => $operationsTab !== 'topics',
                ])>
                <i class="fas fa-lightbulb mr-1.5" aria-hidden="true"></i>Topic bank <span
                    class="ml-1 rounded-full bg-black/5 px-2 py-0.5 text-xs">{{ $topicCounts['pending'] }}</span>
            </button>
        </div>

        @if ($operationsTab === 'activity')
            <section wire:poll.10s class="overflow-hidden">
                <div
                    class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Draft generation activity</h2>
                        <p class="mt-1 text-sm text-slate-500">Each request queues a separate draft.</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">Rows per page
                        <select wire:model.live="activityPerPage"
                            class="rounded-lg border-slate-300 py-2 text-sm text-slate-900">
                            @foreach ([5, 10, 25, 50, 100] as $pageSize)
                                <option value="{{ $pageSize }}">{{ $pageSize }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                @if ($generationRuns->isEmpty())
                    <p class="p-5 text-sm text-slate-500">No generation runs yet. Add and approve topics in the Topic
                        bank, then
                        configure search and review options in Blog Settings.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($generationRuns as $run)
                            <article wire:key="blog-run-{{ $run['id'] }}"
                                class="flex flex-col gap-3 p-5 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold capitalize text-slate-800">{{ $run['status'] }}</span>
                                        <span class="text-xs text-slate-500">{{ $run['run_key'] }}</span>
                                        @if ($run['provider'])
                                            <span class="text-xs text-slate-500">Provider:
                                                {{ $run['provider'] }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-semibold text-slate-500">Run #{{ $run['id'] }} ·
                                        {{ $run['provider'] }}{{ $run['word_target'] ? ' · ' . $run['word_target'] . ' words' : '' }}
                                    </p>
                                    @if ($run['topic_title'])
                                        <p class="mt-1 text-sm font-semibold text-slate-700">Topic:
                                            {{ $run['topic_title'] }}
                                        </p>
                                    @endif
                                    @if ($run['status_message'])
                                        <p @class([
                                            'mt-2 text-sm',
                                            'text-emerald-800' => $run['status'] === 'completed',
                                            'text-cyan-800' => $run['status'] !== 'completed',
                                        ]) role="status" aria-live="polite">
                                            @if ($run['status'] === 'running')
                                                <i class="fas fa-spinner animate-spin" aria-hidden="true"></i>
                                            @endif{{ $run['status_message'] }}
                                        </p>
                                    @endif
                                    @if ($run['post_title'])
                                        <p class="mt-2 font-semibold text-slate-900">{{ $run['post_title'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Review email:
                                            {{ $run['notification_status'] ?: 'pending' }}</p>
                                    @endif
                                    @if ($run['error_message'])
                                        <p role="alert" class="mt-2 text-sm text-rose-700">
                                            {{ $run['error_message'] }}</p>
                                    @endif
                                    @if ($run['notification_error'])
                                        <p role="alert" class="mt-1 text-sm text-amber-800">
                                            {{ $run['notification_error'] }}</p>
                                    @endif
                                </div>
                                @if ($run['preview_url'])
                                    <a wire:navigate href="{{ $run['preview_url'] }}"
                                        class="shrink-0 rounded-lg border border-cyan-200 px-3 py-2 text-sm font-bold text-cyan-900 hover:bg-cyan-50">Review
                                        draft</a>
                                @elseif ($run['can_retry'])
                                    <button type="button" wire:click="retryGeneration({{ $run['id'] }})"
                                        wire:confirm="Retry this failed generation run?" wire:loading.attr="disabled"
                                        class="shrink-0 rounded-lg border border-amber-300 px-3 py-2 text-sm font-bold text-amber-900 hover:bg-amber-50">Retry
                                        generation</button>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
                @if ($generationRuns->hasPages())
                    <div class="border-t border-slate-200 px-5 py-3">{{ $generationRuns->links() }}</div>
                @endif
            </section>
        @endif

        @if ($operationsTab === 'topics')
            <section wire:poll.20s class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Topic bank</h2>
                        <p class="mt-1 text-sm text-slate-500"> Approved
                            topics are
                            drafted once automatically; use “Draft again” to reuse a topic deliberately.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($topicSuggestionBatchStatus)
                            <span
                                class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold capitalize text-slate-700">Latest
                                suggestions: {{ $topicSuggestionBatchStatus }}</span>
                        @endif
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">Rows per page
                            <select wire:model.live="topicsPerPage"
                                class="rounded-lg border-slate-300 py-2 text-sm text-slate-900">
                                @foreach ([5, 10, 25, 50, 100] as $pageSize)
                                    <option value="{{ $pageSize }}">{{ $pageSize }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
                @if ($topics->isEmpty())
                    <div class="p-5 text-sm text-slate-500">No topics yet. Generate suggestions or add topics by
                        approving an
                        AI batch.</div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($topics as $topic)
                            <article wire:key="blog-topic-{{ $topic['id'] }}"
                                class="flex flex-col gap-3 p-5 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $topic['status'] === 'pending' ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800' }}">{{ $topic['status'] }}</span>
                                        <span
                                            class="text-xs font-bold uppercase tracking-wide text-cyan-800">{{ $topic['type'] }}</span>
                                        @if ($topic['uses'] > 0)
                                            <span class="text-xs text-slate-500">Used {{ $topic['uses'] }}
                                                {{ $topic['uses_label'] }}</span>
                                        @endif
                                    </div>
                                    <h3 class="mt-2 font-bold text-slate-900">{{ $topic['title'] }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $topic['angle'] }}</p>
                                    @if ($topic['ebook_title'])
                                        <p class="mt-2 text-xs font-semibold text-violet-800">Inspired by:
                                            {{ $topic['ebook_title'] }}</p>
                                    @endif
                                </div>
                                <div class="flex shrink-0 flex-wrap gap-2">
                                    @if ($topic['status'] === 'pending')
                                        <button type="button" wire:click="approveTopic({{ $topic['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-800 disabled:opacity-60">Approve</button>
                                        <button type="button" wire:click="rejectTopic({{ $topic['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-800 hover:bg-rose-50 disabled:opacity-60">Reject</button>
                                    @elseif ($generationEnabled)
                                        <button type="button" wire:click="generateFromTopic({{ $topic['id'] }})"
                                            wire:loading.attr="disabled" wire:target="generateFromTopic"
                                            class="rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-bold text-cyan-900 hover:bg-cyan-100 disabled:opacity-60">Draft
                                            again</button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
                @if ($topics->hasPages())
                    <div class="border-t border-slate-200 px-5 py-3">{{ $topics->links() }}</div>
                @endif
            </section>
        @endif
        @if ($operationsTab === 'articles')
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">
                            {{ $statusFilter === '' ? 'All articles' : ucfirst($statusFilter) . ' articles' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Only published articles appear in public pages and
                            feeds.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="text-sm font-semibold text-slate-700">Filter status
                            <select wire:model.live="statusFilter"
                                class="ml-2 rounded-lg border-slate-300 py-2 text-sm">
                                <option value="">All statuses</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </label>
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">Rows per page
                            <select wire:model.live="articlesPerPage"
                                class="rounded-lg border-slate-300 py-2 text-sm text-slate-900">
                                @foreach ([5, 10, 25, 50, 100] as $pageSize)
                                    <option value="{{ $pageSize }}">{{ $pageSize }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($posts as $post)
                        <article wire:key="studio-post-{{ $post['id'] }}"
                            class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $post['status_class'] }}">{{ $post['status'] }}</span>
                                    <span class="text-xs text-slate-500">{{ $post['category_name'] }}</span>
                                    <span class="text-xs text-slate-400">Updated
                                        {{ $post['updated_at_label'] }}</span>
                                </div>
                                <h3 class="mt-2 truncate text-lg font-bold text-[#0B2A47]">{{ $post['title'] }}</h3>
                                <p class="mt-1 truncate text-sm text-slate-500">/blog/{{ $post['slug'] }}</p>
                                @if ($post['is_published'])
                                    <p class="mt-2 text-xs text-slate-600">Facebook: <span
                                            class="font-semibold capitalize">{{ $post['facebook_status'] }}</span>
                                        @if ($post['facebook_error'])
                                            <span class="ml-1 text-amber-800">{{ $post['facebook_error'] }}</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                <a wire:navigate href="{{ $post['edit_url'] }}"
                                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Edit</a>
                                <a wire:navigate href="{{ $post['preview_url'] }}"
                                    class="rounded-lg border border-cyan-200 px-3 py-2 text-sm font-bold text-cyan-900 hover:bg-cyan-50">Preview</a>
                                @if ($post['is_published'])
                                    <a target="_blank" rel="noopener noreferrer"
                                        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post['manual_share_url']) }}"
                                        class="rounded-lg border border-blue-200 px-3 py-2 text-sm font-bold text-blue-900 hover:bg-blue-50">Manual
                                        Facebook share</a>
                                    @if ($post['can_retry_facebook'])
                                        <button type="button" wire:click="retryFacebook({{ $post['id'] }})"
                                            wire:confirm="Retry automatic Facebook publishing? This article is already public."
                                            wire:loading.attr="disabled"
                                            class="rounded-lg border border-blue-300 px-3 py-2 text-sm font-bold text-blue-900 hover:bg-blue-50">Retry
                                            Facebook API</button>
                                    @endif
                                    <button type="button" wire:click="unpublish({{ $post['id'] }})"
                                        wire:confirm="Return this article to drafts? It will disappear from public pages and feeds."
                                        wire:loading.attr="disabled"
                                        class="rounded-lg border border-amber-200 px-3 py-2 text-sm font-bold text-amber-900 hover:bg-amber-50">Unpublish</button>
                                @else
                                    <button type="button" wire:click="publish({{ $post['id'] }})"
                                        wire:confirm="Publish this article now?" wire:loading.attr="disabled"
                                        class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-800">Publish</button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="px-6 py-14 text-center">
                            <span
                                class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-cyan-50 text-cyan-800"
                                aria-hidden="true"><i class="fas fa-file-pen"></i></span>
                            <h3 class="mt-4 text-lg font-bold text-slate-900">No {{ $statusFilter ?: '' }} articles
                                yet</h3>
                            <p class="mt-2 text-sm text-slate-600">Create a draft to start the editorial workflow.</p>
                            <a wire:navigate href="{{ route('admin.blog.create') }}"
                                class="mt-4 inline-flex rounded-lg bg-cyan-800 px-4 py-2 font-bold text-white hover:bg-cyan-900">Create
                                a draft</a>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-slate-200 p-4">{{ $posts->links() }}</div>
            </section>
        @endif
    </section>
</section>
