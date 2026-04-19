<div class="max-w-6xl mx-auto px-4 py-10 space-y-10 text-slate-800">
    <section class="grid lg:grid-cols-2 gap-8 items-center aurora-bg text-white rounded-3xl p-8 md:p-10">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] font-black text-cyan-100">{{ $serviceItem->service?->name }}</p>
            <h1 class="text-4xl md:text-5xl font-black mt-2 leading-tight">{{ $serviceItem->name }}</h1>
            <p class="text-cyan-50 mt-4 text-base leading-relaxed max-w-2xl">
                {{ $serviceItem->description ?: 'Practical educational support with clear learning goals and dependable delivery.' }}
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a wire:navigate href="{{ $this->applyUrl }}"
                    class="pulse-ring px-6 py-3 rounded-xl bg-white text-cyan-800 font-black hover:bg-cyan-50 transition-all">
                    Start Request
                </a>
                <a wire:navigate href="{{ route('services') }}"
                    class="px-6 py-3 rounded-xl bg-cyan-600/40 border border-cyan-200/50 text-cyan-100 font-bold hover:bg-cyan-500/50 transition-all">
                    Explore All Services
                </a>
            </div>
        </div>
        <div class="relative">
            <img src="{{ $serviceItem->image_url }}" alt="{{ $serviceItem->name }}"
                class="w-full h-80 object-cover rounded-2xl shadow-2xl ring-1 ring-white/25">
            <div class="absolute -bottom-4 -right-4 bg-white/90 text-slate-800 rounded-xl px-4 py-2 text-xs font-black">
                Trusted | Flexible | Results-Focused
            </div>
        </div>
    </section>

    <section class="bg-white text-slate-800 rounded-3xl p-8 border border-slate-200">
        <h2 class="text-2xl font-black">Why Organisations and Families Choose MephEd For {{ $serviceItem->name }}</h2>
        <div class="grid md:grid-cols-3 gap-4 mt-5">
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 hover-lift">
                <p class="font-black">Clear Process</p>
                <p class="text-sm text-slate-600 mt-1">Clear scope, clear communication, and dependable follow-through at each stage.</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 hover-lift">
                <p class="font-black">Qualified Expertise</p>
                <p class="text-sm text-slate-600 mt-1">Experienced tutors and facilitators aligned to learner needs and goals.</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 hover-lift">
                <p class="font-black">Flexible Delivery</p>
                <p class="text-sm text-slate-600 mt-1">Online, onsite, or hybrid options based on your schedule and context.</p>
            </div>
        </div>
    </section>

    <section class="bg-slate-900 border border-slate-800 rounded-3xl p-8 text-slate-100">
        <h2 class="text-2xl font-black">Service Delivery FAQ</h2>
        <div class="mt-5 space-y-4">
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="font-black">How do we start?</h3>
                <p class="text-sm text-slate-300 mt-1">Start the request form on this page, submit your needs, and our team will follow up with next steps.</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="font-black">Can delivery be online or onsite?</h3>
                <p class="text-sm text-slate-300 mt-1">Yes. Delivery configuration depends on your preferred model, schedule, and context.</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="font-black">Do you support both institutions and individuals?</h3>
                <p class="text-sm text-slate-300 mt-1">Yes. We support both institutions and individuals, based on the selected service.</p>
            </div>
        </div>
    </section>

    @if ($relatedItems->isNotEmpty())
        <section class="space-y-4">
            <h2 class="text-2xl font-black text-slate-900">Related {{ $serviceItem->service?->name }} Services</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($relatedItems as $related)
                    <a wire:navigate href="{{ route('services.show', ['serviceItem' => $related->slug]) }}"
                        class="bg-white text-slate-800 rounded-2xl p-4 border border-slate-200 hover-lift">
                        <p class="font-black">{{ $related->name }}</p>
                        <p class="text-xs text-slate-600 mt-1 line-clamp-3">{{ $related->description }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
