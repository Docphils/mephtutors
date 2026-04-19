@php
    $authRole = auth()->user()->role ?? null;
    $isAuthenticatedUser = auth()->check();
    $isClientAuthenticated = $authRole === 'client';
    $interventionRequestUrl = match ($authRole) {
        'client' => route('client.interventions.create', ['programme' => $programme->slug]),
        'admin' => route('admin.dashboard'),
        'tutor' => route('tutor.dashboard'),
        default => route('programmes.request', ['academicProgramme' => $programme->slug]),
    };
@endphp

<div class="max-w-6xl mx-auto px-4 py-10 space-y-8 text-slate-800">
    <section data-reveal="zoom" class="relative overflow-hidden aurora-bg text-white rounded-3xl p-8 md:p-10">
        <div class="absolute -top-16 -left-12 w-56 h-56 rounded-full bg-cyan-200/20 blur-3xl"></div>
        <div class="absolute -bottom-16 -right-12 w-72 h-72 rounded-full bg-cyan-50/10 blur-3xl"></div>
        <div class="relative">
            <p class="text-xs tracking-[0.22em] uppercase font-bold text-cyan-100">Academic Intervention</p>
            <h1 class="text-3xl md:text-5xl font-black mt-2">{{ $programme->name }}</h1>
            <p class="text-cyan-50 mt-4 max-w-3xl text-base md:text-lg">{{ $programme->tagline }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a wire:navigate href="{{ $interventionRequestUrl }}"
                    class="pulse-ring px-5 py-3 rounded-xl bg-white text-cyan-800 font-black hover:bg-cyan-50 transition">
                    Book a Trial Class
                </a>
                <a wire:navigate href="{{ $interventionRequestUrl }}"
                    class="px-5 py-3 rounded-xl bg-cyan-500 text-white font-black hover:bg-cyan-400 transition">
                    Start Intervention Request
                </a>
            </div>
        </div>
    </section>

    <section data-reveal class="grid lg:grid-cols-3 gap-5">
        <article data-reveal="left" class="lg:col-span-2 vibrant-panel border border-slate-200 rounded-2xl p-6 hover-lift">
            <h2 class="text-2xl font-black text-slate-900">Intervention Overview</h2>
            <p class="text-slate-600 mt-3 leading-relaxed">{{ $programme->overview ?: $programme->summary }}</p>
            @if ($programme->renewability_note)
                <p class="mt-4 text-sm font-bold text-cyan-800">Renewability: {{ $programme->renewability_note }}</p>
            @endif
        </article>
        <aside data-reveal="right" class="vibrant-panel border border-slate-200 rounded-2xl p-6 hover-lift">
            <h2 class="text-xl font-black text-slate-900">Pricing Framework</h2>
            <p class="text-sm text-slate-600 mt-3">{{ $programme->starting_from_text ?: 'Pricing available on request' }}</p>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed">{{ $programme->pricing_note ?: $defaultPricingNote }}</p>
        </aside>
    </section>

    <section data-reveal class="grid md:grid-cols-2 gap-5">
        <article data-reveal="up" class="vibrant-panel border border-slate-200 rounded-2xl p-6 hover-lift">
            <h3 class="text-xl font-black text-slate-900">Who This Intervention Is For</h3>
            <p class="text-slate-600 mt-3 leading-relaxed">{{ $programme->who_it_is_for }}</p>
        </article>
        <article data-reveal="up" data-reveal-delay="100" class="vibrant-panel border border-slate-200 rounded-2xl p-6 hover-lift">
            <h3 class="text-xl font-black text-slate-900">What Parents Can Expect</h3>
            <p class="text-slate-600 mt-3 leading-relaxed">{{ $programme->what_parents_can_expect }}</p>
        </article>
    </section>

    <section data-reveal class="grid md:grid-cols-3 gap-5">
        <article data-reveal="up" class="vibrant-panel border border-slate-200 rounded-2xl p-6 hover-lift">
            <h3 class="font-black text-slate-900">Frequency Options</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                @foreach ($programme->frequency_options ?? [] as $option)
                    <li class="bg-slate-50 rounded-lg px-3 py-2 border border-slate-100">{{ $option }}</li>
                @endforeach
            </ul>
        </article>
        <article data-reveal="up" data-reveal-delay="80" class="vibrant-panel border border-slate-200 rounded-2xl p-6 hover-lift">
            <h3 class="font-black text-slate-900">Session Duration</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                @foreach ($programme->duration_options ?? [] as $option)
                    <li class="bg-slate-50 rounded-lg px-3 py-2 border border-slate-100">{{ $option }}</li>
                @endforeach
            </ul>
        </article>
        <article data-reveal="up" data-reveal-delay="140" class="vibrant-panel border border-slate-200 rounded-2xl p-6 hover-lift">
            <h3 class="font-black text-slate-900">Delivery Modes</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                @foreach ($programme->mode_options ?? [] as $option)
                    <li class="bg-slate-50 rounded-lg px-3 py-2 border border-slate-100">{{ ucfirst($option) }}</li>
                @endforeach
            </ul>
        </article>
    </section>

    <section data-reveal class="bg-slate-900 text-slate-100 rounded-3xl p-8">
        <h2 class="text-2xl font-black">Tutor Match Assurance</h2>
        <p class="text-slate-300 mt-2">
            We monitor early sessions closely and support reassignment when tutor fit is not right.
        </p>
        <div class="grid md:grid-cols-2 gap-3 mt-4 text-sm">
            <div class="bg-slate-800 rounded-xl p-3">Trial class enabled: <strong>{{ $trialClassEnabled ? 'Yes' : 'No' }}</strong></div>
            <div class="bg-slate-800 rounded-xl p-3">Two probationary classes: <strong>{{ $probationaryAllowed ? 'Allowed' : 'Not enabled' }}</strong></div>
            <div class="bg-slate-800 rounded-xl p-3 md:col-span-2">Refund requests are reviewed based on service terms and case details.</div>
        </div>
    </section>

    <section data-reveal class="bg-white border border-slate-200 rounded-2xl p-6">
        <h2 class="text-2xl font-black text-slate-900">Frequently Asked Questions</h2>
        <div class="mt-4 space-y-3">
            @forelse ($programme->faq_items ?? [] as $faq)
                <article class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <h3 class="font-black text-slate-900">{{ $faq['q'] ?? 'Question' }}</h3>
                    <p class="text-sm text-slate-600 mt-1">{{ $faq['a'] ?? '' }}</p>
                </article>
            @empty
                <article class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600">
                    Contact our support desk for intervention-specific clarifications.
                </article>
            @endforelse
        </div>
    </section>

    <section data-reveal class="aurora-bg rounded-3xl p-8 text-white">
        <h2 class="text-3xl font-black">Ready To Start {{ $programme->name }}?</h2>
        <p class="text-cyan-50 mt-2 max-w-2xl">Share your learner details and preferred schedule. Our team will contact you with next steps.</p>
        <div class="mt-5">
            <a wire:navigate href="{{ $interventionRequestUrl }}"
                class="inline-flex px-6 py-3 rounded-xl bg-white text-cyan-800 font-black hover:bg-cyan-50 transition">
                Start Intervention
            </a>
        </div>
    </section>

    @if ($isAuthenticatedUser)
        <section data-reveal class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8">
            <h2 class="text-2xl font-black text-slate-900">Start {{ $programme->name }} Intervention Request</h2>
            <p class="text-slate-600 mt-2">
                @if ($isClientAuthenticated)
                    Continue this request from your client dashboard.
                @else
                    Continue from your dashboard.
                @endif
            </p>
            <div class="mt-5">
                <a wire:navigate href="{{ $interventionRequestUrl }}"
                    class="inline-flex px-6 py-3 rounded-xl bg-cyan-600 text-white font-black hover:bg-cyan-700 transition">
                    @if ($isClientAuthenticated)
                        Open Client Request Form
                    @else
                        Open Dashboard
                    @endif
                </a>
            </div>
        </section>
    @else
        <section data-reveal class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8">
            <h2 class="text-2xl font-black text-slate-900">Start {{ $programme->name }} Intervention Request</h2>
            <p class="text-slate-600 mt-2">Complete this form to get your estimate, create your account, and continue to payment.</p>
            <div class="mt-5">
                <livewire:requests.programme-enquiry-wizard :academic-programme="$programme" />
            </div>
        </section>
    @endif
</div>
