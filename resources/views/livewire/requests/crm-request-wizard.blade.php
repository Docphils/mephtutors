<div class="w-full text-slate-800 p-4 sm:p-6 lg:p-10">

    {{-- Heading --}}
    <h1 class="text-2xl sm:text-3xl font-bold mb-4">School Staffing Solutions Form</h1>
    <p class=" mb-6">Fill out the form below and we’ll assign an instructor to manage your club or
        activity. You can track requests and communications through your account.</p>

    <div class="shadow-xl rounded-2xl p-4 sm:p-6">

        {{-- Progress Bar --}}
        <div class="mb-10">
            <div class="flex flex-wrap items-center gap-2 sm:gap-4 justify-between text-xs sm:text-sm font-semibold text-cyan-500">
                <div class="{{ $step >= 1 ? 'text-cyan-800' : '' }}">1. Your Details</div>
                <div class="{{ $step >= 2 ? 'text-cyan-800' : '' }}">2. Service</div>
                <div class="{{ $step >= 3 ? 'text-cyan-800' : '' }}">3. Schedule</div>
                <div class="{{ $step >= 4 ? 'text-cyan-800' : '' }}">4. Done</div>
            </div>

            <div class="w-full bg-gray-200 h-2 rounded-full mt-2">
                <div class="bg-cyan-500 h-2 rounded-full transition-all duration-500"
                    style="width: {{ $step * 25 }}%">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8">

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
                                    <a wire:navigate href="{{ route('login') }}" class="text-cyan-600 underline">Sign in</a>
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
                </div>

                <div class="mt-8 text-right">
                    <button @if ($existingUser) disabled @endif wire:click="next"
                        class="w-full sm:w-auto bg-cyan-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-cyan-700 disabled:opacity-50"
                        @if ($existingUser) title="Sign in to continue" @endif>
                        Continue →
                    </button>
                </div>
            @endif

            {{-- STEP 2 --}}
            @if ($step === 2)
                <h2 class="text-2xl font-bold mb-6">Institution & Service Details</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 font-semibold">Institution Name</label>
                        <input type="text" wire:model="institution_name" class="w-full border rounded-xl px-4 py-3">
                        @error('institution_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Institution Address</label>
                        <input type="text" wire:model="institution_address"
                            class="w-full border rounded-xl px-4 py-3">
                        @error('institution_address')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Number of Tutors</label>
                        <input type="number" wire:model="number_of_tutors" min="1"
                            class="w-full border rounded-xl px-4 py-3">
                        @error('number_of_tutors')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Engagement Type</label>
                        <select wire:model="engagement_type" class="w-full border rounded-xl px-4 py-3">
                            <option value="">Select Type</option>
                            @foreach ($allowedEngagementTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('engagement_type')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Service Category</label>
                        <select wire:model.live="service_id" class="w-full border rounded-xl px-4 py-3">
                            <option value="">Select Service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
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
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('service_item_id')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- Conditional Missing Fields --}}
                    @if ($needsLevel)
                        <div>
                            <label class="block mb-2 font-semibold">Academic Level</label>
                            <input type="text" wire:model="level" class="w-full border rounded-xl px-4 py-3"
                                placeholder="e.g. Primary, Secondary">
                            @error('level')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if ($needsCurriculum)
                        <div>
                            <label class="block mb-2 font-semibold">Curriculum</label>
                            <input type="text" wire:model="curriculum" class="w-full border rounded-xl px-4 py-3"
                                placeholder="e.g. British, Nigerian">
                            @error('curriculum')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if ($needsExamType)
                        <div>
                            <label class="block mb-2 font-semibold">Exam Type</label>
                            <input type="text" wire:model="exam_type" class="w-full border rounded-xl px-4 py-3"
                                placeholder="e.g. WAEC, IGCSE">
                            @error('exam_type')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label class="block mb-2 font-semibold">Additional Notes</label>
                        <textarea wire:model="requirements" class="w-full border rounded-xl px-4 py-3"
                            placeholder="Any specific requirements?"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3 sm:gap-0 justify-between">
                    <button wire:click="back" class="w-full sm:w-auto bg-gray-200 hover:bg-gray-100 px-6 py-3 rounded-xl border">←
                        Back</button>
                    <button wire:click="next"
                        class="w-full sm:w-auto bg-cyan-600 text-white px-6 py-3 rounded-xl font-semibold">Continue
                        →</button>
                </div>
            @endif

            {{-- STEP 3 --}}
            @if ($step === 3)
                <h2 class="text-2xl font-bold mb-6">Schedule</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 font-semibold">Delivery Mode</label>
                        <select wire:model="delivery_mode" class="w-full border rounded-xl px-4 py-3">
                            <option value="online">Online</option>
                            <option value="offline">Offline (Onsite)</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold">Sessions per Week</label>
                        <input type="number" wire:model="sessions_per_week"
                            class="w-full border rounded-xl px-4 py-3">
                        @error('sessions_per_week')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-8 flex flex-col-reverse sm:flex-row gap-3 sm:gap-0 justify-between">
                    <button wire:click="back" class="w-full sm:w-auto bg-gray-200 hover:bg-gray-100 px-6 py-3 rounded-xl border"
                        wire:target='submit' wire:loading.attr='disabled'>←
                        Back</button>
                    <button wire:click="submit" class="w-full sm:w-auto bg-cyan-600 text-white px-6 py-3 rounded-xl font-semibold"
                        wire:target='submit' wire:loading.class='hidden'>Submit Request</button>
                    <p wire:loading wire:target='submit'
                        class="w-full sm:w-auto bg-cyan-600 text-white px-6 py-3 rounded-xl font-semibold flex items-center justify-center sm:justify-start gap-2">
                        <i class="fas fa-spinner animate-spin"></i>
                        <span>Submitting...</span>
                    </p>
                </div>
            @endif

            {{-- STEP 4 --}}
            @if ($step === 4)
                <div class="text-center py-10">
                    <div class="text-6xl mb-4">🎉</div>
                    <h2 class="text-3xl font-bold mb-4">Request Submitted!</h2>
                    <p class="text-gray-600 mb-8">Thank you for your request. An acknowledgement email has been sent.
                        You can track your request from your account.</p>
                    <a href="/" class="inline-block w-full sm:w-auto bg-cyan-600 text-white px-8 py-3 rounded-xl font-semibold">Return
                        Home</a>
                </div>
            @endif

        </div>
    </div>
</div>
