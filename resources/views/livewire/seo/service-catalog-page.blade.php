<div class="max-w-6xl mx-auto px-4 py-10 space-y-10 text-slate-800">
    <section class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
        <p class="text-xs uppercase tracking-[0.2em] font-black text-cyan-700">MephEd Services</p>
        <h1 class="text-4xl md:text-5xl font-black mt-2 leading-tight text-slate-900">Find The Right Service For Your Learning Goals</h1>
        <p class="text-slate-600 mt-4 max-w-3xl">
            Explore every active MephEd service item, compare options, and open a dedicated page with full service details.
        </p>
    </section>

    @foreach ($services->sortByDesc('name') as $service)
        @if ($service->serviceItems->isNotEmpty())
            <section class="space-y-4">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900">{{ $service->name }}</h2>
                        <p class="text-slate-600 text-sm">{{ $service->description }}</p>
                    </div>
                    <span class="text-[10px] uppercase font-black tracking-wider bg-cyan-50 text-cyan-700 px-3 py-1 rounded-full border border-cyan-100">
                        {{ $service->target }}
                    </span>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($service->serviceItems as $item)
                        <a wire:navigate href="{{ route('services.show', ['serviceItem' => $item->slug]) }}"
                            class="block bg-white text-slate-800 rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all">
                            <img src="{{ $item->image_url }}"
                                alt="{{ $item->name }}" class="h-40 w-full object-cover">
                            <div class="p-5">
                                <h3 class="text-lg font-black">{{ $item->name }}</h3>
                                <p class="text-sm text-slate-600 mt-2 line-clamp-3">{{ $item->description }}</p>
                                <p class="text-xs font-bold text-cyan-700 mt-3 uppercase tracking-wide">View service page</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
</div>
