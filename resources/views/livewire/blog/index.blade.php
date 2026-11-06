<main class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:py-16">
    <header class="mx-auto max-w-3xl text-center">
        <p class="font-bold uppercase tracking-[0.18em] text-cyan-700">MephEd Insights</p>
        <h1 class="mt-3 text-4xl font-black tracking-tight text-[#0B2A47] sm:text-5xl">{{ $heading }}</h1>
        <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-slate-600">Practical ideas and insights for learners, parents, educators, and school leaders.</p>
    </header>

    <div class="mt-9 flex flex-col gap-5 border-y border-slate-200 py-5 lg:flex-row lg:items-center lg:justify-between">
        <nav class="flex flex-wrap gap-2" aria-label="Blog categories">
            <a href="{{ route('blog.index') }}" @class(['rounded-full px-4 py-2 text-sm font-bold transition', 'bg-[#0B2A47] text-white' => request()->routeIs('blog.index'), 'border border-slate-300 text-slate-700 hover:border-cyan-700 hover:text-cyan-900' => !request()->routeIs('blog.index')])>All articles</a>
            @foreach ($categories as $category)
                <a href="{{ route('blog.category', $category->slug) }}" @class(['rounded-full px-4 py-2 text-sm font-bold transition', 'bg-[#0B2A47] text-white' => request()->route('slug') === $category->slug && request()->routeIs('blog.category'), 'border border-slate-300 text-slate-700 hover:border-cyan-700 hover:text-cyan-900' => !(request()->route('slug') === $category->slug && request()->routeIs('blog.category'))])>{{ $category->name }}</a>
            @endforeach
        </nav>
        <div class="flex items-center justify-between gap-5">
            @if (request()->routeIs('blog.index'))
                <form action="{{ route('blog.index') }}" method="GET" role="search" class="flex min-w-0 flex-1 gap-2 lg:flex-initial">
                    <label for="blog-search" class="sr-only">Search articles</label>
                    <input id="blog-search" wire:model.live.debounce.300ms="search" maxlength="100" class="min-w-0 w-full rounded-xl border-slate-300 lg:w-64" placeholder="Search articles">
                    <button type="submit" class="rounded-xl bg-cyan-800 px-4 py-2 font-bold text-white hover:bg-cyan-900">Search</button>
                </form>
            @endif
            <a href="{{ route('blog.feed') }}" class="shrink-0 text-sm font-bold text-cyan-800 underline underline-offset-4">RSS feed</a>
        </div>
    </div>

    <section class="mt-9" aria-label="Blog articles">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <article wire:key="blog-post-{{ $post->id }}" class="group flex min-h-64 flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-lg">
                    @if ($post->hero_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->hero_image_path) }}" alt="Illustration for {{ $post->title }}" width="1200" height="630" loading="lazy" class="mb-5 aspect-[16/9] w-full rounded-xl object-cover">
                    @endif
                    <div class="flex items-center justify-between gap-3">
                        @if ($post->category)
                            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-xs font-extrabold uppercase tracking-wider text-cyan-800">{{ $post->category->name }}</a>
                        @else
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">MephEd Insights</span>
                        @endif
                        <time class="shrink-0 text-xs text-slate-500" datetime="{{ $post->published_at->toAtomString() }}">{{ $post->published_at->format('M j, Y') }}</time>
                    </div>
                    <h2 class="mt-4 line-clamp-2 text-xl font-black leading-snug text-[#0B2A47] group-hover:text-cyan-900"><a href="{{ route('blog.show', $post->slug) }}" class="focus:outline-none focus:ring-2 focus:ring-cyan-600">{{ $post->title }}</a></h2>
                    <p class="mt-3 line-clamp-3 flex-1 text-sm leading-6 text-slate-600">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 180) }}</p>
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                        <span class="text-xs text-slate-500">{{ max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 200)) }} min read</span>
                        <a class="inline-flex items-center gap-2 text-sm font-bold text-cyan-800 group-hover:gap-3" href="{{ route('blog.show', $post->slug) }}">Read article <span aria-hidden="true">→</span></a>
                    </div>
                    @if ($post->tags->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1">
                            @foreach ($post->tags as $tag)
                                <a class="text-xs text-slate-500 underline decoration-slate-300 underline-offset-2 hover:text-cyan-800" href="{{ route('blog.tag', $tag->slug) }}">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-cyan-50 text-cyan-800" aria-hidden="true"><i class="fas fa-book-open"></i></span>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $search !== '' ? 'No matching articles' : 'Articles are coming soon' }}</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">{{ $search !== '' ? 'Try another phrase or browse all articles.' : 'We’re preparing useful education insights. Please check back soon.' }}</p>
                    @if ($search !== '')
                        <button type="button" wire:click="$set('search', '')" class="mt-4 font-bold text-cyan-800 underline">Clear search</button>
                    @endif
                </div>
            @endforelse
        </div>
    </section>

    <div class="mt-8">{{ $posts->links() }}</div>
</main>
