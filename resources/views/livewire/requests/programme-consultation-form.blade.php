@php
    $isAuthenticated = auth()->check();
    $isClientAuthenticated = $authRole === 'client';
@endphp

<div x-data
    x-on:intervention-selected.window="$nextTick(() => document.getElementById('intervention-request-flow')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
    class="max-w-6xl mx-auto px-4 py-10 space-y-8">
    <section data-reveal="zoom" class="aurora-bg text-white rounded-3xl p-8 md:p-10 relative overflow-hidden">
        <div class="absolute -top-24 -left-14 w-72 h-72 bg-cyan-200/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -right-14 w-80 h-80 bg-emerald-200/15 rounded-full blur-3xl"></div>
        <div class="relative space-y-4 max-w-4xl">
            <p class="text-xs uppercase tracking-[0.2em] font-black text-cyan-100">Intervention Directory</p>
            <h1 class="text-3xl md:text-5xl font-black">Choose the right intervention before you request</h1>
            <p class="text-cyan-50 text-base md:text-lg">
                Review available interventions, compare focus areas, then continue with the one that best fits your learner.
                @if ($isAuthenticated)
                    Your request will continue in your protected dashboard flow.
                @else
                    Your selected intervention form will open immediately below.
                @endif
            </p>
            <div class="flex flex-wrap gap-2 pt-1 text-xs font-bold">
                <span class="px-3 py-1 rounded-full bg-white/15 border border-white/20">Step 1: Select Intervention</span>
                <span class="px-3 py-1 rounded-full bg-white/15 border border-white/20">
                    @if ($isAuthenticated)
                        Step 2: Continue In Dashboard
                    @else
                        Step 2: Complete Request Form
                    @endif
                </span>
            </div>
        </div>
    </section>

    @if ($programmes->isEmpty())
        <section data-reveal class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            No active interventions are currently available. Please check back shortly.
        </section>
    @else
        <section class="space-y-5">
            <div data-reveal class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900">Available Interventions</h2>
                    <p class="text-slate-600 mt-1">Select one to continue with the appropriate request flow.</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($programmes as $programme)
                    <article data-reveal="up" class="vibrant-panel border border-slate-200 rounded-2xl p-5 shadow-sm hover-lift">
                        <p class="text-[11px] uppercase tracking-widest font-bold text-cyan-700">Intervention</p>
                        <h3 class="text-xl font-black text-slate-900 mt-1">{{ $programme->name }}</h3>
                        <p class="text-sm text-slate-600 mt-3 min-h-[68px]">{{ $programme->summary ?: $programme->tagline }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a wire:navigate href="{{ route('programmes.show', ['academicProgramme' => $programme->slug]) }}"
                                class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-bold hover:bg-slate-100 transition">
                                View Details
                            </a>
                            <button type="button" wire:click="requestProgramme('{{ $programme->slug }}')"
                                class="px-3.5 py-2 rounded-lg bg-cyan-600 text-white text-sm font-black hover:bg-cyan-700 transition">
                                {{ $isAuthenticated ? 'Request This Intervention' : 'Select & Continue' }}
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section data-reveal class="rounded-3xl border border-slate-200 bg-white p-6 md:p-7 space-y-4">
            <h3 class="text-xl font-black text-slate-900">Quick Selection</h3>
            <div class="grid md:grid-cols-[1fr_auto] gap-3 items-end">
                <div>
                    <label class="text-xs font-bold text-slate-600">Preferred Intervention</label>
                    <select wire:model.live="selected_programme" class="mt-1 w-full rounded-xl border-slate-300">
                        <option value="">Select an intervention</option>
                        @foreach ($programmes as $programme)
                            <option value="{{ $programme->slug }}">{{ $programme->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" wire:click="proceedWithSelectedProgramme" @disabled(!$selectedProgramme)
                    class="px-5 py-3 rounded-xl bg-cyan-600 text-white font-black hover:bg-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    @if ($isAuthenticated)
                        @if ($isClientAuthenticated)
                            Continue To Client Form
                        @else
                            Open Dashboard
                        @endif
                    @else
                        Continue With Selected Intervention
                    @endif
                </button>
            </div>

            @if ($selectedProgramme)
                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4">
                    <p class="text-sm font-black text-cyan-900">{{ $selectedProgramme->name }}</p>
                    <p class="text-xs text-cyan-800 mt-1">{{ $selectedProgramme->summary ?: $selectedProgramme->tagline }}</p>
                </div>
            @endif
        </section>
    @endif

    @if ($selectedProgramme && !$isAuthenticated)
        <section id="intervention-request-flow" data-reveal class="rounded-3xl border border-slate-200 bg-white p-4 md:p-6">
            <livewire:requests.programme-enquiry-wizard
                :academic-programme="$selectedProgramme"
                :key="'intervention-enquiry-'.$selectedProgramme->slug.'-guest'" />
        </section>
    @endif

    @if ($selectedProgramme && $isAuthenticated)
        <section data-reveal class="rounded-3xl border border-slate-200 bg-white p-6 md:p-7">
            <h3 class="text-2xl font-black text-slate-900">Selected: {{ $selectedProgramme->name }}</h3>
            <p class="text-slate-600 mt-2">
                Continue in your protected request flow to complete onboarding for this intervention.
            </p>
            <div class="mt-4">
                <button type="button" wire:click="proceedWithSelectedProgramme"
                    class="px-5 py-3 rounded-xl bg-cyan-600 text-white font-black hover:bg-cyan-700 transition">
                    @if ($isClientAuthenticated)
                        Open Client Request Form
                    @else
                        Open Dashboard
                    @endif
                </button>
            </div>
        </section>
    @endif
</div>
