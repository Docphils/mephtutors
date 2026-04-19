<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    <x-slot name="header">
        <div>
            <h1 class="text-3xl font-black text-slate-800">New <span class="text-cyan-600">Intervention Request</span></h1>
            <p class="text-slate-500 text-sm mt-1">Choose an intervention to begin.</p>
        </div>
    </x-slot>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 md:p-8 space-y-4">
        @if ($programmes->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                No active interventions are currently available. Please check back shortly.
            </div>
        @else
            <div>
                <label class="text-xs font-bold text-slate-600">Preferred Intervention</label>
                <select wire:model.live="selected_programme" class="mt-1 w-full rounded-xl border-slate-300">
                    <option value="">Select an intervention</option>
                    @foreach ($programmes as $programme)
                        <option value="{{ $programme->slug }}">{{ $programme->name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($selectedProgramme)
                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4">
                    <p class="text-sm font-black text-cyan-900">{{ $selectedProgramme->name }}</p>
                    <p class="text-xs text-cyan-800 mt-1">{{ $selectedProgramme->summary ?: $selectedProgramme->tagline }}</p>
                </div>
            @endif
        @endif
    </div>

    @if ($selectedProgramme)
        <div class="rounded-3xl border border-slate-200 bg-white p-4 md:p-6">
            <livewire:requests.programme-enquiry-wizard
                :academic-programme="$selectedProgramme"
                :key="'client-intervention-enquiry-'.$selectedProgramme->slug.'-'.auth()->id()" />
        </div>
    @endif
</div>

