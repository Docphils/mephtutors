<div class="w-full text-gray-700">
    <div class="mb-8">
        <h1 class="text-white text-3xl font-bold mb-2">Find Your Perfect Tutor</h1>
        <p class="text-cyan-100/80">Complete the form below and we'll match you with an expert.</p>
    </div>

    <div class="shadow-xl rounded-2xl overflow-hidden bg-white">
        {{-- Progress Bar --}}
        <div class="bg-cyan-50 px-8 py-4 border-b flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= 1 ? 'bg-cyan-600 text-white' : 'bg-gray-200 text-gray-500' }} font-bold text-sm">1</span>
                <span
                    class="hidden md:block text-sm font-semibold {{ $step >= 1 ? 'text-cyan-900' : 'text-gray-400' }}">Identity</span>
            </div>
            <div class="h-px bg-cyan-200 flex-1 mx-4"></div>
            <div class="flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= 2 ? 'bg-cyan-600 text-white' : 'bg-cyan-200 text-gray-500' }} font-bold text-sm">2</span>
                <span
                    class="hidden md:block text-sm font-semibold {{ $step >= 2 ? 'text-cyan-900' : 'text-gray-400' }}">Service</span>
            </div>
            <div class="h-px bg-cyan-200 flex-1 mx-4"></div>
            <div class="flex items-center gap-4">
                <span
                    class="flex items-center justify-center w-8 h-8 rounded-full {{ $step >= 3 ? 'bg-cyan-600 text-white' : 'bg-cyan-200 text-gray-500' }} font-bold text-sm">3</span>
                <span
                    class="hidden md:block text-sm font-semibold {{ $step >= 3 ? 'text-cyan-900' : 'text-gray-400' }}">Logistics</span>
            </div>
        </div>

        <div class="p-8">
            {{-- STEP 1: IDENTITY --}}
            @if ($step === 1)
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Basic Information</h2>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Full Name</label>
                        <input type="text" wire:model="fullname"
                            class="w-full border-gray-300 rounded-lg px-4 py-3 focus:ring-cyan-500 focus:border-cyan-500">
                        @error('fullname')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Email Address</label>
                        <input type="email" wire:model.live.debounce.400ms="email"
                            class="w-full border-gray-300 rounded-lg px-4 py-3 {{ $existingUser ? 'border-red-500 bg-red-50' : '' }}">
                        @error('email')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                        @if ($existingUser)
                            <div class="mt-1 text-xs text-red-600 font-bold">Account exists! <a
                                    href="{{ route('login') }}" class="underline">Login here</a> or use another email.
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Phone Number</label>
                        <input type="text" wire:model="phone" class="w-full border-gray-300 rounded-lg px-4 py-3">
                        @error('phone')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Your Gender</label>
                        <select wire:model="gender" class="w-full border-gray-300 rounded-lg px-4 py-3">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        @error('gender')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2 mt-4 p-6 bg-cyan-50 rounded-xl">
                        <label class="block mb-4 font-bold text-gray-800">Who are the lessons for?</label>
                        <div class="flex gap-6 mb-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="is_for_self" value="1"
                                    class="text-cyan-600">
                                <span class="font-medium">Myself</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="is_for_self" value="0"
                                    class="text-cyan-600">
                                <span class="font-medium">Someone Else (Learners)</span>
                            </label>
                        </div>

                        @if (!$is_for_self)
                            <div class="space-y-3">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Learner Names</label>
                                @foreach ($learners as $index => $learner)
                                    <div class="flex gap-2">
                                        <input type="text" wire:model="learners.{{ $index }}.name"
                                            class="flex-1 border-gray-300 rounded-lg px-4 py-2 text-sm"
                                            placeholder="Learner Full Name">
                                        @if (count($learners) > 1)
                                            <button type="button" wire:click="removeLearner({{ $index }})"
                                                class="text-red-500 px-2">✕</button>
                                        @endif
                                    </div>
                                    @error("learners.$index.name")
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                @endforeach
                                <button type="button" wire:click="addLearner"
                                    class="text-cyan-600 text-xs font-bold uppercase mt-2">+ Add Learner</button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button wire:click="next"
                        class="bg-cyan-600 hover:bg-cyan-700 text-white px-10 py-3 rounded-lg font-bold transition-all shadow-lg disabled:opacity-50"
                        {{ $existingUser ? 'disabled' : '' }}>
                        Next Step: Service Details
                    </button>
                </div>
            @endif

            {{-- STEP 2: SERVICE --}}
            @if ($step === 2)
                <div class="space-y-6">
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-2">Academic Selection</h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Service Category</label>
                            <select wire:model.live="service_id" class="w-full border-gray-300 rounded-lg px-4 py-3">
                                <option value="">Select Service</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Program/Item</label>
                            <select wire:model.live="service_item_id"
                                class="w-full border-gray-300 rounded-lg px-4 py-3">
                                <option value="">Select Option</option>
                                @foreach ($serviceItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('service_item_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        @if ($requires_level)
                            <div>
                                <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Level</label>
                                <select wire:model="level_id" class="w-full border-gray-300 rounded-lg px-4 py-3">
                                    <option value="">Select Level</option>
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                    @endforeach
                                </select>
                                @error('level_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        @if ($requires_curriculum)
                            <div>
                                <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Curriculum</label>
                                <select wire:model="curriculum" class="w-full border-gray-300 rounded-lg px-4 py-3">
                                    <option value="Nigerian">Nigerian</option>
                                    <option value="British">British</option>
                                    <option value="French">French</option>
                                    <option value="Blended">Blended</option>
                                    <option value="N/A">N/A</option>
                                </select>
                                @error('curriculum')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        @if ($requires_exam_type)
                            <div>
                                <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Exam Type</label>
                                <select wire:model="exam_type_id" class="w-full border-gray-300 rounded-lg px-4 py-3">
                                    <option value="">Select Exam</option>
                                    @foreach ($examTypes as $exam)
                                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                    @endforeach
                                </select>
                                @error('exam_type_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>

                    @if ($has_subjects)
                        <div class="mt-4 p-6 bg-cyan-50 border border-cyan-100 rounded-xl">
                            <label
                                class="block mb-3 font-bold text-cyan-800 uppercase text-xs tracking-wider">Subjects/Courses
                                to be Taught</label>
                            <div class="grid md:grid-cols-2 gap-3">
                                @foreach ($courses as $index => $course)
                                    <div class="flex gap-2">
                                        <input type="text" wire:model="courses.{{ $index }}.name"
                                            class="flex-1 border-gray-300 rounded-lg px-4 py-2 text-sm"
                                            placeholder="e.g. Mathematics">
                                        @if (count($courses) > 1)
                                            <button type="button" wire:click="removeCourse({{ $index }})"
                                                class="text-red-500">✕</button>
                                        @endif
                                    </div>
                                    @error("courses.$index.name")
                                        <span class="text-red-500 text-xs block w-full">{{ $message }}</span>
                                    @enderror
                                @endforeach
                            </div>
                            <button type="button" wire:click="addCourse"
                                class="text-cyan-700 text-xs font-bold uppercase mt-4">+ Add Another Subject</button>
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex justify-between">
                    <button wire:click="back" class="text-gray-500 font-bold px-6 py-3">← Back</button>
                    <button wire:click="next"
                        class="bg-cyan-600 hover:bg-cyan-700 text-white px-10 py-3 rounded-lg font-bold shadow-lg">Continue</button>
                </div>
            @endif

            {{-- STEP 3: LOGISTICS --}}
            @if ($step === 3)
                <div class="space-y-6">
                    <h2 class="text-xl font-bold text-gray-800 border-b pb-2">Scheduling & Logistics</h2>

                    <div>
                        <label class="block mb-3 text-sm font-bold text-gray-600 uppercase">Preferred Lesson
                            Days</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach ($dayOptions as $day)
                                <div
                                    class="flex flex-wrap items-center gap-4 p-3 border rounded-lg hover:bg-cyan-50 transition-all">
                                    <label class="flex items-center gap-2 cursor-pointer min-w-[120px]">
                                        <input type="checkbox"
                                            wire:model.live="preferred_days.{{ $day }}.selected"
                                            class="w-4 h-4 text-cyan-600 border-gray-300 rounded">
                                        <span class="text-sm font-medium">{{ $day }}</span>
                                    </label>

                                    @if (isset($preferred_days[$day]['selected']) && $preferred_days[$day]['selected'])
                                        <div class="flex items-center gap-2 animate-fadeIn">
                                            <span class="text-xs text-gray-400 font-bold uppercase">At:</span>
                                            <input type="time"
                                                wire:model="preferred_days.{{ $day }}.time"
                                                class="border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-cyan-500">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @error('preferred_days')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Delivery Mode</label>
                            <select wire:model.live="delivery_mode"
                                class="w-full border-gray-300 rounded-lg px-4 py-3">
                                <option value="online">Online</option>
                                <option value="offline">Offline (Home/Physical)</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                            @error('delivery_mode')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Tutor Gender
                                Preference</label>
                            <select wire:model="preferred_tutor_gender"
                                class="w-full border-gray-300 rounded-lg px-4 py-3">
                                <option value="any">Any Gender</option>
                                <option value="male">Male Only</option>
                                <option value="female">Female Only</option>
                            </select>
                            @error('preferred_tutor_gender')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Duration per Session
                                (minutes)</label>
                            <input type="number" wire:model="duration_per_session"
                                class="w-full border-gray-300 rounded-lg px-4 py-3" placeholder="e.g., 60">
                            @error('duration_per_session')
                                <p class="text-red-500 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Min Monthly Budget
                                (₦)</label>
                            <input type="number" wire:model="budget_min"
                                class="w-full border-gray-300 rounded-lg px-4 py-3" placeholder="e.g. 50000">
                            @error('budget_min')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Max Monthly Budget
                                (₦)</label>
                            <input type="number" wire:model="budget_max"
                                class="w-full border-gray-300 rounded-lg px-4 py-3" placeholder="e.g. 100000">
                            @error('budget_max')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    @if ($delivery_mode !== 'online')
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">State</label>
                                <select wire:model="state" class="w-full border-gray-300 rounded-lg px-4 py-3">
                                    <option value="">Select State</option>
                                    @foreach ($states as $st)
                                        <option value="{{ $st }}">{{ $st }}</option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">City</label>
                                <input type="text" wire:model="city"
                                    class="w-full border-gray-300 rounded-lg px-4 py-3">
                                @error('city')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Specific
                                    Address</label>
                                <input type="text" wire:model="address"
                                    class="w-full border-gray-300 rounded-lg px-4 py-3">
                                @error('address')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block mb-1 text-sm font-bold text-gray-600 uppercase">Additional Notes</label>
                        <textarea wire:model="additional_notes" class="w-full border-gray-300 rounded-lg px-4 py-3" rows="3"
                            placeholder="Specific learning needs, disabilities, or goals..."></textarea>
                        @error('addtional_notes')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <button wire:click="back" class="text-gray-500 font-bold px-6 py-3">← Back</button>
                    <button wire:click="submit"  wire:target='submit' wire:loading.class='hidden'
                        class="bg-cyan-600 hover:bg-cyan-700 text-white px-12 py-3 rounded-lg font-bold shadow-lg transition-all">
                        Submit Tutor Request
                    </button>
                    <p wire:loading wire:target='submit'
                        class="bg-cyan-600 text-white px-6 py-3 rounded-xl font-semibold flex items-center gap-2">
                        <i class="fas fa-spinner animate-spin"></i>
                        <span>Submitting...</span>
                    </p>
                </div>
            @endif

            {{-- STEP 4: SUCCESS --}}
            @if ($step === 4)
                <div class="text-center py-10">
                    <div
                        class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">
                        ✓</div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Request Submitted!</h2>
                    <p class="text-gray-600 max-w-sm mx-auto mb-8">We've received your details. Our team will review
                        your request and contact you within 24 hours.</p>
                    <a href="/" class="bg-gray-900 text-white px-8 py-3 rounded-lg font-bold">Back to
                        Homepage</a>
                </div>
            @endif
        </div>
    </div>
</div>
