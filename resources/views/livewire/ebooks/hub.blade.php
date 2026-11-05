<main class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
    <header class="mx-auto max-w-2xl text-center"><p class="font-bold uppercase tracking-widest text-cyan-700">MephEd Publishing</p><h1 class="mt-2 text-4xl font-black text-[#0B2A47]">eBooks</h1><p class="mt-4 text-slate-600">Practical digital books published by MephEd.</p></header>
    <section class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" aria-label="Published eBooks">
        @forelse ($ebooks as $ebook)
            <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                @if ($ebook->cover_path)<img src="{{ asset('storage/'.$ebook->cover_path) }}" alt="Cover of {{ $ebook->title }}" loading="lazy" class="aspect-[4/3] w-full object-cover">@endif
                <div class="p-5"><h2 class="text-xl font-bold text-[#0B2A47]"><a class="hover:underline" href="{{ route('ebooks.show', ['slug' => $ebook->slug]) }}">{{ $ebook->title }}</a></h2><p class="mt-2 text-sm text-slate-600">By {{ $ebook->author_name }}</p><p class="mt-3 font-bold">₦{{ number_format($ebook->price_kobo / 100, 2) }}</p><a class="mt-4 inline-block rounded-lg bg-[#0B2A47] px-4 py-2 font-semibold text-white focus:outline-none focus:ring-2 focus:ring-cyan-500" href="{{ route('ebooks.show', ['slug' => $ebook->slug]) }}">View eBook</a></div>
            </article>
        @empty
            <p class="col-span-full rounded-lg border border-slate-200 bg-white p-6 text-center text-slate-600">New eBooks will be listed here when available.</p>
        @endforelse
    </section>
</main>
