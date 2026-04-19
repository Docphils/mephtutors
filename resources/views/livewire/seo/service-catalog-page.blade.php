@php
    $interventionEntryUrl = match (auth()->user()->role ?? null) {
        'client' => route('programmes.enquiry'),
        'admin' => route('admin.dashboard'),
        'tutor' => route('tutor.dashboard'),
        default => route('programmes.enquiry'),
    };
@endphp

<div class="max-w-6xl mx-auto px-4 py-10 space-y-10 text-slate-800">
    <section class="relative overflow-hidden aurora-bg rounded-3xl p-8 md:p-10 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.16),transparent_52%)]">
        </div>
        <div class="relative">
            <p class="text-xs uppercase tracking-[0.2em] font-black text-cyan-100">Service Portfolio</p>
            <h1 class="text-4xl md:text-5xl font-black mt-2 leading-tight">Enterprise Learning Services For Families,
                Schools, and Institutions</h1>
            <p class="text-cyan-50 mt-4 max-w-3xl text-base md:text-lg">
                Explore our active service lines and choose the option that fits your learning needs.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a wire:navigate href="{{ route('contact') }}"
                    class="px-6 py-3 rounded-xl bg-white text-cyan-800 font-black hover:bg-cyan-50 transition">Talk to
                    Support</a>
                <a wire:navigate href="{{ $interventionEntryUrl }}"
                    class="px-6 py-3 rounded-xl border border-cyan-100/60 text-cyan-100 font-bold hover:bg-white/10 transition">Intervention
                    Request</a>
            </div>
        </div>
    </section>

    @foreach ($services->sortByDesc('name') as $service)
        @if ($service->serviceItems->isNotEmpty())
            <section class="space-y-4">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-black text-slate-900">{{ $service->name }}</h2>
                        <p class="text-slate-600 text-sm md:text-base max-w-3xl">{{ $service->description }}</p>
                    </div>
                    <span
                        class="text-[10px] uppercase font-black tracking-wider bg-cyan-50 text-cyan-700 px-3 py-1 rounded-full border border-cyan-100">
                        {{ $service->target }}
                    </span>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($service->serviceItems as $item)
                        <a wire:navigate href="{{ route('services.show', ['serviceItem' => $item->slug]) }}"
                            class="group block bg-white text-slate-800 rounded-2xl overflow-hidden border border-slate-200 hover-lift">
                            <div class="relative overflow-hidden">
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                    class="h-44 w-full object-cover transition-transform duration-500 group-hover:scale-110">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-slate-900/45 via-transparent to-transparent">
                                </div>
                            </div>
                            <div class="p-5">
                                <h3 class="text-lg font-black">{{ $item->name }}</h3>
                                <p class="text-sm text-slate-600 mt-2 line-clamp-3">{{ $item->description }}</p>
                                <p class="text-xs font-bold text-cyan-700 mt-3 uppercase tracking-wide">Open service
                                    page</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
</div>
