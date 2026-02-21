<div class="p-6 max-w-7xl mx-auto space-y-6 text-gray-900">
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
    @if (session()->has('success'))
        <div class="flex items-center p-4 mb-4 text-cyan-800 rounded-2xl bg-cyan-50 border border-cyan-100 animate-fade-in-down"
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
        <div class="flex items-center p-4 mb-4 text-red-800 rounded-2xl bg-red-50 border border-red-100" role="alert">
            <svg class="flex-shrink-0 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <div class="ml-3 text-sm font-bold">{{ session('error') }}</div>
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-6 border-t border-slate-50">
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
                                class="text-sm font-bold text-slate-700">${{ number_format($request->budget_min, 0) }}
                                - ${{ number_format($request->budget_max, 0) }}</span>
                        </div>
                        <div>
                            <span
                                class="block text-[10px] uppercase font-bold text-slate-400 tracking-tighter">Frequency</span>
                            <span class="text-sm font-bold text-slate-700">{{ $request->sessions_per_week }}x Per
                                Week</span>
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

    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md">
            <div
                class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto flex flex-col">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800">
                            {{ $isEditing ? 'Edit Request' : 'Post New Request' }}</h2>
                        <p class="text-xs text-slate-500">Provide details for the best tutor matching.</p>
                    </div>
                    <button wire:click="$set('showModal', false)"
                        class="p-2 hover:bg-slate-100 rounded-full transition-all">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-8">
                    <form wire:submit="save" class="space-y-6 text-gray-900">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label
                                    class="text-xs font-black text-slate-500 uppercase tracking-widest">Subject</label>
                                <select wire:model="service_item_id"
                                    class="w-full bg-slate-100 border-transparent rounded-2xl py-3 focus:bg-white focus:ring-2 focus:ring-cyan-500 transition-all">
                                    <option value="">Select Subject</option>
                                    @foreach ($serviceItems as $si)
                                        <option value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-xs font-black text-slate-500 uppercase tracking-widest">Level</label>
                                <select wire:model="level_id"
                                    class="w-full bg-slate-100 border-transparent rounded-2xl py-3 focus:bg-white focus:ring-2 focus:ring-cyan-500 transition-all">
                                    <option value="">Select Level</option>
                                    @foreach ($levels as $l)
                                        <option value="{{ $l->id }}">{{ $l->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Delivery
                                    Mode</label>
                                <select wire:model.live="delivery_mode"
                                    class="w-full bg-slate-100 border-transparent rounded-2xl py-3 focus:bg-white focus:ring-2 focus:ring-cyan-500">
                                    <option value="online">Online</option>
                                    <option value="offline">In-Person</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Session
                                    Type</label>
                                <select wire:model="session_type"
                                    class="w-full bg-slate-100 border-transparent rounded-2xl py-3 focus:bg-white focus:ring-2 focus:ring-cyan-500">
                                    <option value="individual">1-on-1</option>
                                    <option value="group">Group</option>
                                </select>
                            </div>
                        </div>

                        @if (in_array($delivery_mode, ['offline', 'hybrid']))
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Lesson
                                    Address</label>
                                <input type="text" wire:model="lesson_address"
                                    class="w-full bg-slate-100 border-transparent rounded-2xl py-3 focus:bg-white focus:ring-2 focus:ring-cyan-500">
                            </div>
                        @endif

                        <div class="space-y-3">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Preferred
                                Days</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                    <label class="cursor-pointer group">
                                        <input type="checkbox" wire:model="preferred_days"
                                            value="{{ $day }}" class="hidden peer">
                                        <span
                                            class="px-4 py-2 rounded-xl bg-slate-100 text-slate-500 font-bold text-sm peer-checked:bg-cyan-600 peer-checked:text-white transition-all inline-block">
                                            {{ $day }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Max
                                    Budget
                                    ($)</label>
                                <input type="number" wire:model="budget_max"
                                    class="w-full bg-slate-100 border-transparent rounded-2xl py-3">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Preferred
                                    Time</label>
                                <input type="text" wire:model="preferred_time" placeholder="e.g. 5pm onwards"
                                    class="w-full bg-slate-100 border-transparent rounded-2xl py-3">
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 pt-6 border-t border-slate-50">
                            <button type="button" wire:click="$set('showModal', false)"
                                class="px-6 py-3 font-bold text-slate-400 hover:bg-slate-100 rounded-xl">Cancel</button>
                            <button type="submit"
                                class="px-10 py-3 bg-cyan-600 text-white font-black rounded-xl hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all">
                                {{ $isEditing ? 'Save Changes' : 'Post Request' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showDetails && $selectedRequest)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-[2.5rem] w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col">
                <div class="p-8 bg-cyan-600 text-white flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-black">{{ $selectedRequest->serviceItem->name }}</h2>
                        <p class="text-cyan-100 text-sm">Application #TR-{{ $selectedRequest->id }}</p>
                    </div>
                    <button wire:click="$set('showDetails', false)"
                        class="p-2 hover:bg-cyan-500 rounded-full transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-8 space-y-6 overflow-y-auto max-h-[70vh] text-gray-900">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Level</label>
                            <p class="font-bold text-slate-700">{{ $selectedRequest->level->name ?? 'N/A' }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Exam
                                Focus</label>
                            <p class="font-bold text-slate-700">{{ $selectedRequest->examType->name ?? 'None' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tutor
                                Preference</label>
                            <p class="font-bold text-slate-700 uppercase">
                                {{ $selectedRequest->preferred_tutor_gender }} Tutor</p>
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Schedule</label>
                            <p class="font-bold text-slate-700">{{ $selectedRequest->sessions_per_week }}x week
                                ({{ $selectedRequest->duration_per_session }}m)</p>
                        </div>
                    </div>

                    <div class="p-5 bg-slate-50 rounded-2xl space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Additional
                            Requirements</label>
                        <p class="text-sm leading-relaxed text-slate-600 italic">
                            {{ $selectedRequest->additional_notes ?: 'No specific notes provided.' }}
                        </p>
                    </div>

                    @if ($selectedRequest->lesson_address)
                        <div class="flex items-start gap-3 p-4 border border-slate-100 rounded-2xl">
                            <svg class="w-5 h-5 text-cyan-500 mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Meeting
                                    Location</label>
                                <p class="text-sm font-bold text-slate-700">{{ $selectedRequest->lesson_address }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
