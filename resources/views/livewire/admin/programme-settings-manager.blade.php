<div class="min-h-screen space-y-6 bg-slate-50 p-3 sm:p-4 lg:p-6">
    <x-slot name="header">
        <h2 class="text-xl font-black text-slate-800 sm:text-2xl">Intervention <span class="text-cyan-600">Settings</span>
        </h2>
    </x-slot>

    @if (session('success'))
        <div class="rounded-xl bg-emerald-500 px-4 py-3 text-sm font-bold text-white">{{ session('success') }}</div>
    @endif

    <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-lg font-black text-slate-800">Homepage Mode and Promise Settings</h3>
        <form wire:submit.prevent="saveSiteSettings" class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-bold text-slate-600">Homepage Mode</label>
                <select wire:model="homepage_mode" class="w-full rounded-xl border-slate-200">
                    <option value="default">Default Existing Homepage</option>
                    <option value="academic">Academic Intervention Homepage</option>
                </select>
                @error('homepage_mode')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-slate-600">WhatsApp Number (digits only)</label>
                <input wire:model="programme_whatsapp_number" type="text" class="w-full rounded-xl border-slate-200">
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-bold text-slate-600">Default Pricing Note</label>
                <textarea wire:model="programme_pricing_note_default" rows="2" class="w-full rounded-xl border-slate-200"></textarea>
            </div>

            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input type="checkbox" wire:model="programme_trial_class_enabled"
                    class="rounded border-slate-300 text-cyan-600">
                Trial Class Enabled
            </label>

            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                <input type="checkbox" wire:model="programme_probationary_classes_allowed"
                    class="rounded border-slate-300 text-cyan-600">
                Up to Two Probationary Classes Allowed
            </label>

            <div class="md:col-span-2">
                <button type="submit"
                    class="rounded-xl bg-cyan-600 px-5 py-2.5 font-black text-white transition hover:bg-cyan-700">
                    Save Settings
                </button>
            </div>
        </form>
    </section>

    <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <h3 class="text-lg font-black text-slate-800">Manage Intervention Pages</h3>
            <div class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search interventions..."
                    class="w-full rounded-xl border-slate-200 text-sm sm:w-72">
                @if (!$showProgrammeForm)
                    <button type="button" wire:click="openProgrammeForm"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-black">
                        Add Intervention
                    </button>
                @endif
            </div>
        </div>

        @if ($showProgrammeForm)
            @php
                $frequencyChoices = collect($frequency_option_rows)
                    ->pluck('label')
                    ->map(fn($item) => trim((string) $item))
                    ->filter()
                    ->unique()
                    ->values();
                $durationChoices = collect($duration_option_rows)
                    ->pluck('label')
                    ->map(fn($item) => trim((string) $item))
                    ->filter()
                    ->unique()
                    ->values();
                $modeChoices = collect($mode_option_rows)
                    ->pluck('label')
                    ->map(fn($item) => trim((string) $item))
                    ->filter()
                    ->unique()
                    ->values();
            @endphp

            <form wire:submit.prevent="saveProgramme"
                class="space-y-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-cyan-700">
                            {{ $editingId ? 'Editing Intervention' : 'New Intervention' }}
                        </p>
                        <h4 class="text-xl font-black text-slate-900">
                            {{ $editingId ? $name : 'Create New Intervention' }}</h4>
                    </div>
                    <button type="button" wire:click="cancelProgrammeForm"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100">
                        Close Form
                    </button>
                </div>

                <div class="grid gap-2 sm:grid-cols-4">
                    <button type="button" wire:click="goToFormStep(1)"
                        class="rounded-xl border px-3 py-2 text-xs font-black {{ $formStep === 1 ? 'border-cyan-600 bg-cyan-50 text-cyan-700' : 'border-slate-200 bg-white text-slate-500' }}">
                        Step 1: Basics
                    </button>
                    <button type="button" wire:click="goToFormStep(2)"
                        class="rounded-xl border px-3 py-2 text-xs font-black {{ $formStep === 2 ? 'border-cyan-600 bg-cyan-50 text-cyan-700' : 'border-slate-200 bg-white text-slate-500' }}">
                        Step 2: Options
                    </button>
                    <button type="button" wire:click="goToFormStep(3)"
                        class="rounded-xl border px-3 py-2 text-xs font-black {{ $formStep === 3 ? 'border-cyan-600 bg-cyan-50 text-cyan-700' : 'border-slate-200 bg-white text-slate-500' }}">
                        Step 3: Pricing
                    </button>
                    <button type="button" wire:click="goToFormStep(4)"
                        class="rounded-xl border px-3 py-2 text-xs font-black {{ $formStep === 4 ? 'border-cyan-600 bg-cyan-50 text-cyan-700' : 'border-slate-200 bg-white text-slate-500' }}">
                        Step 4: Publish
                    </button>
                </div>

                @if ($formStep === 1)
                    <section class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">Intervention Name</label>
                            <input wire:model="name" type="text" class="w-full rounded-xl border-slate-200"
                                placeholder="e.g. WAEC Final Sprint">
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">Slug (optional)</label>
                            <input wire:model="slug" type="text" class="w-full rounded-xl border-slate-200"
                                placeholder="auto-created if empty">
                            @error('slug')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold text-slate-600">Tagline</label>
                            <textarea wire:model="tagline" rows="2" class="w-full rounded-xl border-slate-200"
                                placeholder="Short statement shown at the top of the intervention page."></textarea>
                            @error('tagline')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold text-slate-600">Summary</label>
                            <textarea wire:model="summary" rows="2" class="w-full rounded-xl border-slate-200"
                                placeholder="Short paragraph for listings and cards."></textarea>
                            @error('summary')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold text-slate-600">Overview</label>
                            <textarea wire:model="overview" rows="4" class="w-full rounded-xl border-slate-200"
                                placeholder="Explain what this intervention delivers."></textarea>
                            @error('overview')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">Who It Is For</label>
                            <textarea wire:model="who_it_is_for" rows="4" class="w-full rounded-xl border-slate-200"
                                placeholder="Describe the ideal learners."></textarea>
                            @error('who_it_is_for')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">What Parents Can Expect</label>
                            <textarea wire:model="what_parents_can_expect" rows="4" class="w-full rounded-xl border-slate-200"
                                placeholder="Set expectations on updates, support, and outcomes."></textarea>
                            @error('what_parents_can_expect')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">Starting From Text</label>
                            <input wire:model="starting_from_text" type="text"
                                class="w-full rounded-xl border-slate-200" placeholder="e.g. From ₦45,000 / month">
                            @error('starting_from_text')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600">Renewability Note</label>
                            <input wire:model="renewability_note" type="text"
                                class="w-full rounded-xl border-slate-200"
                                placeholder="e.g. Renewable monthly by progress review">
                            @error('renewability_note')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold text-slate-600">Pricing Note</label>
                            <textarea wire:model="pricing_note" rows="2" class="w-full rounded-xl border-slate-200"
                                placeholder="Helpful explanation displayed with price info."></textarea>
                            @error('pricing_note')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>
                @endif

                @if ($formStep === 2)
                    <section class="space-y-4">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <h5 class="text-sm font-black text-slate-800">Frequency Options</h5>
                                    <button type="button" wire:click="addFrequencyOptionRow"
                                        class="rounded-lg bg-cyan-600 px-2.5 py-1 text-xs font-black text-white">Add</button>
                                </div>
                                <div class="space-y-2">
                                    @foreach ($frequency_option_rows as $index => $row)
                                        <div class="flex items-start gap-2"
                                            wire:key="frequency-option-{{ $index }}">
                                            <input wire:model="frequency_option_rows.{{ $index }}.label"
                                                type="text" class="w-full rounded-xl border-slate-200 text-sm"
                                                placeholder="e.g. 3x weekly">
                                            <button type="button"
                                                wire:click="removeFrequencyOptionRow({{ $index }})"
                                                class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold text-slate-600">
                                                Remove
                                            </button>
                                        </div>
                                        @error('frequency_option_rows.' . $index . '.label')
                                            <p class="text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <h5 class="text-sm font-black text-slate-800">Duration Options</h5>
                                    <button type="button" wire:click="addDurationOptionRow"
                                        class="rounded-lg bg-cyan-600 px-2.5 py-1 text-xs font-black text-white">Add</button>
                                </div>
                                <div class="space-y-2">
                                    @foreach ($duration_option_rows as $index => $row)
                                        <div class="flex items-start gap-2"
                                            wire:key="duration-option-{{ $index }}">
                                            <input wire:model="duration_option_rows.{{ $index }}.label"
                                                type="text" class="w-full rounded-xl border-slate-200 text-sm"
                                                placeholder="e.g. 2 hours">
                                            <button type="button"
                                                wire:click="removeDurationOptionRow({{ $index }})"
                                                class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold text-slate-600">
                                                Remove
                                            </button>
                                        </div>
                                        @error('duration_option_rows.' . $index . '.label')
                                            <p class="text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <h5 class="text-sm font-black text-slate-800">Lesson Mode Options</h5>
                                    <button type="button" wire:click="addModeOptionRow"
                                        class="rounded-lg bg-cyan-600 px-2.5 py-1 text-xs font-black text-white">Add</button>
                                </div>
                                <div class="space-y-2">
                                    @foreach ($mode_option_rows as $index => $row)
                                        <div class="flex items-start gap-2"
                                            wire:key="mode-option-{{ $index }}">
                                            <input wire:model="mode_option_rows.{{ $index }}.label"
                                                type="text" class="w-full rounded-xl border-slate-200 text-sm"
                                                placeholder="e.g. online / home">
                                            <button type="button"
                                                wire:click="removeModeOptionRow({{ $index }})"
                                                class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold text-slate-600">
                                                Remove
                                            </button>
                                        </div>
                                        @error('mode_option_rows.' . $index . '.label')
                                            <p class="text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <h5 class="text-sm font-black text-slate-800">Subject Options</h5>
                                    <button type="button" wire:click="addSubjectOptionRow"
                                        class="rounded-lg bg-cyan-600 px-2.5 py-1 text-xs font-black text-white">Add</button>
                                </div>
                                <div class="space-y-2">
                                    @foreach ($subject_option_rows as $index => $row)
                                        <div class="flex items-start gap-2"
                                            wire:key="subject-option-{{ $index }}">
                                            <input wire:model="subject_option_rows.{{ $index }}.label"
                                                type="text" class="w-full rounded-xl border-slate-200 text-sm"
                                                placeholder="e.g. Mathematics">
                                            <button type="button"
                                                wire:click="removeSubjectOptionRow({{ $index }})"
                                                class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold text-slate-600">
                                                Remove
                                            </button>
                                        </div>
                                        @error('subject_option_rows.' . $index . '.label')
                                            <p class="text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="max-w-sm">
                            <label class="mb-1 block text-xs font-bold text-slate-600">Maximum Selectable
                                Subjects</label>
                            <input wire:model="max_selectable_subjects" type="number" min="1" max="20"
                                class="w-full rounded-xl border-slate-200">
                            @error('max_selectable_subjects')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>
                @endif

                @if ($formStep === 3)
                    <section class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <h5 class="text-sm font-black text-slate-800">Base Price by Frequency (NGN)</h5>
                                <button type="button" wire:click="addFrequencyPricingRow"
                                    class="rounded-lg bg-cyan-600 px-2.5 py-1 text-xs font-black text-white">Add</button>
                            </div>
                            <p class="mb-3 text-xs text-slate-500">Set the core price before multipliers are applied.
                            </p>
                            <div class="space-y-2">
                                @foreach ($pricing_frequency_rows as $index => $pricingRow)
                                    @php
                                        $selectedFrequency = trim((string) ($pricingRow['frequency'] ?? ''));
                                    @endphp
                                    <div class="grid gap-2 md:grid-cols-[1fr,180px,auto]"
                                        wire:key="pricing-frequency-{{ $index }}">
                                        @if ($frequencyChoices->isNotEmpty())
                                            <select wire:model="pricing_frequency_rows.{{ $index }}.frequency"
                                                class="w-full rounded-xl border-slate-200 text-sm">
                                                <option value="">Select frequency</option>
                                                @if ($selectedFrequency !== '' && !$frequencyChoices->contains($selectedFrequency))
                                                    <option value="{{ $selectedFrequency }}">{{ $selectedFrequency }}
                                                    </option>
                                                @endif
                                                @foreach ($frequencyChoices as $choice)
                                                    <option value="{{ $choice }}">{{ $choice }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input wire:model="pricing_frequency_rows.{{ $index }}.frequency"
                                                type="text" class="w-full rounded-xl border-slate-200 text-sm"
                                                placeholder="e.g. 3x weekly">
                                        @endif
                                        <input wire:model="pricing_frequency_rows.{{ $index }}.amount"
                                            type="number" min="0" step="0.01"
                                            class="w-full rounded-xl border-slate-200 text-sm" placeholder="0.00">
                                        <button type="button"
                                            wire:click="removeFrequencyPricingRow({{ $index }})"
                                            class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold text-slate-600">
                                            Remove
                                        </button>
                                    </div>
                                    @error('pricing_frequency_rows.' . $index . '.frequency')
                                        <p class="text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                    @error('pricing_frequency_rows.' . $index . '.amount')
                                        <p class="text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h5 class="text-sm font-black text-slate-800">Duration Multipliers</h5>
                                    <button type="button" wire:click="addDurationPricingRow"
                                        class="rounded-lg bg-cyan-600 px-2.5 py-1 text-xs font-black text-white">Add</button>
                                </div>
                                <p class="mb-3 text-xs text-slate-500">Multiplier of base price (for example 1.75).</p>
                                <div class="space-y-2">
                                    @foreach ($pricing_duration_rows as $index => $pricingRow)
                                        @php
                                            $selectedDuration = trim((string) ($pricingRow['duration'] ?? ''));
                                        @endphp
                                        <div class="grid gap-2 md:grid-cols-[1fr,150px,auto]"
                                            wire:key="pricing-duration-{{ $index }}">
                                            @if ($durationChoices->isNotEmpty())
                                                <select
                                                    wire:model="pricing_duration_rows.{{ $index }}.duration"
                                                    class="w-full rounded-xl border-slate-200 text-sm">
                                                    <option value="">Select duration</option>
                                                    @if ($selectedDuration !== '' && !$durationChoices->contains($selectedDuration))
                                                        <option value="{{ $selectedDuration }}">
                                                            {{ $selectedDuration }}</option>
                                                    @endif
                                                    @foreach ($durationChoices as $choice)
                                                        <option value="{{ $choice }}">{{ $choice }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input wire:model="pricing_duration_rows.{{ $index }}.duration"
                                                    type="text" class="w-full rounded-xl border-slate-200 text-sm"
                                                    placeholder="e.g. 2 hours">
                                            @endif
                                            <input wire:model="pricing_duration_rows.{{ $index }}.multiplier"
                                                type="number" min="0" step="0.01"
                                                class="w-full rounded-xl border-slate-200 text-sm" placeholder="1.00">
                                            <button type="button"
                                                wire:click="removeDurationPricingRow({{ $index }})"
                                                class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold text-slate-600">
                                                Remove
                                            </button>
                                        </div>
                                        @error('pricing_duration_rows.' . $index . '.duration')
                                            <p class="text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                        @error('pricing_duration_rows.' . $index . '.multiplier')
                                            <p class="text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h5 class="text-sm font-black text-slate-800">Mode Multipliers</h5>
                                    <button type="button" wire:click="addModePricingRow"
                                        class="rounded-lg bg-cyan-600 px-2.5 py-1 text-xs font-black text-white">Add</button>
                                </div>
                                <p class="mb-3 text-xs text-slate-500">Multiplier by lesson mode (for example home
                                    1.2).</p>
                                <div class="space-y-2">
                                    @foreach ($pricing_mode_rows as $index => $pricingRow)
                                        @php
                                            $selectedMode = trim((string) ($pricingRow['mode'] ?? ''));
                                        @endphp
                                        <div class="grid gap-2 md:grid-cols-[1fr,150px,auto]"
                                            wire:key="pricing-mode-{{ $index }}">
                                            @if ($modeChoices->isNotEmpty())
                                                <select wire:model="pricing_mode_rows.{{ $index }}.mode"
                                                    class="w-full rounded-xl border-slate-200 text-sm">
                                                    <option value="">Select mode</option>
                                                    @if ($selectedMode !== '' && !$modeChoices->contains($selectedMode))
                                                        <option value="{{ $selectedMode }}">{{ $selectedMode }}
                                                        </option>
                                                    @endif
                                                    @foreach ($modeChoices as $choice)
                                                        <option value="{{ $choice }}">{{ $choice }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input wire:model="pricing_mode_rows.{{ $index }}.mode"
                                                    type="text" class="w-full rounded-xl border-slate-200 text-sm"
                                                    placeholder="e.g. home">
                                            @endif
                                            <input wire:model="pricing_mode_rows.{{ $index }}.multiplier"
                                                type="number" min="0" step="0.01"
                                                class="w-full rounded-xl border-slate-200 text-sm" placeholder="1.00">
                                            <button type="button"
                                                wire:click="removeModePricingRow({{ $index }})"
                                                class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold text-slate-600">
                                                Remove
                                            </button>
                                        </div>
                                        @error('pricing_mode_rows.' . $index . '.mode')
                                            <p class="text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                        @error('pricing_mode_rows.' . $index . '.multiplier')
                                            <p class="text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">Extra Subject
                                    Fraction</label>
                                <input wire:model="pricing_additional_subject_fraction" type="number" min="0"
                                    step="0.01" class="w-full rounded-xl border-slate-200" placeholder="0.35">
                                <p class="mt-1 text-[11px] text-slate-500">Portion added per extra subject.</p>
                                @error('pricing_additional_subject_fraction')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">Base Subject
                                    Allowance</label>
                                <input wire:model="pricing_base_subject_allowance" type="number" min="1"
                                    max="10" class="w-full rounded-xl border-slate-200" placeholder="1">
                                <p class="mt-1 text-[11px] text-slate-500">Subjects included before extra charge.</p>
                                @error('pricing_base_subject_allowance')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">Home Lesson Location
                                    Surcharge (NGN)</label>
                                <input wire:model="pricing_location_surcharge" type="number" min="0"
                                    step="0.01" class="w-full rounded-xl border-slate-200" placeholder="0">
                                <p class="mt-1 text-[11px] text-slate-500">Applied only when lesson mode is home.</p>
                                @error('pricing_location_surcharge')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                @endif

                @if ($formStep === 4)
                    <section class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <h5 class="text-sm font-black text-slate-800">Frequently Asked Questions</h5>
                                <button type="button" wire:click="addFaqRow"
                                    class="rounded-lg bg-cyan-600 px-2.5 py-1 text-xs font-black text-white">Add</button>
                            </div>
                            <div class="space-y-3">
                                @foreach ($faq_rows as $index => $faqRow)
                                    <div class="rounded-xl border border-slate-200 p-3"
                                        wire:key="faq-row-{{ $index }}">
                                        <div class="mb-2 flex justify-end">
                                            <button type="button" wire:click="removeFaqRow({{ $index }})"
                                                class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold text-slate-600">
                                                Remove
                                            </button>
                                        </div>
                                        <label class="mb-1 block text-xs font-bold text-slate-600">Question</label>
                                        <input wire:model="faq_rows.{{ $index }}.q" type="text"
                                            class="w-full rounded-xl border-slate-200 text-sm"
                                            placeholder="Enter FAQ question">
                                        @error('faq_rows.' . $index . '.q')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                        <label class="mb-1 mt-2 block text-xs font-bold text-slate-600">Answer</label>
                                        <textarea wire:model="faq_rows.{{ $index }}.a" rows="2"
                                            class="w-full rounded-xl border-slate-200 text-sm" placeholder="Enter FAQ answer"></textarea>
                                        @error('faq_rows.' . $index . '.a')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">Linked Service Item</label>
                                <select wire:model="service_item_id" class="w-full rounded-xl border-slate-200">
                                    <option value="">Auto-select default</option>
                                    @foreach ($serviceItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('service_item_id')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">Sort Order</label>
                                <input wire:model="sort_order" type="number" min="0"
                                    class="w-full rounded-xl border-slate-200">
                                @error('sort_order')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">Meta Title</label>
                                <input wire:model="meta_title" type="text"
                                    class="w-full rounded-xl border-slate-200">
                                @error('meta_title')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">Meta Description</label>
                                <input wire:model="meta_description" type="text"
                                    class="w-full rounded-xl border-slate-200">
                                @error('meta_description')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">OG Image Path
                                    (optional)</label>
                                <input wire:model="og_image" type="text"
                                    class="w-full rounded-xl border-slate-200" placeholder="images/banner.jpg">
                                @error('og_image')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-600">Hero Image Path</label>
                                <input wire:model="hero_image" type="text"
                                    class="w-full rounded-xl border-slate-200" placeholder="images/banner2.jpg">
                                @error('hero_image')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                            <input type="checkbox" wire:model="is_active"
                                class="rounded border-slate-300 text-cyan-600">
                            Active intervention
                        </label>
                    </section>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 pt-4">
                    <div class="flex gap-2">
                        @if ($formStep > 1)
                            <button type="button" wire:click="previousFormStep"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100">
                                Previous
                            </button>
                        @endif
                        @if ($formStep < 4)
                            <button type="button" wire:click="nextFormStep"
                                class="rounded-xl bg-cyan-600 px-4 py-2 text-xs font-black text-white transition hover:bg-cyan-700">
                                Next
                            </button>
                        @endif
                    </div>

                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-black text-white transition hover:bg-black">
                        {{ $editingId ? 'Update Intervention' : 'Create Intervention' }}
                    </button>
                </div>
            </form>
        @endif

        <div class="overflow-x-auto rounded-2xl border border-slate-100">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-black uppercase text-slate-500">Intervention</th>
                        <th class="px-4 py-3 text-xs font-black uppercase text-slate-500">Slug</th>
                        <th class="px-4 py-3 text-xs font-black uppercase text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-black uppercase text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($programmes as $programme)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $programme->name }}</div>
                                <div class="text-xs text-slate-500">{{ $programme->starting_from_text }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $programme->slug }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-lg px-2 py-1 text-xs font-bold {{ $programme->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $programme->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="space-x-3 px-4 py-3 text-right">
                                <button wire:click="editProgramme({{ $programme->id }})"
                                    class="text-xs font-bold uppercase text-cyan-600">Edit</button>
                                <button wire:click="deleteProgramme({{ $programme->id }})"
                                    wire:confirm="Delete this intervention?"
                                    class="text-xs font-bold uppercase text-rose-500">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No interventions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $programmes->links() }}
    </section>
</div>
