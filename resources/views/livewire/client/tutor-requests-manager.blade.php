<div class="px-6 max-w-7xl mx-auto space-y-6 text-gray-900">
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Tutor <span
                        class="text-cyan-600">Requests</span></h1>
                <p class="text-slate-500 text-sm mt-1">Manage, track, and filter your learning applications.</p>
            </div>
            <button x-on:click="$dispatch('openRequestCreate')"
                class="flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl font-bold transition-all shadow-lg shadow-cyan-100 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Request Tutor
            </button>
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
    @if (session()->has('success'))
        <div class="flex items-center p-2 mb-1 text-cyan-800 rounded-2xl bg-cyan-50 border border-cyan-100 animate-fade-in-down"
            role="alert">
            <svg class="flex-shrink-0 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            <div class="ml-3 text-sm font-bold">{{ session('success') }}</div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="flex items-center p-2 mb-1 text-red-800 rounded-2xl bg-red-50 border border-red-100" role="alert">
            <svg class="flex-shrink-0 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <div class="ml-3 text-sm font-bold">{{ session('error') }}</div>
        </div>
    @endif

    <div class="bg-white p-2 rounded-xl shadow-sm border border-slate-100">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-4 border-t border-slate-50">
            <div class="md:col-span-5 relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by subject name..."
                    class="w-full pl-12 bg-slate-50 border-transparent rounded-2xl py-3 focus:bg-white focus:ring-2 focus:ring-cyan-500 transition-all">
            </div>

            <div class="md:col-span-3">
                <select wire:model.live="statusFilter"
                    class="w-full bg-slate-50 border-transparent rounded-2xl py-3 focus:ring-2 focus:ring-cyan-500 transition-all">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="reviewing">Reviewing</option>
                    <option value="matched">Matched</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="md:col-span-4 flex gap-2">
                <select wire:model.live="sortField"
                    class="flex-1 bg-slate-50 border-transparent rounded-2xl py-3 focus:ring-2 focus:ring-cyan-500 transition-all">
                    <option value="created_at">Date Created</option>
                    <option value="budget_max">Max Budget</option>
                </select>
                <button wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')"
                    class="p-3 bg-slate-50 rounded-2xl text-slate-600 hover:bg-cyan-50 hover:text-cyan-600 transition-all">
                    @if ($sortDirection === 'asc')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M3 4h13M3 8h9m-9 4h9m5-1l-4 4m0 0l-4-4m4 4V8" />
                        </svg>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($requests as $request)
            <div
                class="bg-white rounded-xl border border-slate-100 shadow-sm hover:shadow-xl transition-all overflow-hidden relative group">
                <div class="p-4">
                    <div class="flex items-center justify-between items-start mb-4 bg-cyan-600 p-2 rounded text-white">
                        <span
                            class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest
                            {{ $request->status === 'pending' ? 'bg-amber-50 text-amber-600' : 'bg-cyan-50 text-cyan-600' }}">
                            {{ $request->status }}
                        </span>

                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            @if (in_array($request->status, ['pending', 'reviewing']))
                                <button wire:click="openEdit({{ $request->id }})"
                                    class="p-2 bg-slate-50 text-slate-400 hover:text-cyan-600 rounded-xl transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button wire:confirm="Are you sure you want to delete this request?"
                                    wire:click="delete({{ $request->id }})"
                                    class="p-2 bg-slate-50 text-slate-400 hover:text-red-600 rounded-xl transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-slate-800">{{ $request->serviceItem->name }}</h3>
                    <p class="text-sm text-slate-500 mb-6">{{ $request->level->name ?? 'General Level' }} •
                        {{ ucfirst($request->delivery_mode) }}</p>

                    <div class="grid grid-cols-2 gap-4 border-t border-slate-50 pt-4 mb-6">
                        <div>
                            <span
                                class="block text-[10px] uppercase font-bold text-slate-400 tracking-tighter">Budget</span>
                            <span
                                class="text-sm font-bold text-slate-700">₦{{ number_format($request->budget_min, 0) }}
                                - ₦{{ number_format($request->budget_max, 0) }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-tighter">Session
                                Type</span>
                            <span class="text-sm font-bold text-slate-700">{{ $request->session_type }}</span>
                        </div>
                    </div>

                    <button wire:click="openDetails({{ $request->id }})"
                        class="w-full py-3 rounded-xl bg-cyan-50 hover:bg-cyan-600 hover:text-white text-slate-600 font-bold text-sm transition-all">
                        View Detailed Info
                    </button>
                </div>
            </div>
        @empty
            <div
                class="col-span-full py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                <h3 class="text-slate-400 font-medium">No requests found matching your filters.</h3>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $requests->links() }}
    </div>

    @if ($showDetails && $selectedRequest)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div
                class="bg-white rounded-[2.5rem] w-full max-w-3xl shadow-2xl overflow-hidden flex flex-col max-h-[95vh]">
                <div class="p-8 bg-slate-800 text-white flex justify-between items-start">
                    <div>
                        <span
                            class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-cyan-500 mb-2 inline-block">
                            {{ $selectedRequest->status }}
                        </span>
                        <h2 class="text-3xl font-black">{{ $selectedRequest->serviceItem->name }}</h2>
                        <p class="text-slate-400 text-sm">Requested on
                            {{ $selectedRequest->created_at->format('M d, Y') }}</p>
                    </div>
                    <button wire:click="$set('showDetails', false)"
                        class="p-2 hover:bg-slate-700 rounded-full">✕</button>
                </div>

                <div class="p-8 space-y-8 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label
                                class="text-[10px] font-black text-cyan-600 uppercase tracking-widest block mb-2">Learner(s)</label>
                            <p class="font-bold text-slate-800">{{ $selectedRequest->learner_names }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $selectedRequest->is_for_self ? 'Self-application' : 'Managed Account' }}</p>
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-black text-cyan-600 uppercase tracking-widest block mb-2">Subjects/Focus</label>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($selectedRequest->subjects ?? [] as $subject)
                                    <span
                                        class="px-2 py-0.5 bg-slate-100 rounded text-xs font-bold text-slate-600 border">{{ $subject }}</span>
                                @endforeach
                                @if (empty($selectedRequest->subjects))
                                    <span class="text-slate-400 italic text-sm">General Study</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 p-6 bg-slate-50 rounded-3xl">
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Level</label>
                            <p class="font-bold text-slate-700">{{ $selectedRequest->level->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Curriculum</label>
                            <p class="font-bold text-slate-700">{{ $selectedRequest->curriculum }}</p>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Exam
                                Type</label>
                            <p class="font-bold text-slate-700">{{ $selectedRequest->examType->name ?? 'None' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div><label class="text-[10px] font-black text-slate-400 uppercase block">Mode</label>
                            <p class="font-bold capitalize">{{ $selectedRequest->delivery_mode }}</p>
                        </div>
                        <div><label class="text-[10px] font-black text-slate-400 uppercase block">Sessions</label>
                            <p class="font-bold">{{ $selectedRequest->sessions_per_week }}x / Week</p>
                        </div>
                        <div><label class="text-[10px] font-black text-slate-400 uppercase block">Duration</label>
                            <p class="font-bold">{{ $selectedRequest->duration_per_session }} min</p>
                        </div>
                        <div><label class="text-[10px] font-black text-slate-400 uppercase block">Gender Pref</label>
                            <p class="font-bold capitalize">{{ $selectedRequest->preferred_tutor_gender }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-4 p-4 border border-slate-100 rounded-2xl">
                            <div class="p-3 bg-green-50 text-green-600 rounded-xl font-bold">₦</div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase block">Monthly Budget
                                    Range</label>
                                <p class="font-bold text-slate-700">₦{{ number_format($selectedRequest->budget_min) }}
                                    - ₦{{ number_format($selectedRequest->budget_max) }}</p>
                            </div>
                        </div>
                        @if ($selectedRequest->lesson_address)
                            <div class="flex items-center gap-4 p-4 border border-slate-100 rounded-2xl">
                                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">📍</div>
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase block">Location</label>
                                    <p class="text-sm font-bold text-slate-700">
                                        {{ $selectedRequest->lesson_address }}, {{ $selectedRequest->city }},
                                        {{ $selectedRequest->state }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-6 bg-slate-50 border-t flex justify-end gap-3">
                    <button wire:click="$set('showDetails', false)"
                        class="px-6 py-2 text-sm font-bold text-slate-500">Close</button>
                    @if (in_array($selectedRequest->status, ['pending', 'reviewing']))
                        <button wire:click="openEdit({{ $selectedRequest->id }})"
                            class="px-6 py-2 bg-cyan-600 text-white rounded-xl text-sm font-bold shadow-lg">Edit
                            Request</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- REQUEST  MODAL --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-zoom-in h-90%">

                {{-- Progress Header --}}
                <div
                    class="bg-cyan-600 p-6 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black">{{ $isEditing ? 'Edit' : 'New' }} Tutor Request</h2>
                        <p class="text-cyan-100 text-[10px] font-bold uppercase tracking-widest mt-1">
                            Step {{ $step }} of 3:
                            {{ $step == 1 ? 'Learner' : ($step == 2 ? 'Academics' : 'Logistics') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @for ($i = 1; $i <= 3; $i++)
                            <div class="h-1.5 w-12 rounded-full {{ $step >= $i ? 'bg-white' : 'bg-cyan-800' }}"></div>
                        @endfor
                    </div>
                </div>

                <div class="p-8 max-h-[70vh] overflow-y-auto">

                    {{-- STEP 1: IDENTITY --}}
                    @if ($step === 1)
                        <div class="space-y-6">
                            <h3 class="text-lg font-bold text-slate-800 border-b pb-2">Learner Information</h3>
                            <div class="bg-slate-50 p-6 rounded-3xl">
                                <label class="block mb-4 text-sm font-bold text-slate-700">Who is this request
                                    for?</label>
                                <div class="flex gap-8 mb-6">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" wire:model.live="is_for_self" value="1"
                                            class="w-5 h-5 text-cyan-600">
                                        <span class="font-bold text-slate-700">Myself</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="radio" wire:model.live="is_for_self" value="0"
                                            class="w-5 h-5 text-cyan-600">
                                        <span class="font-bold text-slate-700">Someone Else</span>
                                    </label>
                                </div>

                                @if (!$is_for_self)
                                    <div class="space-y-4">
                                        @foreach ($learners as $idx => $l)
                                            <div class="flex gap-2">
                                                <input type="text" wire:model="learners.{{ $idx }}.name"
                                                    class="flex-1 rounded-xl border-slate-200 py-3"
                                                    placeholder="Full Name">
                                                @if (count($learners) > 1)
                                                    <button wire:click="removeLearner({{ $idx }})"
                                                        class="text-red-400 p-2">✕</button>
                                                @endif
                                            </div>
                                            @error("learners.$idx.name")
                                                <span class="text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                        @endforeach
                                        <button wire:click="addLearner"
                                            class="text-xs font-black text-cyan-600 uppercase">+ Add Learner</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- STEP 2: ACADEMICS --}}
                    @if ($step === 2)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-bold text-slate-800 border-b pb-2">Service Details</h3>
                            </div>

                            <div class="md:col-span-2 grid md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase mb-1 block">Category</label>
                                    <select wire:model.live="service_id"
                                        class="w-full rounded-xl border-slate-200 py-3">
                                        <option value="">Select Category</option>
                                        @foreach ($services as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-1 block">Specific
                                        Item</label>
                                    <select wire:model.live="service_item_id"
                                        class="w-full rounded-xl border-slate-200 py-3">
                                        <option value="">Select Item</option>
                                        @foreach ($serviceItems as $si)
                                            <option value="{{ $si->id }}">{{ $si->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @if ($requires_level)
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase mb-1 block">Level</label>
                                    <select wire:model="level_id" class="w-full rounded-xl border-slate-200 py-3">
                                        <option value="">Select Level</option>
                                        @foreach ($levels as $l)
                                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($requires_exam_type)
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-1 block">Exam
                                        Type</label>
                                    <select wire:model="exam_type_id" class="w-full rounded-xl border-slate-200 py-3">
                                        <option value="">Select Exam</option>
                                        @foreach ($examTypes as $e)
                                            <option value="{{ $e->id }}">{{ $e->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($requires_curriculum)
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase mb-1 block">Curriculum</label>
                                    <select wire:model="curriculum" class="w-full rounded-xl border-slate-200 py-3">
                                        <option value="N/A">N/A</option>
                                        <option value="British">British</option>
                                        <option value="Nigerian">Nigerian</option>
                                        <option value="Blended">Blended</option>
                                    </select>
                                </div>
                            @endif

                            @if ($has_subjects)
                                <div class="md:col-span-2 bg-slate-50 p-6 rounded-3xl space-y-4">
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase block">Subjects/Courses</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach ($courses as $idx => $c)
                                            <div class="flex gap-2">
                                                <input type="text" wire:model="courses.{{ $idx }}.name"
                                                    class="flex-1 rounded-xl border-slate-200"
                                                    placeholder="e.g. Mathematics">
                                                @if (count($courses) > 1)
                                                    <button wire:click="removeCourse({{ $idx }})"
                                                        class="text-red-400">✕</button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <button wire:click="addCourse"
                                        class="text-xs font-black text-cyan-600 uppercase">+ Add Subject</button>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- STEP 3: LOGISTICS --}}
                    @if ($step === 3)
                        <div class="space-y-8">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 border-b pb-2 mb-4">Scheduling & Logistics
                                </h3>
                                <label class="text-[10px] font-black text-slate-400 uppercase block mb-3">Preferred
                                    Days & Times</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach ($dayOptions as $day)
                                        @php
                                            $selectedItem = collect($preferred_days)->firstWhere('day', $day);
                                            $isSelected = (bool) $selectedItem;
                                            $dayIdx = collect($preferred_days)->search(fn($i) => $i['day'] === $day);
                                        @endphp
                                        <div
                                            class="p-3 border rounded-2xl transition-all {{ $isSelected ? 'bg-cyan-50 border-cyan-200' : 'bg-white' }}">
                                            <label class="flex items-center justify-between cursor-pointer">
                                                <span
                                                    class="text-sm font-bold {{ $isSelected ? 'text-cyan-700' : 'text-slate-600' }}">{{ $day }}</span>
                                                <input type="checkbox" wire:click="toggleDay('{{ $day }}')"
                                                    {{ $isSelected ? 'checked' : '' }}
                                                    class="w-5 h-5 text-cyan-600 rounded">
                                            </label>
                                            @if ($isSelected)
                                                <input type="time"
                                                    wire:model="preferred_days.{{ $dayIdx }}.time"
                                                    class="mt-2 w-full text-xs border-none bg-transparent p-0 focus:ring-0 text-cyan-600 font-bold">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-1">Session
                                        Mode</label>
                                    <select wire:model.live="delivery_mode"
                                        class="w-full rounded-xl border-slate-200 py-3">
                                        <option value="online">Online</option>
                                        <option value="offline">Physical</option>
                                        <option value="hybrid">Hybrid</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-1">Session
                                        Type</label>
                                    <select wire:model="session_type" class="w-full rounded-xl border-slate-200 py-3">
                                        <option value="individual">Individual</option>
                                        <option value="group">Group</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-1">Duration
                                        (Mins)</label>
                                    <input type="number" wire:model="duration_per_session"
                                        class="w-full rounded-xl border-slate-200 py-3">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-1">Preferred
                                        Gender</label>
                                    <select wire:model="preferred_tutor_gender"
                                        class="w-full rounded-xl border-slate-200 py-3">
                                        <option value="any">Any</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-1">Min
                                        Budget (₦)</label>
                                    <input type="number" wire:model="budget_min"
                                        class="w-full rounded-xl border-slate-200 py-3">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase block mb-1">Max
                                        Budget (₦)</label>
                                    <input type="number" wire:model="budget_max"
                                        class="w-full rounded-xl border-slate-200 py-3">
                                </div>
                            </div>

                            @if ($delivery_mode !== 'online')
                                <div class="p-6 bg-amber-50 rounded-3xl border border-amber-100 space-y-4">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" wire:model.live="use_profile_address"
                                            class="w-5 h-5 text-cyan-600 rounded">
                                        <span class="text-sm font-bold text-slate-700">Use my profile address</span>
                                    </label>

                                    @if ($use_profile_address)
                                        @php $prof = Auth::user()->profile; @endphp
                                        @if ($prof && $prof->address)
                                            <div
                                                class="p-4 bg-white rounded-2xl text-xs text-slate-500 border border-amber-200 italic">
                                                {{ $prof->address }}, {{ $prof->city }}, {{ $prof->state }}
                                            </div>
                                        @else
                                            <div
                                                class="p-4 bg-white rounded-2xl text-xs text-red-600 font-bold border border-red-200">
                                                No address found in profile. Please enter manually or <a
                                                    href="/profile" class="underline text-cyan-600">set up profile
                                                    address</a>.
                                            </div>
                                        @endif
                                        @error('use_profile_address')
                                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                                        @enderror
                                    @else
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <select wire:model="state" class="rounded-xl border-slate-200">
                                                <option value="">State</option>
                                                @foreach ($states as $s)
                                                    <option value="{{ $s }}">{{ $s }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" wire:model="city"
                                                class="rounded-xl border-slate-200" placeholder="City">
                                            <textarea wire:model="street_address" class="md:col-span-2 rounded-xl border-slate-200"
                                                placeholder="Street Address / House No"></textarea>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <textarea wire:model="additional_notes" rows="2" class="w-full rounded-xl border-slate-200"
                                placeholder="Additional requirements..."></textarea>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="p-6 bg-slate-50 border-t flex justify-between rounded-b-[2.5rem]">
                    <button wire:click="$set('showModal', false)" class="font-bold text-slate-400">Cancel</button>
                    <div class="flex gap-1 sm:gap-3">
                        @if ($step > 1)
                            <button wire:click="back" class="px-4 py-2 border-2 rounded-xl font-black">Back</button>
                        @endif
                        @if ($step < 3)
                            <button wire:click="next"
                                class="px-4 py-2 bg-cyan-600 text-white rounded-xl font-black">Continue</button>
                        @else
                            <button wire:loading.class='hidden' wire:click="save"
                                class="px-4 py-2 bg-cyan-600 text-white rounded-xl font-black shadow-lg">
                                {{ $isEditing ? 'Save Changes' : 'Submit Request' }}
                            </button>
                            <p wire:loading wire:target="save"
                                class="flex items-center gap-2 px-4 py-2 bg-cyan-600 text-white rounded-xl font-black shadow-lg">
                                <i class="fas fa-spinner animate-spin mr-1"></i>
                                <span>{{ $isEditing ? 'Saving Changes...' : 'Submitting Request...' }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
