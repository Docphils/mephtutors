<main>
    <section class="relative overflow-hidden bg-slate-950 px-4 py-16 text-white sm:px-6 sm:py-20 lg:py-24">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-cyan-700/35 via-slate-950 to-slate-950"></div>
        <div class="relative mx-auto max-w-6xl">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-cyan-300">MephEd products</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">{{ $heading }}</h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-300">{{ $intro }}</p>
            <div class="mt-8 flex flex-wrap gap-3 text-sm font-semibold text-slate-300">
                <span class="rounded-full border border-white/15 bg-white/5 px-4 py-2">Education</span>
                <span class="rounded-full border border-white/15 bg-white/5 px-4 py-2">Software</span>
                <span class="rounded-full border border-white/15 bg-white/5 px-4 py-2">Digital publishing</span>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 px-4 py-14 sm:px-6 sm:py-20">
        <div class="mx-auto max-w-6xl">
            <div class="mb-9 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-cyan-700">Our portfolio</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Explore what we’re building</h2>
                </div>
                <p class="max-w-xl text-sm leading-6 text-slate-600">Each product is developed for a distinct need, with clear information about its purpose and availability.</p>
            </div>

            @if ($products->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                            <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm">{{ \App\Models\CompanyProduct::CATEGORIES[$product->category] ?? 'Product' }}</span>
                                @if ($product->is_featured)
                                    <span class="absolute right-4 top-4 rounded-full bg-cyan-700 px-3 py-1.5 text-xs font-bold text-white">Featured</span>
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col p-6 sm:p-7">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-2xl font-black text-slate-900">{{ $product->name }}</h3>
                                    @if ($product->release_status !== 'available')
                                        <span class="shrink-0 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">{{ \App\Models\CompanyProduct::RELEASE_STATUSES[$product->release_status] ?? 'Coming soon' }}</span>
                                    @endif
                                </div>
                                <p class="mt-4 text-sm leading-7 text-slate-600">{{ $product->summary }}</p>
                                @if ($product->description)
                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $product->description }}</p>
                                @endif
                                <div class="mt-auto pt-6">
                                    @if ($product->release_status === 'available' && $product->website_url)
                                        <a href="{{ $product->website_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-cyan-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                                            {{ $product->cta_label ?: 'Visit ' . $product->name }}<i class="fas fa-arrow-up-right-from-square text-xs" aria-hidden="true"></i>
                                        </a>
                                    @elseif ($product->release_status === 'available')
                                        <span class="inline-flex items-center rounded-xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-600">More information coming soon</span>
                                    @else
                                        <span class="inline-flex items-center gap-2 text-sm font-bold text-cyan-800"><i class="fas fa-hourglass-half" aria-hidden="true"></i> We’re preparing this product</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                    <h3 class="text-xl font-bold text-slate-900">New products are on the way</h3>
                    <p class="mt-2 text-slate-600">Please check back soon to see what MephEd is building.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="bg-white px-4 py-14 sm:px-6">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-5 rounded-3xl bg-cyan-50 p-6 sm:flex-row sm:items-center sm:p-9">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Have a question about a MephEd product?</h2>
                <p class="mt-2 text-slate-600">Our team can direct your enquiry to the right product team.</p>
            </div>
            <a href="{{ route('contact') }}" wire:navigate class="inline-flex min-h-11 items-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800">Contact MephEd</a>
        </div>
    </section>
</main>
