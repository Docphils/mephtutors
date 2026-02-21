<div class="w-full text-gray-700">

    {{-- Heading --}}
    <h1 class="text-white text-3xl font-bold mb-4">MephEd Service Request Form</h1>
    <p class="text-white mb-6">Fill out the form below and we’ll match you with a suitable tutor. You can manage your
        requests and
        messages through your account.</p>

    <div class="shadow-xl rounded-2xl p-6">
        {{-- Progress Bar --}}
        <div class="mb-10">
            <div class="flex items-center justify-between text-sm font-semibold text-cyan-50">
                <div class="{{ $step >= 1 ? 'text-cyan-200' : '' }}">1. Your Details</div>
                <div class="{{ $step >= 2 ? 'text-cyan-200' : '' }}">2. Service</div>
                <div class="{{ $step >= 3 ? 'text-cyan-200' : '' }}">3. Schedule</div>
                <div class="{{ $step >= 4 ? 'text-cyan-200' : '' }}">4. Done</div>
            </div>

            <div class="w-full bg-gray-200 h-2 rounded-full mt-2">
                <div class="bg-cyan-500 h-2 rounded-full transition-all duration-500"
                    style="width: {{ $step * 25 }}%">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">

            {{-- STEP 1 --}}
            @if ($step === 1)
                <h2 class="text-2xl font-bold mb-6">Tell us about you</h2>

                <div class="grid md:grid-cols-2 gap-6">

                    <div>
                        <label class="block mb-2 font-semibold">Full Name</label>
                        <input type="text" wire:model="fullname" class="w-full border rounded-xl px-4 py-3">
                        @error('fullname')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold">Email</label>
                        <input type="email" wire:model="email" class="w-full border rounded-xl px-4 py-3">
                        @error('email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @if ($existingUser)
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                <p class="text-sm text-yellow-800">An account already exists for this email.</p>
                                <div class="mt-2 flex items-center gap-3">
                                    <a href="{{ route('login') }}" class="text-cyan-600 underline">Sign in</a>
                                    <button type="button" wire:click="$set('email', '')"
                                        class="text-sm text-gray-600">Use different email</button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold">Phone</label>
                        <input type="text" wire:model="phone" class="w-full border rounded-xl px-4 py-3">
                        @error('phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold">Gender</label>
                        <select wire:model="gender" class="w-full border rounded-xl px-4 py-3">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        @error('gender')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($is_crm)
                        <div class="md:col-span-2">
                            <label class="block mb-2 font-semibold">Institution Name</label>
                            <input type="text" wire:model="institution_name"
                                class="w-full border rounded-xl px-4 py-3">
                            @error('institution_name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-2 font-semibold">Institution Address</label>
                            <input type="text" wire:model="address" class="w-full border rounded-xl px-4 py-3"
                                placeholder="Street, Area">
                            @error('address')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        {{-- New Learner Selection --}}
                        <div class="md:col-span-2 border-t pt-4">
                            <label class="block mb-2 font-semibold">Who is this service for?</label>
                            <div class="flex gap-6 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="is_for_self" value="1"
                                        class="w-4 h-4 text-cyan-600">
                                    <span>Myself</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="is_for_self" value="0"
                                        class="w-4 h-4 text-cyan-600">
                                    <span>Someone else (Learner)</span>
                                </label>
                            </div>

                            @if (!$is_for_self)
                                <div class="mt-4 p-4 bg-gray-50 rounded-xl">
                                    <label class="block mb-2 font-semibold text-sm">Learner Names</label>
                                    @foreach ($learners as $index => $learner)
                                        <div class="flex gap-2 mb-2">
                                            <input type="text" wire:model="learners.{{ $index }}.name"
                                                class="flex-1 border rounded-xl px-4 py-2 text-sm"
                                                placeholder="Enter learner name">
                                            @if (count($learners) > 1)
                                                <button type="button" wire:click="removeLearner({{ $index }})"
                                                    class="text-red-500 px-2">
                                                    ✕
                                                </button>
                                            @endif
                                        </div>
                                        @error('learners.' . $index . '.name')
                                            <p class="text-red-600 text-xs mb-2">{{ $message }}</p>
                                        @enderror
                                    @endforeach
                                    <button type="button" wire:click="addLearner"
                                        class="text-cyan-600 text-sm font-semibold mt-2">
                                        + Add another learner
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                <div class="mt-8 text-right">
                    <button @if ($existingUser) disabled @endif wire:click="next"
                        class="bg-cyan-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-cyan-700 disabled:opacity-50"
                        @if ($existingUser) title="Sign in to continue" @endif>
                        Continue →
                    </button>
                    @if ($existingUser)
                        <p class="text-sm text-red-600 mt-2">An account exists for this email — please <a
                                href="{{ route('login') }}" class="underline">sign in</a>.</p>
                    @endif
                </div>
            @endif


            {{-- STEP 2 --}}
            @if ($step === 2)
                <h2 class="text-2xl font-bold mb-6">What do you need?</h2>

                <div class="space-y-6">

                    <div>
                        <label class="block mb-2 font-semibold">Service Category</label>
                        <select wire:model.live="service_id" class="w-full border rounded-xl px-4 py-3">
                            <option value="">Select Service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($serviceItems)
                        <div>
                            <label class="block mb-2 font-semibold">Specific Option</label>
                            <select wire:model.live="service_item_id" class="w-full border rounded-xl px-4 py-3">
                                <option value="">Select Option</option>
                                @foreach ($serviceItems as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_item_id')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if ($requires_level)
                        <div>
                            <label class="block mb-2 font-semibold">Level</label>
                            <select wire:model="level_id" class="w-full border rounded-xl px-4 py-3">
                                <option value="">Select Level</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('level_id')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if ($requires_exam_type)
                        <div>
                            <label class="block mb-2 font-semibold">Exam Type</label>
                            <select wire:model="exam_type_id" class="w-full border rounded-xl px-4 py-3">
                                <option value="">Select Exam</option>
                                @foreach ($examTypes as $exam)
                                    <option value="{{ $exam->id }}">
                                        {{ $exam->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('exam_type_id')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- New Curriculum Field --}}
                    @if (!$is_crm)
                        <div>
                            <label class="block mb-2 font-semibold">Curriculum</label>
                            <select wire:model="curriculum" class="w-full border rounded-xl px-4 py-3">
                                <option value="N/A">N/A (Not Applicable)</option>
                                <option value="British">British</option>
                                <option value="French">French</option>
                                <option value="Nigerian">Nigerian</option>
                                <option value="Blended">Blended</option>
                            </select>
                            @error('curriculum')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                </div>

                <div class="mt-8 flex justify-between">
                    <button wire:click="back" class="bg-gray-200 hover:bg-gray-100 px-6 py-3 rounded-xl border">
                        ← Back
                    </button>

                    <button wire:click="next" class="bg-cyan-600 text-white px-6 py-3 rounded-xl font-semibold">
                        Continue →
                    </button>
                </div>
            @endif


            {{-- STEP 3 --}}
            @if ($step === 3)
                <h2 class="text-2xl font-bold mb-6">Schedule & Format</h2>

                <div class="space-y-6">

                    <div>
                        <label class="block mb-2 font-semibold">Delivery Mode</label>
                        <select wire:model.live="delivery_mode" class="w-full border rounded-xl px-4 py-3">
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    {{-- If CRM request: show institution-specific fields --}}
                    @if ($is_crm)
                        {{-- Address fields when physical attendance requested --}}
                        @if (in_array($delivery_mode, ['offline', 'hybrid']))
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block mb-2 font-semibold">State</label>
                                    <select wire:model="state" class="w-full border rounded-xl px-4 py-3">
                                        <option value="">Select State</option>
                                        @foreach ($states as $st)
                                            <option value="{{ $st }}">{{ $st }}</option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block mb-2 font-semibold">City</label>
                                    <input type="text" wire:model="city" placeholder="City"
                                        class="w-full border rounded-xl px-4 py-3">
                                    @error('city')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 font-semibold">Full Address</label>
                                <textarea wire:model="address" placeholder="Full Address" class="w-full border rounded-xl px-4 py-3"></textarea>
                                @error('address')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block mb-2 font-semibold">Number of tutors required</label>
                                <input type="number" wire:model="number_of_tutors" min="1"
                                    class="w-full border rounded-xl px-4 py-3">
                                @error('number_of_tutors')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-2 font-semibold">Engagement Type</label>
                                <select wire:model="engagement_type" class="w-full border rounded-xl px-4 py-3">
                                    <option value="">Select engagement type</option>
                                    <option value="short_term">Short term</option>
                                    <option value="long_term">Long term</option>
                                    <option value="contract">Contract</option>
                                    <option value="club_management">Club management</option>
                                </select>
                                @error('engagement_type')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 font-semibold">Requirements / Notes</label>
                            <textarea wire:model="requirements" placeholder="Describe your requirements"
                                class="w-full border rounded-xl px-4 py-3"></textarea>
                            @error('requirements')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        {{-- Individual/tutor request fields --}}
                        @if (in_array($delivery_mode, ['offline', 'hybrid']))
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block mb-2 font-semibold">State</label>
                                    <select wire:model="state" class="w-full border rounded-xl px-4 py-3">
                                        <option value="">Select State</option>
                                        @foreach ($states as $st)
                                            <option value="{{ $st }}">{{ $st }}</option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block mb-2 font-semibold">City</label>
                                    <input type="text" wire:model="city" placeholder="City"
                                        class="w-full border rounded-xl px-4 py-3">
                                    @error('city')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block mb-2 font-semibold">Full Address</label>
                                <textarea wire:model="address" placeholder="Full Address" class="w-full border rounded-xl px-4 py-3"></textarea>
                                @error('address')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block mb-2 font-semibold">Session Type</label>
                                <select wire:model="session_type" class="w-full border rounded-xl px-4 py-3">
                                    <option value="individual">Individual</option>
                                    <option value="group">Group</option>
                                </select>
                                @error('session_type')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-2 font-semibold">Sessions per week</label>
                                <input type="number" wire:model="sessions_per_week" placeholder="Sessions per week"
                                    class="w-full border rounded-xl px-4 py-3">
                                @error('sessions_per_week')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block mb-2 font-semibold">Duration per session (minutes)</label>
                                <input type="number" wire:model="duration_per_session"
                                    placeholder="Duration per session (minutes)"
                                    class="w-full border rounded-xl px-4 py-3">
                                @error('duration_per_session')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-2 font-semibold">Preferred Tutor Gender</label>
                                <select wire:model="preferred_tutor_gender"
                                    class="w-full border rounded-xl px-4 py-3">
                                    <option value="any">Any</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                @error('preferred_tutor_gender')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 font-semibold">Additional Notes</label>
                            <textarea wire:model="additional_notes" placeholder="Any other specific requirements?"
                                class="w-full border rounded-xl px-4 py-3"></textarea>
                        </div>
                    @endif

                </div>

                <div class="mt-8 flex justify-between">
                    <button wire:click="back" class="bg-gray-200 hover:bg-gray-100 px-6 py-3 rounded-xl border">
                        ← Back
                    </button>

                    <button wire:click="submit" class="bg-cyan-600 text-white px-6 py-3 rounded-xl font-semibold">
                        Submit Request
                    </button>
                </div>
            @endif

            {{-- STEP 4: SUCCESS --}}
            @if ($step === 4)
                <div class="text-center py-10">
                    <div class="text-6xl mb-4">🎉</div>
                    <h2 class="text-3xl font-bold mb-4">Request Submitted!</h2>
                    <p class="text-gray-600 mb-8">Thank you for your request. We have sent an acknowledgement email
                        with instructions on how to track your request.</p>
                    <a href="/" class="bg-cyan-600 text-white px-8 py-3 rounded-xl font-semibold">Return
                        Home</a>
                </div>
            @endif

        </div>
    </div>
</div>
