<div class="max-w-6xl mx-auto px-4 py-10 space-y-10 text-slate-800">
    <section class="grid lg:grid-cols-2 gap-8 items-center bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] font-black text-cyan-700">{{ $serviceItem->service?->name }}</p>
            <h1 class="text-4xl md:text-5xl font-black mt-2 leading-tight text-slate-900">{{ $serviceItem->name }}</h1>
            <p class="text-slate-600 mt-4 text-base leading-relaxed">
                {{ $serviceItem->description ?: 'Personalized and result-driven educational support tailored to your goals.' }}
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a wire:navigate href="{{ $this->applyUrl }}"
                    class="px-6 py-3 rounded-xl bg-cyan-500 text-white font-black hover:bg-cyan-600 transition-all">
                    Get Started
                </a>
                <a wire:navigate href="{{ route('services') }}"
                    class="px-6 py-3 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 font-bold hover:bg-slate-200 transition-all">
                    Explore Other Services
                </a>
            </div>
        </div>
        <div>
            <img src="{{ $serviceItem->image_url }}" alt="{{ $serviceItem->name }}"
                class="w-full h-80 object-cover rounded-2xl shadow-lg">
        </div>
    </section>

    <section class="bg-white text-slate-800 rounded-3xl p-8 border border-slate-200">
        <h2 class="text-2xl font-black">Why Choose MephEd For {{ $serviceItem->name }}?</h2>
        <div class="grid md:grid-cols-3 gap-4 mt-5">
            <div class="bg-slate-50 rounded-2xl p-4">
                <p class="font-black">Structured Delivery</p>
                <p class="text-sm text-slate-600 mt-1">Service execution with clear milestones, communication, and measurable outcomes.</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4">
                <p class="font-black">Qualified Team</p>
                <p class="text-sm text-slate-600 mt-1">Experienced professionals aligned to your learning context and requirements.</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4">
                <p class="font-black">Flexible Engagement</p>
                <p class="text-sm text-slate-600 mt-1">Online, onsite, or hybrid options based on your goals, timeline, and budget.</p>
            </div>
        </div>
    </section>

    <section class="bg-slate-900 border border-slate-800 rounded-3xl p-8 text-slate-100">
        <h2 class="text-2xl font-black">Frequently Asked Questions</h2>
        <div class="mt-5 space-y-4">
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="font-black">How do I request this service?</h3>
                <p class="text-sm text-slate-300 mt-1">Click "Get Started" above and complete the request form. Our team will review and follow up.</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="font-black">Can service delivery be online or onsite?</h3>
                <p class="text-sm text-slate-300 mt-1">Yes. Delivery mode depends on your request details and preferred setup.</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4">
                <h3 class="font-black">Do you support institutions and individuals?</h3>
                <p class="text-sm text-slate-300 mt-1">Yes. We support both based on the selected service item.</p>
            </div>
        </div>
    </section>

    @if ($relatedItems->isNotEmpty())
        <section class="space-y-4">
            <h2 class="text-2xl font-black text-slate-900">Related {{ $serviceItem->service?->name }} Services</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($relatedItems as $related)
                    <a wire:navigate href="{{ route('services.show', ['serviceItem' => $related->slug]) }}"
                        class="bg-white text-slate-800 rounded-2xl p-4 border border-slate-200 hover:shadow-lg transition-all">
                        <p class="font-black">{{ $related->name }}</p>
                        <p class="text-xs text-slate-600 mt-1 line-clamp-3">{{ $related->description }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
