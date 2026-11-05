<article class="mx-auto max-w-3xl px-4 py-10 text-slate-800 sm:px-6">
    <a href="{{ route('ebooks.show', ['slug' => $ebook->slug]) }}" class="text-sm font-bold text-cyan-800 hover:underline">← About {{ $ebook->title }}</a>
    <header class="mt-8 border-b border-slate-200 pb-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-700">Free chapter</p>
        <h1 class="mt-2 text-3xl font-black text-[#0B2A47]">{{ $ebook->title }}</h1>
        <p class="mt-2 text-sm text-slate-600">By {{ $ebook->author_name }}</p>
    </header>
    @if($ebook->free_chapter_path)
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('ebooks.free-chapter.pdf', ['slug' => $ebook->slug], false) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center rounded-lg bg-cyan-800 px-4 py-2 font-semibold text-white hover:bg-cyan-900">Open PDF in a new tab</a>
            <a href="{{ route('ebooks.free-chapter.pdf', ['slug' => $ebook->slug, 'download' => 1], false) }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-800 hover:bg-slate-50">Download PDF</a>
        </div>
        <p class="mt-3 text-sm text-slate-600">If the PDF preview below appears blank, open it in a new tab or download it to read in your PDF app.</p>
        <iframe title="Free chapter PDF: {{ $ebook->title }}" src="{{ route('ebooks.free-chapter.pdf', ['slug' => $ebook->slug], false) }}" loading="lazy" class="mt-4 h-[80vh] min-h-[600px] w-full rounded-xl border border-slate-300 bg-white"></iframe>
        @if($ebook->free_chapter_content)
            <section class="prose prose-slate mt-8 max-w-none whitespace-pre-line rounded-xl border border-slate-200 bg-white p-6" aria-label="Free chapter text">
                <h2>Read the chapter text</h2>
                {{ $ebook->free_chapter_content }}
            </section>
        @endif
    @else
        <div class="prose prose-slate mt-8 max-w-none whitespace-pre-line">{{ $ebook->free_chapter_content }}</div>
    @endif
</article>
