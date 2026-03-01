<div class="p-6 max-w-7xl mx-auto space-y-8">
    {{-- Header Section --}}
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                    My Booked <span class="text-cyan-600">Lessons</span>
                </h1>
                <p class="text-slate-500 font-medium text-xs">Manage, track, and approve your academic sessions.</p>
            </div>
        </div>
    </x-slot>
    <div wire:offline class="fixed top-6 right-6 z-50">
        <div class="flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-full shadow-2xl animate-pulse">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a5 5 0 010-7.072M4.929 19.071a9 9 0 010-12.728m0 0l2.829 2.829m-2.829-2.829L3 3">
                </path>
            </svg>
            <span class="text-xs font-bold uppercase tracking-wider">System Offline</span>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="bg-white p-2 rounded-2xl border border-slate-100 shadow-sm flex flex-wrap gap-2 overflow-x-auto">
        @foreach ($tabs as $tab)
            <button wire:click="setTab('{{ $tab['name'] }}')"
                class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all whitespace-nowrap flex items-center gap-2
                {{ $activeTab == $tab['name'] ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-200' : 'text-slate-500 hover:bg-slate-50' }}">
                <i class="fa-solid {{ $tab['icon'] }} opacity-80"></i>
                {{ str_replace(' Lessons', '', $tab['name']) }}
            </button>
        @endforeach
    </div>

    {{-- Alerts --}}
    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl font-bold flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif
    @if (session()->has('success'))
        <div
            class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl font-bold flex items-center gap-3">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Lessons Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($lessons as $booking)
            <div
                class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all p-6 group">
                <div class="flex justify-between items-start mb-6">
                    <span
                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $this->getStatusClasses($booking->status) }}">
                        {{ $booking->status }}
                    </span>

                    <div class="flex items-center gap-2">
                        @if ($booking->status === 'Accepted' && $booking->client_payment_status !== 'Paid')
                            <button wire:click="retryPayment({{ $booking->id }})"
                                class="px-2 py-1 text-sm rounded-2xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition">
                                Complete Payment
                            </button>
                        @endif
                        <button wire:click="showLesson({{ $booking->id }})"
                            class="w-8 h-8 flex items-center justify-center text-slate-400 bg-slate-50 rounded-lg hover:bg-cyan-600 hover:text-white transition-all">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 text-xl">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 leading-tight">{{ $booking->tutor->name }}</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Assigned Tutor</p>
                    </div>
                </div>

                <div class="space-y-3 mb-6 bg-slate-50/50 p-4 rounded-2xl">
                    <div class="flex items-center gap-3 text-sm font-medium text-slate-600">
                        <i class="fa-solid fa-calendar text-cyan-500 w-4"></i>
                        <span>{{ $booking->start_date }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium text-slate-600">
                        <i class="fa-solid fa-layer-group text-cyan-500 w-4"></i>
                        <span class="truncate">{{ $booking->subjects_string ?? $booking->subjects }}</span>
                    </div>
                </div>
                @if ($booking->status === 'Pending')
                    <button wire:click="editAcceptance({{ $booking->id }})"
                        class="w-full py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-sm hover:bg-cyan-600 transition-all flex items-center justify-center gap-2">
                        <span>Review Lesson Acceptance</span>
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                @endif
                @if (in_array($booking->status, ['Completed', 'Declined']))
                    <button wire:click="editApproval({{ $booking->id }})"
                        class="w-full py-3.5 rounded-2xl bg-emerald-900 text-white font-bold text-sm hover:bg-emerald-600 transition-all flex items-center justify-center gap-2">
                        <span>Review Lesson Approval</span>
                        <i class="fa-solid fa-check-double"></i>
                    </button>
                @endif
            </div>
        @empty
            <div
                class="col-span-full py-20 bg-slate-50 rounded-[2.5rem] border-2 border-dashed border-slate-200 text-center">
                <i class="fa-solid fa-calendar-xmark text-slate-200 text-5xl mb-4"></i>
                <p class="text-slate-400 font-black uppercase tracking-widest text-xs">No {{ $activeTab }} Found</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $lessons->links() }}
    </div>

    {{-- Expanded Details Drawer --}}
    <div x-data="{ open: @entangle('showModal') }" x-show="open" style="display: none;" class="fixed inset-0 z-[80] overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div class="absolute inset-y-0 right-0 max-w-full flex">
            <div class="w-screen max-w-lg bg-white shadow-2xl flex flex-col"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0">

                @if ($selectedLesson)
                    {{-- Drawer Header --}}
                    <div class="p-8 bg-cyan-600 text-white relative">
                        <button @click="open = false"
                            class="absolute top-6 right-6 text-white/60 hover:text-white transition-all">
                            <i class="fa-solid fa-xmark text-2xl"></i>
                        </button>
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-2xl font-black">{{ $selectedLesson->learners_string }}</h2>
                            <span class="px-2 py-0.5 rounded bg-white/20 text-[10px] font-bold uppercase">ID:
                                #{{ $selectedLesson->id }}</span>
                        </div>
                        @if ($selectedLesson->serviceItem->has_subjects)
                            <p class="text-cyan-100 text-xs font-bold uppercase tracking-widest">
                                {{ $selectedLesson->subjects_string }}</p>
                        @endif
                    </div>

                    {{-- Drawer Body --}}
                    <div class="p-8 flex-1 overflow-y-auto space-y-8">
                        {{-- Tutor Details --}}
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Tutor
                            Particulars</label>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Tutor</label>
                                <p class="text-sm font-bold text-slate-800">
                                    {{ $selectedLesson?->tutor?->tutorProfile?->fullName }}</p>
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Tutor's
                                    Contact</label>
                                <p class="text-sm font-bold text-cyan-600 flex items-center justify-between">
                                    <span>{{ $selectedLesson->tutor?->tutorProfile?->phone ?? 'N/A' }}</span> <img
                                        src="{{ asset('storage/' . $selectedLesson->tutor?->tutorProfile?->image) }}"
                                        alt="{{ $selectedLesson->tutor?->tutorProfile?->fullName }}"
                                        class="object-cover h-10 w-10 rounded-full text-end">
                                </p>
                            </div>
                            <div class="col-span-2">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Tutor's
                                    Bio</label>
                                <p class="text-xs font-bold text-cyan-600">
                                    {{ $selectedLesson->tutor?->tutorProfile?->careerProfile ?? 'N/A' }}</p>
                            </div>

                        </div>

                        {{-- Service Details --}}
                        <div class="pt-6 border-t border-slate-100 ">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Service
                                Details</label>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Service
                                        Type</label>
                                    <p class="text-sm font-bold text-cyan-600">
                                        {{ $selectedLesson->serviceItem->name ?? 'Tuition' }}</p>
                                </div>
                                @if ($selectedLesson->serviceItem->requires_exam_type)
                                    <div>
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Exam
                                            Type</label>
                                        <p class="text-sm font-bold text-cyan-600">
                                            {{ $selectedLesson->examType->name ?? 'N/A' }}</p>
                                    </div>
                                @endif
                                <div class="col-span-2">
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Location</label>
                                    <p class="text-sm text-slate-600 flex items-start gap-2">
                                        <i class="fa-solid fa-location-dot text-cyan-500 mt-1"></i>
                                        {{ $selectedLesson->location }}
                                    </p>
                                </div>

                            </div>

                        </div>

                        {{-- Academic Profile --}}
                        <div class="pt-6 border-t border-slate-100">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Academic
                                Profile</label>
                            <div class="grid grid-cols-3 gap-4">
                                @if ($selectedLesson->serviceItem->requires_level)
                                    <div class="p-3 bg-slate-50 rounded-xl">
                                        <p class="text-[9px] text-slate-400 font-bold uppercase mb-1">Class/Level</p>
                                        <p class="text-xs font-bold text-slate-700">
                                            {{ $selectedItem?->level?->name ?? ($selectedLesson->classes ?? 'N/A') }}
                                        </p>
                                    </div>
                                @endif
                                @if ($selectedLesson->serviceItem->requires_curriculum)
                                    <div class="p-3 bg-slate-50 rounded-xl">
                                        <p class="text-[9px] text-slate-400 font-bold uppercase mb-1">Curriculum</p>
                                        <p class="text-xs font-bold text-slate-700">{{ $selectedLesson->curriculum }}
                                        </p>
                                    </div>
                                @endif
                                <div class="p-3 bg-slate-50 rounded-xl">
                                    <p class="text-[9px] text-slate-400 font-bold uppercase mb-1">Tutor Pref.</p>
                                    <p class="text-xs font-bold text-slate-700">{{ $selectedLesson->tutorGender }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Learner Details --}}
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Learners
                                List</label>
                            <div class="space-y-2">
                                @foreach ($selectedLesson->formatted_learners ?? [] as $learner)
                                    <div
                                        class="flex justify-between items-center p-3 border border-slate-100 rounded-xl text-sm">
                                        <span class="font-bold text-slate-700"><i
                                                class="fa-solid fa-user-graduate text-slate-300 mr-2"></i>{{ $learner['name'] }}</span>
                                        <span class="text-slate-400 text-xs font-medium">{{ $learner['age'] }}
                                            Years</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Schedule & Billing --}}
                        <div class="pt-6 border-t border-slate-100">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Schedule
                                & Contract</label>
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach ($selectedLesson->formatted_days_times ?? [] as $slot)
                                    )
                                    <span
                                        class="bg-white border border-slate-200 px-3 py-1.5 rounded-xl text-[11px] font-bold text-slate-600 flex items-center gap-2">
                                        <i class="fa-solid fa-clock text-cyan-500"></i>
                                        {{ substr($slot['day'], 0, 3) }} at {{ $slot['time'] }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="bg-slate-900 rounded-2xl p-6 text-white">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Contract Amount
                                        </p>
                                        <p class="text-2xl font-black">₦{{ number_format($selectedLesson->amount) }}
                                        </p>
                                    </div>
                                    <div class="">
                                        <span
                                            class="px-2 py-1 rounded-lg bg-white/10 text-[10px] font-bold uppercase">{{ $selectedLesson->client_payment_status }}</span>
                                        @if ($selectedLesson->status === 'Accepted' && $selectedLesson->client_payment_status !== 'Paid')
                                            <button wire:click="retryPayment({{ $selectedLesson->id }})"
                                                class="px-2 py-1 text-xs rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition">
                                                Complete Payment
                                            </button>
                                        @endif
                                    </div>

                                </div>
                                <div class="grid grid-cols-2 gap-4 border-t border-white/10 pt-4 text-xs">
                                    <div>
                                        <p class="text-white/40 font-bold uppercase text-[9px]">Pace</p>
                                        <p class="font-bold">{{ $selectedLesson->sessions }} sessions/week</p>
                                    </div>
                                    <div>
                                        <p class="text-white/40 font-bold uppercase text-[9px]">Duration</p>
                                        <p class="font-bold">{{ $selectedLesson->duration }} / session</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div class="pt-6 border-t border-slate-100 grid grid-cols-1 gap-4">
                            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                                <label class="text-[9px] font-black text-amber-600 uppercase block mb-1">Tutor
                                    Remarks</label>
                                <p class="text-xs text-amber-800 italic">
                                    "{{ $selectedLesson->tutorRemarks ?? 'No special remarks from tutor.' }}"</p>
                            </div>
                            <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                                <label class="text-[9px] font-black text-blue-600 uppercase block mb-1">My
                                    Instructions</label>
                                <p class="text-xs text-blue-800 italic">
                                    "{{ $selectedLesson->clientAcceptanceRemarks ?? 'No specific instructions provided.' }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Drawer Footer --}}
                    <div class="p-6 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div class="text-[10px] font-black text-slate-400 uppercase">
                            Status: <span class="text-cyan-600">{{ $selectedLesson->status }}</span>
                        </div>
                        <div class="">
                            <button @click="open = false"
                                class="py-3 px-8 bg-slate-900 text-white font-black rounded-xl hover:bg-black transition-all">
                                Close Panel
                            </button>
                            @if ($selectedLesson->status === 'Pending')
                                <button wire:click="editAcceptance({{ $selectedLesson->id }})"
                                    class="px-8 py-3 rounded-2xl bg-cyan-600 text-white font-bold hover:bg-cyan-700 transition">
                                    Review Acceptance
                                </button>
                            @elseif ($selectedLesson->status === 'Completed')
                                <button wire:click="editApproval({{ $selectedLesson->id }})"
                                    class="px-8 py-3 rounded-2xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition">
                                    Review Approval
                                </button>
                            @endif
                        </div>

                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- Confirmation Modal (Acceptance) --}}
    @if ($showAcceptanceModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-8 text-center bg-slate-50 border-b border-slate-100">
                    <div
                        class="w-16 h-16 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800">Commencement</h2>
                    <p class="text-slate-500 text-sm">Review schedule and confirm the lesson.</p>
                </div>

                <div class="p-8 space-y-4">
                    <div class="">
                        <textarea wire:model="clientAcceptanceRemarks"
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm focus:ring-2 focus:ring-cyan-500"
                            placeholder="Special instructions..."></textarea>
                        @error('clientAcceptanceRemarks')
                            <p class="text-xs mt-1 text-red-600">{{ 'Remark is required!' }}</p>
                        @enderror
                    </div>

                    <div class="">
                        <select wire:model.live="status"
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold">
                            <option value="">Choose Action</option>
                            <option value="Adjust">Request Adjustment</option>
                            <option value="Accepted">Accept & Start</option>
                        </select>
                        @error('status')
                            <p class="text-xs mt-1 text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button wire:loading.class='hidden' wire:click="submitAcceptance" @disabled(!$status)
                        class="w-full py-4 rounded-2xl font-black shadow-lg {{ $status === 'Adjust' ? 'bg-amber-600 shadow-amber-100' : 'bg-cyan-600 shadow-cyan-100' }} disabled:bg-slate-300 text-white transition">
                        @if ($status === 'Adjust')
                            Submit Adjustment Request
                        @elseif ($status === 'Accepted')
                            Accept & Proceed to Payment
                        @else
                            Choose Action First
                        @endif
                    </button>
                    <button wire:loading wire:target="submitAcceptance"
                        class="w-full py-4 rounded-2xl font-black shadow-lg {{ $status === 'Adjust' ? 'bg-amber-600 shadow-amber-100' : 'bg-cyan-600 shadow-cyan-100' }} disabled:bg-slate-300 text-white transition">
                        <i class="fas fa-spinner animate-spin mr-1"></i> Processing...
                    </button>
                    <button wire:click="$set('showAcceptanceModal', false)"
                        class="w-full py-2 font-bold text-slate-400">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Approval Modal (Completion) --}}
    @if ($showApprovalModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-8 text-center bg-emerald-50 border-b border-emerald-100">
                    <div
                        class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800">Final Approval</h2>
                    <p class="text-slate-500 text-sm">Close this lesson and approve tutor payment.</p>
                </div>

                <div class="p-8 space-y-4">
                    <div class="">
                        <textarea wire:model="clientApprovalRemarks"
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm focus:ring-2 focus:ring-emerald-500"
                            placeholder="Feedback for the tutor..."></textarea>
                        @error('clientApprovalRemarks')
                            <p class="text-xs mt-1 text-red-600">{{ 'Remark is required!' }}</p>
                        @enderror

                    </div>
                    <div class="">
                        <select wire:model.live="status"
                            class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold">
                            <option value="">Select Status</option>
                            <option value="Declined">Decline Approval</option>
                            <option value="Closed">Approve & Close</option>
                        </select>
                        @error('status')
                            <p class="text-xs mt-1 text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    <button wire:click="submitApproval" @disabled(!$status)
                        class="w-full py-4  text-white font-black rounded-2xl shadow-lg  {{ $status === 'Declined' ? 'bg-cyan-600 shadow-cyan-100' : 'bg-emerald-600 shadow-emerald-100' }} disabled:bg-slate-300">
                        {{ $status === 'Declined' ? 'Submit' : 'Finish Lesson' }}
                    </button>
                    <p wire:loading wire:target="submitApproval"
                        class="flex items-center w-full py-4  text-white font-black rounded-2xl shadow-lg  {{ $status === 'Declined' ? 'bg-cyan-600 shadow-cyan-100' : 'bg-emerald-600 shadow-emerald-100' }} disabled:bg-slate-300">
                        <i class="fas fa-spinner animate-spin mr-1"></i> Submitting...
                    </p>
                    <button wire:click="$set('showApprovalModal', false)"
                        class="w-full py-2 font-bold text-slate-400 text-sm">Back to List</button>
                </div>
            </div>
        </div>
    @endif
</div>
