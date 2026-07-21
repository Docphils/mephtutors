@php
    $authRole = auth()->user()->role ?? null;
    $interventionEntryUrl = match ($authRole) {
        'client' => route('programmes.enquiry'),
        'admin' => route('admin.dashboard'),
        'tutor' => route('tutor.dashboard'),
        default => route('programmes.enquiry'),
    };
@endphp

<div wire:key="welcome-academic" class="space-y-16 pb-20">
    <section x-data="{
        index: 0,
        timer: null,
        slides: [{
                image: '{{ asset('images/banner.jpg') }}',
                eyebrow: 'Exam-Season Delivery',
                title: 'Private Academic Support That Improves Results',
                summary: 'Focused tutoring for WAEC, NECO, NABTEB, and urgent subject support.'
            },
            ...@js(
    $programmes
        ->map(function ($programme) use ($authRole) {
            return [
                'image' => asset($programme->hero_image ?: 'images/banner2.jpg'),
                'eyebrow' => 'Intervention Spotlight',
                'title' => $programme->name,
                'summary' => $programme->tagline ?: $programme->summary,
                'url' => route('programmes.show', ['academicProgramme' => $programme->slug]),
                'request' => match ($authRole) {
                    'client' => route('client.interventions.create', ['programme' => $programme->slug]),
                    'admin' => route('admin.dashboard'),
                    'tutor' => route('tutor.dashboard'),
                    default => route('programmes.request', ['academicProgramme' => $programme->slug]),
                },
            ];
        })
        ->values()
        ->all(),
)
        ],
        next() { this.index = (this.index + 1) % this.slides.length; },
        prev() { this.index = (this.index - 1 + this.slides.length) % this.slides.length; },
        go(i) { this.index = i; },
        start() { this.timer = setInterval(() => this.next(), 6500); },
        stop() { if (this.timer) clearInterval(this.timer); },
    }" x-init="start()" @mouseenter="stop()" @mouseleave="start()"
        class="relative overflow-hidden h-[72vh] min-h-[520px] max-h-[820px]">
        <template x-for="(slide, i) in slides" :key="i">
            <div class="absolute inset-0 transition-opacity duration-1000"
                :class="index === i ? 'opacity-100' : 'opacity-0'">
                <img :src="slide.image" alt="Academic support"
                    class="w-full h-full object-cover scale-[1.08] transition-transform duration-[4000ms]"
                    :class="index === i ? 'scale-[1.0]' : 'scale-[1.08]'">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/55 to-cyan-900/50"></div>
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(34,211,238,0.20),transparent_40%)]">
                </div>
            </div>
        </template>

        <div class="relative z-10 max-w-6xl mx-auto h-full px-4 py-12 md:py-16 flex flex-col justify-center">
            <div class="max-w-3xl text-white space-y-5">
                <p class="text-xs tracking-[0.24em] uppercase font-black text-cyan-100" x-text="slides[index].eyebrow">
                </p>
                <h1 class="text-4xl md:text-6xl font-black leading-tight" x-text="slides[index].title"></h1>
                <p class="text-cyan-50 text-base md:text-xl max-w-2xl" x-text="slides[index].summary"></p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a wire:navigate href="{{ $interventionEntryUrl }}"
                        class="pulse-ring inline-flex px-6 py-3 rounded-xl bg-white text-cyan-800 font-black hover:bg-cyan-50 transition">
                        Select Intervention
                    </a>
                    <a wire:navigate :href="slides[index]?.request || '{{ $interventionEntryUrl }}'"
                        class="inline-flex px-6 py-3 rounded-xl bg-cyan-500 text-white font-black hover:bg-cyan-400 transition">
                        Request This Intervention
                    </a>
                    <a wire:navigate :href="slides[index]?.url || '#programmes'"
                        class="inline-flex px-6 py-3 rounded-xl border border-cyan-100/70 text-cyan-100 font-bold hover:bg-white/10 transition">
                        View Details
                    </a>
                </div>
            </div>

            <div class="pt-8 flex items-center gap-2">
                <button type="button" @click="prev()"
                    class="w-10 h-10 rounded-full bg-white/20 border border-white/30 text-white hover:bg-white/30 transition">
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <div class="flex gap-2">
                    <template x-for="(slide, i) in slides" :key="'dot-' + i">
                        <button type="button" @click="go(i)" class="h-2 rounded-full transition-all"
                            :class="index === i ? 'w-8 bg-cyan-300' : 'w-2 bg-white/50'"></button>
                    </template>
                </div>
                <button type="button" @click="next()"
                    class="w-10 h-10 rounded-full bg-white/20 border border-white/30 text-white hover:bg-white/30 transition">
                    <i class="fa-solid fa-angle-right"></i>
                </button>
            </div>
        </div>
    </section>

    <section data-reveal class="max-w-6xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-4">
            <div data-reveal="up" class="bg-white border border-slate-200 rounded-2xl p-5 hover-lift">
                <p class="text-3xl font-black text-cyan-700">92%</p>
                <p class="text-sm text-slate-600 mt-1">Parents report stronger learner confidence after first cycle.</p>
            </div>
            <div data-reveal="up" data-reveal-delay="80"
                class="bg-white border border-slate-200 rounded-2xl p-5 hover-lift">
                <p class="text-3xl font-black text-cyan-700">48hrs</p>
                <p class="text-sm text-slate-600 mt-1">Average response time for new requests.</p>
            </div>
            <div data-reveal="up" data-reveal-delay="140"
                class="bg-white border border-slate-200 rounded-2xl p-5 hover-lift">
                <p class="text-3xl font-black text-cyan-700">3 Tracks</p>
                <p class="text-sm text-slate-600 mt-1">Exam prep, term support, and 30-day subject rescue.</p>
            </div>
            <div data-reveal="up" data-reveal-delay="200"
                class="bg-white border border-slate-200 rounded-2xl p-5 hover-lift">
                <p class="text-3xl font-black text-cyan-700">100%</p>
                <p class="text-sm text-slate-600 mt-1">Intervention plans mapped to learner-specific weak areas and
                    goals.</p>
            </div>
        </div>
    </section>

    <section id="programmes" data-reveal class="max-w-6xl mx-auto px-4 space-y-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900">Academic Interventions</h2>
                <p class="text-slate-600 mt-2">Focused plans designed for steady progress and academic recovery.</p>
            </div>
            <a wire:navigate href="{{ $interventionEntryUrl }}"
                class="inline-flex px-5 py-2.5 rounded-xl bg-cyan-600 text-white font-black hover:bg-cyan-700 transition">
                Start Intervention
            </a>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($programmes as $programme)
                <article data-reveal="up" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover-lift">
                    <p class="text-[11px] uppercase tracking-widest font-bold text-cyan-700">Intervention</p>
                    <h3 class="text-xl font-black text-slate-900 mt-2">{{ $programme->name }}</h3>
                    <p class="text-slate-600 text-sm mt-2 min-h-16">{{ $programme->summary ?: $programme->tagline }}</p>
                    <div class="mt-3 text-sm font-bold text-slate-700">
                        {{ $programme->starting_from_text ?: 'Pricing on request' }}</div>
                    <div class="mt-4 flex gap-2">
                        <a wire:navigate
                            href="{{ route('programmes.show', ['academicProgramme' => $programme->slug]) }}"
                            class="px-4 py-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-200 transition">
                            Details
                        </a>
                        <a wire:navigate
                            href="{{ match ($authRole) {
                                'client' => route('client.interventions.create', ['programme' => $programme->slug]),
                                'admin' => route('admin.dashboard'),
                                'tutor' => route('tutor.dashboard'),
                                default => route('programmes.request', ['academicProgramme' => $programme->slug]),
                            } }}"
                            class="px-4 py-2 rounded-lg bg-cyan-600 text-white font-bold text-sm hover:bg-cyan-700 transition">
                            Request
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section data-reveal class="max-w-6xl mx-auto px-4">
        <div class="bg-white border border-slate-200 rounded-3xl p-7 md:p-9">
            <h2 class="text-3xl font-black text-slate-900">How It Works</h2>
            <div class="mt-6 grid md:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-widest text-cyan-700 font-bold">01</p>
                    <h3 class="font-black mt-1">Needs Diagnosis</h3>
                    <p class="text-sm text-slate-600 mt-1">We review current performance, weak areas, and goals.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-widest text-cyan-700 font-bold">02</p>
                    <h3 class="font-black mt-1">Tutor Matching</h3>
                    <p class="text-sm text-slate-600 mt-1">We match tutors by subject, class level, and learning mode.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-widest text-cyan-700 font-bold">03</p>
                    <h3 class="font-black mt-1">Structured Sessions</h3>
                    <p class="text-sm text-slate-600 mt-1">Classes follow an agreed schedule and clear learning goals.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-widest text-cyan-700 font-bold">04</p>
                    <h3 class="font-black mt-1">Performance Review</h3>
                    <p class="text-sm text-slate-600 mt-1">Progress reviews guide next steps and renewals.</p>
                </div>
            </div>
        </div>
    </section>

    <section data-reveal class="max-w-6xl mx-auto px-4">
        <div class="grid lg:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 hover-lift">
                <h3 class="font-black text-slate-900">Clear Onboarding</h3>
                <p class="text-sm text-slate-600 mt-2">Every request follows a clear setup process from profile review
                    to class start.</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 hover-lift">
                <h3 class="font-black text-slate-900">Tutor Match Support</h3>
                <p class="text-sm text-slate-600 mt-2">If tutor fit is not right in the early stage, we review and
                    support reassignment.</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 hover-lift">
                <h3 class="font-black text-slate-900">Transparent Pricing</h3>
                <p class="text-sm text-slate-600 mt-2">Pricing is based on frequency, session duration, delivery mode,
                    and location for home lessons.</p>
            </div>
        </div>
    </section>

    <section data-reveal class="max-w-6xl mx-auto px-4">
        <div class="bg-slate-900 text-slate-100 rounded-3xl p-8 md:p-10 space-y-4">
            <h2 class="text-2xl md:text-3xl font-black">Confidence &amp; Tutor Match Promise</h2>
            <p class="text-slate-300">
                We combine quality checks with clear service terms so families know what to expect.
            </p>
            <ul class="grid md:grid-cols-2 gap-3 text-sm text-slate-200">
                <li class="bg-slate-800 rounded-xl p-3">Trial class availability:
                    <strong>{{ $trialClassEnabled ? 'Enabled' : 'Currently paused' }}</strong></li>
                <li class="bg-slate-800 rounded-xl p-3">Extended probation:
                    <strong>{{ $probationaryAllowed ? 'Available where approved' : 'Restricted to standard window' }}</strong>
                </li>
                <li class="bg-slate-800 rounded-xl p-3">Tutor reassignment is available when early fit is not right.
                </li>
                <li class="bg-slate-800 rounded-xl p-3">Refund requests are handled based on service terms and case
                    review.</li>
            </ul>
        </div>
    </section>

    <section data-reveal class="max-w-6xl mx-auto px-4">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8">
            <h2 class="text-2xl font-black text-slate-900">Not Sure Which Intervention Fits Best?</h2>
            <p class="text-slate-600 mt-2">Choose any intervention to begin. We will guide you to the best fit for your
                learner.</p>
            <div class="mt-5">
                <a wire:navigate href="{{ $interventionEntryUrl }}"
                    class="inline-flex px-6 py-3 rounded-xl bg-cyan-600 text-white font-black hover:bg-cyan-700 transition">
                    Select Intervention
                </a>
            </div>
        </div>
    </section>

    <section data-reveal class="max-w-6xl mx-auto px-4">
        <div x-data="{ open: 1 }" class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8">
            <h2 class="text-3xl font-black text-slate-900">Frequently Asked Questions</h2>
            <p class="text-slate-600 mt-2">Quick answers on delivery, pricing, and scheduling.</p>

            <div class="mt-6 space-y-3">
                @php
                    $faqList = [
                        [
                            'q' => 'How quickly can classes start after enquiry?',
                            'a' =>
                                'Most learners can start within 48 hours after profile review and tutor confirmation.',
                        ],
                        [
                            'q' => 'Can we choose online or home lessons?',
                            'a' => 'Yes. You can choose online or home lessons during setup.',
                        ],
                        [
                            'q' => 'Do you support multiple subjects under one intervention?',
                            'a' => 'Yes. We can include multiple subjects based on learner needs and schedule.',
                        ],
                        [
                            'q' => 'How is pricing determined?',
                            'a' =>
                                'Pricing depends on weekly frequency, session duration, mode, and location for home lessons.',
                        ],
                        [
                            'q' => 'Can interventions be renewed?',
                            'a' => 'Yes. Monthly interventions can be renewed based on learner progress.',
                        ],
                        [
                            'q' => 'What happens if tutor fit is not right?',
                            'a' => 'If tutor fit is poor, we review and arrange reassignment where appropriate.',
                        ],
                        [
                            'q' => 'Is there a trial class option?',
                            'a' => 'Yes, where enabled for the selected intervention.',
                        ],
                        [
                            'q' => 'How do you track learner progress?',
                            'a' => 'We track progress through goals, assignments, and parent feedback.',
                        ],
                        [
                            'q' => 'Do you support urgent short-term interventions?',
                            'a' => 'Yes. 30-Day Subject Rescue is designed for urgent support.',
                        ],
                        [
                            'q' => 'Can we switch from online to home lessons later?',
                            'a' => 'Yes, subject to logistics and tutor availability.',
                        ],
                    ];
                @endphp

                @foreach ($faqList as $idx => $faq)
                    <article class="border border-slate-200 rounded-2xl overflow-hidden">
                        <button type="button"
                            @click="open === {{ $idx + 1 }} ? open = 0 : open = {{ $idx + 1 }}"
                            class="w-full text-left px-5 py-4 flex items-center justify-between bg-slate-50 hover:bg-slate-100 transition">
                            <span class="font-black text-slate-900 text-sm md:text-base">{{ $faq['q'] }}</span>
                            <span class="text-cyan-700 font-black text-lg"
                                x-text="open === {{ $idx + 1 }} ? '-' : '+'"></span>
                        </button>
                        <div x-show="open === {{ $idx + 1 }}" x-collapse
                            class="px-5 py-4 text-sm text-slate-600 leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section data-reveal class="max-w-6xl mx-auto px-4">
        <div class="aurora-bg rounded-3xl p-8 md:p-10 text-white">
            <h2 class="text-3xl font-black">Ready to Improve Academic Performance?</h2>
            <p class="mt-3 text-cyan-50 max-w-2xl">
                Share your learner details today. We will recommend the best intervention and next steps.
            </p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a wire:navigate href="{{ $interventionEntryUrl }}"
                    class="inline-flex px-6 py-3 rounded-xl bg-white text-cyan-800 font-black hover:bg-cyan-50 transition">
                    Request Support
                </a>
                <a wire:navigate href="{{ route('contact') }}"
                    class="inline-flex px-6 py-3 rounded-xl border border-cyan-100/70 text-cyan-100 font-bold hover:bg-white/10 transition">
                    Contact Team
                </a>
            </div>
        </div>
    </section>
</div>
