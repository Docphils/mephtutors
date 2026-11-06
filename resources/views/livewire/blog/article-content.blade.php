<main class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:py-14">
    <div class="mx-auto max-w-3xl">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-600">
            <a href="{{ route('blog.index') }}" class="underline underline-offset-4">Blog</a>
            @if ($post->category)
                <span class="px-2 text-slate-400" aria-hidden="true">/</span>
                <a href="{{ route('blog.category', $post->category->slug) }}" class="underline underline-offset-4">{{ $post->category->name }}</a>
            @endif
        </nav>

        <article class="mt-7">
            @if ($post->hero_image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->hero_image_path) }}" alt="Illustration for {{ $post->title }}" width="1200" height="630" class="mb-7 aspect-[1200/630] w-full rounded-2xl object-cover shadow-sm">
            @endif
            <header class="border-b border-slate-200 pb-7">
                @if ($post->category)
                    <p class="font-extrabold uppercase tracking-[0.16em] text-cyan-800">{{ $post->category->name }}</p>
                @endif
                <h1 class="mt-3 text-3xl font-black leading-tight tracking-tight text-[#0B2A47] sm:text-5xl">{{ $post->title }}</h1>
                @if ($post->excerpt)
                    <p class="mt-5 text-lg leading-8 text-slate-600">{{ $post->excerpt }}</p>
                @endif
                <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-slate-500">
                    <span class="font-semibold text-slate-700">{{ config('blog.author_byline') }}</span>
                    @if ($post->published_at)
                        <span aria-hidden="true">·</span><time datetime="{{ $post->published_at->toAtomString() }}">{{ $post->published_at->format('F j, Y') }}</time>
                    @endif
                    <span aria-hidden="true">·</span>
                    <span>{{ max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 200)) }} min read</span>
                </div>
                @if (request()->routeIs('blog.show'))
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:border-cyan-600 hover:text-cyan-900" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->canonical_url ?: route('blog.show', $post->slug)) }}">Share on Facebook</a>
                        <a class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 hover:border-cyan-600 hover:text-cyan-900" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text={{ urlencode($post->title.' '.($post->canonical_url ?: route('blog.show', $post->slug))) }}">Share on WhatsApp</a>
                    </div>
                @endif
            </header>

            <div class="blog-article-content mt-8 text-base leading-8 text-slate-800 sm:text-lg">
                {!! $post->body !!}
            </div>

            @if ($post->tags->isNotEmpty())
                <footer class="mt-10 border-t border-slate-200 pt-5">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Topics</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($post->tags as $tag)
                            <a class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-cyan-50 hover:text-cyan-900" href="{{ route('blog.tag', $tag->slug) }}">#{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </footer>
            @endif
        </article>
    </div>

    @if (isset($relatedPosts) && $relatedPosts->isNotEmpty())
        <aside class="mx-auto mt-14 max-w-5xl border-t border-slate-200 pt-8" aria-labelledby="related-articles-heading">
            <h2 id="related-articles-heading" class="text-2xl font-black text-[#0B2A47]">Continue reading</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relatedPosts as $relatedPost)
                    <a href="{{ route('blog.show', $relatedPost->slug) }}" class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-cyan-300 hover:shadow-md">
                        <span class="text-xs font-bold uppercase tracking-wide text-cyan-800">{{ $relatedPost->category?->name ?: 'MephEd Insights' }}</span>
                        <span class="mt-2 block font-bold leading-snug text-[#0B2A47]">{{ $relatedPost->title }}</span>
                        <time class="mt-3 block text-xs text-slate-500" datetime="{{ $relatedPost->published_at->toAtomString() }}">{{ $relatedPost->published_at->format('M j, Y') }}</time>
                    </a>
                @endforeach
            </div>
        </aside>
    @endif
</main>
