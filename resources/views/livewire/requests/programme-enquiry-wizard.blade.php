<div class="space-y-6">
    @if ($submitted)
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 md:p-8">
            <h3 class="text-2xl font-black text-emerald-900">Request Submitted Successfully</h3>
            <p class="text-emerald-800 mt-2">
                Your intervention request has been submitted successfully.
                Your request has been received and will be reviewed by our team. A final price will be provided after
                admin review.
                @if ($this->isAuthenticatedUser)
                    Complete payment from your client dashboard.
                @else
                    Check your email for account access details, then complete payment from your client dashboard after
                    login.
                @endif
            </p>

            <div class="mt-5 flex flex-wrap gap-3">
                @if ($this->isAuthenticatedUser)
                    <a wire:navigate href="{{ route('client.programmeRequests.manager') }}"
                        class="px-5 py-3 rounded-xl bg-emerald-700 text-white font-black hover:bg-emerald-800 transition">
                        Open Intervention Requests
                    </a>
                @else
                    <a wire:navigate href="{{ route('login') }}"
                        class="px-5 py-3 rounded-xl bg-emerald-700 text-white font-black hover:bg-emerald-800 transition">
                        Login
                    </a>
                @endif
                <a wire:navigate href="{{ route('welcome') }}"
                    class="px-5 py-3 rounded-xl border border-emerald-300 text-emerald-800 bg-white font-bold hover:bg-emerald-100 transition">
                    Back to Homepage
                </a>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap gap-2 text-xs font-bold">
                @for ($i = 1; $i <= $this->reviewStepNumber; $i++)
                    <span
                        class="px-3 py-1 rounded-full {{ $step === $i ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-500' }}">Step
                        {{ $i }}</span>
                @endfor
            </div>
            <div class="mt-3 text-sm text-slate-600">
                Intervention: <span class="font-black text-slate-900">{{ $programme->name }}</span>
            </div>
        </div>

        @if ($step === 1)
            <section class="rounded-3xl border border-slate-200 bg-white p-5 md:p-6 space-y-3 max-w-5xl mx-auto">
                <h3 class="text-xl font-black text-slate-900">1. Learner Snapshot</h3>
                <div class="grid md:grid-cols-2 gap-3">
                    @if (!$this->isAuthenticatedUser)
                        <div class="md:col-span-2">
                            <label class="text-xs font-bold text-slate-600">Account Email</label>
                            <input type="email" wire:model.live.debounce.350ms="account_email"
                                class="mt-1 w-full rounded-xl border-slate-300" placeholder="name@example.com">
                            @error('account_email')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                            @if ($this->existingAccountDetected)
                                <div
                                    class="mt-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                                    <p>An account already exists for this email. Login to continue this request.</p>
                                    <a wire:navigate href="{{ route('login') }}"
                                        class="mt-2 inline-flex items-center px-4 py-2 rounded-lg bg-amber-600 text-white font-bold hover:bg-amber-700 transition">
                                        Login
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div>
                        <label class="text-xs font-bold text-slate-600">Learner Name</label>
                        <input type="text" wire:model="learner_name" class="mt-1 w-full rounded-xl border-slate-300">
                        @error('learner_name')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @if ($this->isExamSpecificProgramme)
                        <div>
                            <label class="text-xs font-bold text-slate-600">School Name (optional)</label>
                            <input type="text" wire:model="school_name"
                                class="mt-1 w-full rounded-xl border-slate-300">
                        </div>
                    @else
                        <div>
                            <label class="text-xs font-bold text-slate-600">Class Level</label>
                            <input type="text" wire:model="class_level"
                                class="mt-1 w-full rounded-xl border-slate-300" placeholder="JSS3, SS1, SS2...">
                            @error('class_level')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs font-bold text-slate-600">School Name (optional)</label>
                            <input type="text" wire:model="school_name"
                                class="mt-1 w-full rounded-xl border-slate-300">
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($step === 2)
            <section class="rounded-3xl border border-slate-200 bg-white p-5 md:p-6 space-y-4 max-w-5xl mx-auto">
                <h3 class="text-xl font-black text-slate-900">2. Subject Selection</h3>
                <p class="text-sm text-slate-600">
                    Select up to <strong>{{ $this->maxSelectableSubjects }}</strong> subjects relevant to this
                    intervention.
                </p>
                @error('subjects')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach ($this->subjectOptions as $subject)
                        <label
                            class="flex items-center gap-2 p-2.5 rounded-xl border text-sm cursor-pointer
                            {{ in_array($subject, $subjects, true) ? 'border-cyan-500 bg-cyan-50 text-cyan-800' : 'border-slate-200 bg-white text-slate-700 hover:border-cyan-200' }}">
                            <input type="checkbox" @checked(in_array($subject, $subjects, true))
                                wire:click="toggleSubject('{{ addslashes($subject) }}')"
                                class="rounded border-slate-300 text-cyan-600">
                            <span>{{ $subject }}</span>
                        </label>
                    @endforeach
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-600">Weak Areas / Concerns (optional)</label>
                    <textarea wire:model="weak_areas" rows="3" class="mt-1 w-full rounded-xl border-slate-300"></textarea>
                </div>
            </section>
        @endif

        @if ($step === 3)
            <section class="rounded-3xl border border-slate-200 bg-white p-5 md:p-6 space-y-4 max-w-5xl mx-auto">
                <h3 class="text-xl font-black text-slate-900">3. Lesson Plan &amp; Schedule</h3>
                <p class="text-sm text-slate-600">
                    Select exactly <strong>{{ $this->frequencyCount }}</strong> day(s) to match your chosen frequency.
                </p>
                <div class="grid md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-600">Lesson Mode</label>
                        <select wire:model.live="lesson_mode" class="mt-1 w-full rounded-xl border-slate-300">
                            @foreach ($this->modeOptions as $option)
                                @php
                                    $normalizedModeLabel = strtolower((string) $option);
                                    $modeVal =
                                        str_contains($normalizedModeLabel, 'home') ||
                                        str_contains($normalizedModeLabel, 'physical') ||
                                        str_contains($normalizedModeLabel, 'in-person') ||
                                        str_contains($normalizedModeLabel, 'in person') ||
                                        str_contains($normalizedModeLabel, 'offline')
                                            ? 'home'
                                            : 'online';
                                @endphp
                                <option value="{{ $modeVal }}">{{ ucfirst($option) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-600">Frequency</label>
                        <select wire:model.live="preferred_frequency" class="mt-1 w-full rounded-xl border-slate-300">
                            @foreach ($this->frequencyOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-600">Session Duration</label>
                        <select wire:model="preferred_duration" class="mt-1 w-full rounded-xl border-slate-300">
                            @foreach ($this->durationOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-600 mb-2">Preferred Days</p>
                    <p class="text-xs text-slate-500 mb-2">
                        Selected {{ count($preferred_days) }} / {{ $this->frequencyCount }} day(s)
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach ($dayOptions as $day)
                            @php
                                $isSelected = in_array($day, $preferred_days, true);
                                $isLimitReached = count($preferred_days) >= $this->frequencyCount;
                            @endphp
                            <label class="flex items-center gap-2 text-sm p-2 rounded-xl border border-slate-200">
                                <input type="checkbox" @checked($isSelected)
                                    wire:click="togglePreferredDay('{{ $day }}')" @disabled(!$isSelected && $isLimitReached)
                                    class="rounded border-slate-300 text-cyan-600">
                                <span>{{ $day }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('preferred_days')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-600 mb-2">Preferred Time</p>
                    <input type="time" wire:model.live="preferred_time"
                        class="w-full md:w-60 rounded-xl border-slate-300">
                    @error('preferred_time')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if ($this->isHomeLessonMode)
                    <div class="space-y-3">
                        @if ($this->canUseExistingAddress)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 space-y-2">
                                <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                                    <input type="checkbox" wire:model.live="use_existing_address"
                                        class="rounded border-slate-300 text-cyan-600">
                                    Use Existing Address
                                </label>

                                @if ($this->use_existing_address)
                                    <div>
                                        <label class="text-xs font-bold text-slate-600">Saved Addresses</label>
                                        <select wire:model.live="selected_existing_address"
                                            class="mt-1 w-full rounded-xl border-slate-300">
                                            <option value="">Select a saved address</option>
                                            @foreach ($existing_addresses as $savedAddress)
                                                <option value="{{ $savedAddress['id'] }}">
                                                    {{ $savedAddress['label'] }} ({{ $savedAddress['source'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selected_existing_address')
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    @if ($this->selectedExistingAddressOption)
                                        <div
                                            class="rounded-xl border border-cyan-200 bg-cyan-50 p-3 text-sm text-cyan-900">
                                            <p class="font-bold">Selected Address</p>
                                            <p>{{ $this->selectedExistingAddressOption['label'] }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif

                        @if (!$this->canUseExistingAddress || !$this->use_existing_address)
                            <div class="grid md:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-600">State</label>
                                    <select wire:model="state" class="mt-1 w-full rounded-xl border-slate-300">
                                        <option value="">Select State</option>
                                        @foreach ($stateOptions as $stateName)
                                            <option value="{{ $stateName }}">{{ $stateName }}</option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-slate-600">City/Area</label>
                                    <input type="text" wire:model="city_area"
                                        class="mt-1 w-full rounded-xl border-slate-300">
                                    @error('city_area')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs font-bold text-slate-600">Address</label>
                                    <textarea wire:model="address" rows="2" class="mt-1 w-full rounded-xl border-slate-300"></textarea>
                                    @error('address')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        @endif

        @if ($this->needsContactStep && $step === 4)
            <section class="rounded-3xl border border-slate-200 bg-white p-5 md:p-6 space-y-3 max-w-5xl mx-auto">
                <h3 class="text-xl font-black text-slate-900">4. Contact Details</h3>
                @if ($this->isAuthenticatedUser)
                    <div class="rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm text-cyan-800">
                        We loaded details from your account. Please provide only missing fields.
                    </div>
                @endif
                <div class="grid md:grid-cols-2 gap-3">
                    @if ($this->isAuthenticatedUser)
                        <div>
                            <label class="text-xs font-bold text-slate-600">Account Name</label>
                            <input type="text" value="{{ $parent_name }}" readonly
                                class="mt-1 w-full rounded-xl border-slate-300 bg-slate-100 text-slate-500">
                        </div>
                    @else
                        <div>
                            <label class="text-xs font-bold text-slate-600">Parent/Guardian Name</label>
                            <input type="text" wire:model="parent_name"
                                class="mt-1 w-full rounded-xl border-slate-300">
                            @error('parent_name')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    <div>
                        <label class="text-xs font-bold text-slate-600">Phone
                            {{ $this->needsPhoneCapture ? '(required)' : '' }}</label>
                        <input type="text" wire:model="parent_phone"
                            class="mt-1 w-full rounded-xl border-slate-300">
                        @error('parent_phone')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-600">Account Email</label>
                        <input type="email" value="{{ $account_email }}" readonly
                            class="mt-1 w-full rounded-xl border-slate-300 bg-slate-100 text-slate-500">
                    </div>
                </div>
            </section>
        @endif

        @if ($step === $this->reviewStepNumber)
            <section class="rounded-3xl border border-slate-200 bg-white p-5 md:p-6 space-y-3 max-w-5xl mx-auto">
                <h3 class="text-xl font-black text-slate-900">{{ $this->reviewStepNumber }}. Quote Review</h3>
                <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 text-sm space-y-2">
                    <p><strong>Intervention:</strong> {{ $programme->name }}</p>
                    <p><strong>Subjects Selected:</strong> {{ count($subjects) }}</p>
                    <p><strong>Frequency:</strong> {{ $preferred_frequency }}</p>
                    <p><strong>Duration:</strong> {{ $preferred_duration }}</p>
                    <p><strong>Mode:</strong> {{ $this->isHomeLessonMode ? 'Home' : 'Online' }}</p>
                    <p><strong>Parent Contact:</strong> {{ $parent_name }} | {{ $parent_phone }} |
                        {{ $account_email }}</p>
                    <p><strong>Estimated Total:</strong> <em>Will be provided after admin review</em></p>
                </div>
                <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
                    class="px-5 py-3 rounded-xl bg-cyan-600 text-white font-black hover:bg-cyan-700 transition">
                    Submit Intervention Request
                </button>
                <span wire:loading wire:target="submit" class="text-sm text-cyan-700 font-bold">
                    Submitting request{{ $this->isAuthenticatedUser ? '...' : ' and creating account...' }}
                </span>
            </section>
        @endif

        <div class="flex justify-between p-2">
            <button type="button" wire:click="back" @disabled($step === 1)
                class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold disabled:opacity-50">
                Back
            </button>
            @if ($step < $this->reviewStepNumber)
                <button type="button" wire:click="next" wire:loading.class="hidden" wire:target="next"
                    @disabled(
                        ($step === 3 && count($preferred_days) !== $this->frequencyCount) ||
                            ($step === 1 && !$this->isAuthenticatedUser && $this->existingAccountDetected))
                    class="px-4 py-2 rounded-xl bg-cyan-600 text-white text-sm font-bold hover:bg-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Continue
                </button>
            @endif
            <span wire:loading wire:target="next" class="text-sm text-cyan-700 font-bold">Validating...</span>
        </div>
        @if ($step === 3 && count($preferred_days) !== $this->frequencyCount)
            <p class="text-xs text-rose-600">
                Choose exactly {{ $this->frequencyCount }} day(s) before continuing.
            </p>
        @endif
    @endif
</div>
