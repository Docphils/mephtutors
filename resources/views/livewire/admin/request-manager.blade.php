<div class="relative p-2 sm:p-6 lg:p-8 bg-cyan-100 min-h-screen text-slate-800 w-full">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="group flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200 text-cyan-600 rounded-xl shadow-sm hover:bg-cyan-600 hover:text-white transition-all shrink-0">
                    <i class="fa fa-arrow-left transition-transform group-hover:-translate-x-1 text-xs sm:text-sm"></i>
                </a>
                <div>
                    <h2 class="font-black text-lg sm:text-2xl lg:text-3xl text-slate-800 tracking-tight leading-tight">
                        Tutor <span class="text-cyan-600">Requests</span>
                    </h2>
                    <p class="hidden sm:block text-slate-500 text-xs sm:text-sm font-medium mt-1">
                        Manage and assign incoming tutor applications.
                    </p>
                </div>
            </div>


        </div>
    </x-slot>
    @if (session('success'))
        <div
            class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-lg text-xs font-bold animate-fade-in-down">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-7xl mx-auto mt-4 sm:mt-8">

        {{-- Filters --}}
        <div class="bg-white border border-slate-200 p-2 sm:p-4 rounded-xl sm:rounded-2xl shadow-sm mb-4 sm:mb-6">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                <nav class="flex overflow-x-auto no-scrollbar items-center p-1 bg-cyan-600 rounded-lg w-full lg:w-auto"
                    x-data="{ activeStatus: @entangle('status') }">
                    @foreach (['all', 'pending', 'reviewing', 'matched', 'in_progress', 'completed', 'cancelled'] as $status)
                        <button wire:click.prevent="$set('status', '{{ $status }}')"
                            :class="activeStatus === '{{ $status }}' ? 'bg-white text-cyan-600 shadow-sm' :
                                'text-white hover:text-cyan-100'"
                            class="flex-1 lg:flex-none px-3 sm:px-5 py-1.5 text-[10px] sm:text-sm font-bold rounded-md transition-all whitespace-nowrap capitalize">
                            {{ str_replace('_', ' ', $status) }}
                        </button>
                    @endforeach
                </nav>

                <div class="relative w-full lg:flex-1">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-cyan-500 text-slate-700 placeholder:text-slate-400 text-xs sm:text-sm" />
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm w-full overflow-hidden">
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-cyan-600 text-white">
                            <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest">Client / Service</th>
                            <th class="hidden md:table-cell px-4 py-3 text-[10px] font-black uppercase tracking-widest">
                                Created</th>
                            <th class="hidden sm:table-cell px-4 py-3 text-[10px] font-black uppercase tracking-widest">
                                Location</th>
                            <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest">Status</th>
                            <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-right">
                                Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $request)
                            <tr wire:key="row-{{ $request->id }}" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 text-sm">
                                        {{ $request->user?->name ?? 'Guest' }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        Learner(s): {{ $request->learner_names ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-cyan-600 font-medium">
                                        {{ $request->serviceItem?->name }}
                                    </div>
                                </td>

                                <td class="hidden md:table-cell px-4 py-4 text-xs text-slate-600 italic">
                                    {{ $request->created_at->format('M d, Y') }}
                                </td>

                                <td class="hidden sm:table-cell px-4 py-4 text-xs text-slate-600">
                                    @if ($request->delivery_mode === 'online')
                                        Online
                                    @else
                                        {{ $request->lesson_address ?? $request->city . ', ' . $request->state }}
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-block px-2 py-1 rounded-full text-[10px] font-black uppercase
                                        {{ in_array($request->status, ['matched', 'completed']) ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ in_array($request->status, ['pending', 'reviewing']) ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $request->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}">
                                        {{ str_replace('_', ' ', $request->status) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click.prevent="showRequest({{ $request->id }})"
                                            class="p-1.5 text-cyan-500 hover:bg-cyan-50 rounded-lg">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button wire:click.prevent="openEditModal({{ $request->id }})"
                                            class="p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click.prevent="openDeleteModal({{ $request->id }})"
                                            class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    No requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="px-4 py-3 bg-slate-50 border-t border-slate-100">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Details Modal --}}
    @if ($showModal && $selectedRequest)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[95vh] overflow-hidden flex flex-col border border-slate-200">

                {{-- Header --}}
                <div class="bg-white border-b border-slate-100 px-8 py-5 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-cyan-50 text-cyan-600 rounded-xl flex items-center justify-center text-xl shadow-sm border border-cyan-100">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">
                                Request Profile</h3>
                            <p class="text-2xl font-black text-slate-800 tracking-tight">
                                {{ $selectedRequest->serviceItem?->name }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-center">
                        <div class="text-right">
                            <span
                                class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border 
                            {{ in_array($selectedRequest->status, ['matched', 'completed']) ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-700' }}">
                                {{ str_replace('_', ' ', $selectedRequest->status) }}
                            </span>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">
                                #REQ-{{ str_pad($selectedRequest->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="p-8 overflow-y-auto bg-slate-50/50">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                        {{-- Left Column: Client & Academic (4 Cols) --}}
                        <div class="lg:col-span-4 space-y-6">
                            {{-- Client Info --}}
                            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                                <h4
                                    class="font-bold text-xs uppercase tracking-widest text-cyan-600 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-user-circle"></i> Client Details
                                </h4>
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="text-[10px] uppercase font-black text-slate-400 block">Name</label>
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $selectedRequest->user?->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="pt-2">
                                        <label
                                            class="text-[10px] uppercase font-black text-slate-400 block">Contact</label>
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $selectedRequest->user?->email }}</p>
                                        <p class="text-xs font-medium text-slate-500">
                                            {{ $selectedRequest->user?->userProfile?->phone ?? 'No phone' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Academic Profile --}}
                            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                                <h4 class="font-bold text-xs uppercase tracking-widest text-slate-500 mb-4">Academic
                                    Profile</h4>
                                <div class="space-y-4">
                                    @if ($selectedRequest->level_id)
                                        <div>
                                            <label
                                                class="text-[10px] uppercase font-black text-slate-400 block">Level</label>
                                            <span
                                                class="text-sm font-bold text-slate-700">{{ $selectedRequest->level?->name }}</span>
                                        </div>
                                    @endif
                                    @if ($selectedRequest->curriculum && $selectedRequest->curriculum !== 'N/A')
                                        <div class="pt-3 border-t border-slate-100">
                                            <label
                                                class="text-[10px] uppercase font-black text-slate-400 block">Curriculum</label>
                                            <span
                                                class="text-sm font-bold text-slate-700">{{ $selectedRequest->curriculum }}</span>
                                        </div>
                                    @endif
                                    @if ($selectedRequest->subjects)
                                        <div class="pt-3 border-t border-slate-100">
                                            <label
                                                class="text-[10px] uppercase font-black text-slate-400 block mb-2">Subjects</label>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($selectedRequest->subjects as $subject)
                                                    <span
                                                        class="px-2 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] font-bold text-slate-600 uppercase">{{ $subject }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Middle Column: Scheduling & Learners (4 Cols) --}}
                        <div class="lg:col-span-4 space-y-6">
                            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                                <h4
                                    class="font-bold text-xs uppercase tracking-widest text-cyan-600 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-clock"></i> Schedule & Logistics
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="text-[10px] uppercase font-black text-slate-400 block mb-2">Preferred
                                            Days & Times</label>
                                        <div class="space-y-2">
                                            @if (is_array($selectedRequest->preferred_days))
                                                @foreach ($selectedRequest->preferred_days as $slot)
                                                    <div
                                                        class="flex justify-between items-center bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">

                                                        <span class="text-xs font-bold text-slate-700">
                                                            {{ $slot['day'] }}
                                                        </span>

                                                        <span
                                                            class="text-[10px] font-black text-cyan-600 uppercase bg-white px-2 py-0.5 rounded shadow-sm border border-cyan-100">
                                                            {{ !empty($slot['time']) ? \Carbon\Carbon::parse($slot['time'])->format('h:i A') : 'Anytime' }}
                                                        </span>

                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-sm font-bold text-slate-700">Flexible Schedule</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="pt-3 border-t border-slate-100">
                                        <label
                                            class="text-[10px] uppercase font-black text-slate-400 block mb-1">Target
                                            Learners</label>
                                        <p class="text-sm font-bold text-slate-700">
                                            @if ($selectedRequest->is_for_self)
                                                Self (Client)
                                            @else
                                                {{ collect($selectedRequest->learners)->map(fn($l) => is_array($l) ? $l['name'] ?? 'Unknown' : $l)->implode(', ') }}
                                            @endif
                                        </p>
                                    </div>
                                    @if ($selectedRequest->delivery_mode === 'online')
                                        <div class="pt-3 border-t border-slate-100">
                                            <label
                                                class="text-[10px] uppercase font-black text-slate-400 block mb-1">Address</label>
                                            <p class="text-xs font-medium text-slate-600 italic">
                                                <i class="fa-solid fa-location-dot mr-1 text-cyan-500"></i>
                                                Online (Virtual Tutoring)
                                            </p>
                                        </div>
                                    @else
                                        <div class="pt-3 border-t border-slate-100">
                                            <label
                                                class="text-[10px] uppercase font-black text-slate-400 block mb-1">Address</label>
                                            <p class="text-xs font-medium text-slate-600 italic">
                                                <i class="fa-solid fa-location-dot mr-1 text-cyan-500"></i>
                                                {{ collect([
                                                    $selectedRequest->lesson_address,
                                                    $selectedRequest->user?->userProfile?->city,
                                                    $selectedRequest->user?->userProfile?->state,
                                                ])->filter()->join(', ') }}

                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Commercials (4 Cols) --}}
                        <div class="lg:col-span-4 space-y-6">
                            <div
                                class="bg-cyan-600 rounded-2xl p-6 text-white shadow-xl shadow-cyan-100 relative overflow-hidden group">
                                <i class="fa-solid fa-naira-sign absolute -right-4 -bottom-4 text-8xl opacity-10"></i>
                                <span class="text-[10px] uppercase font-black opacity-80 block mb-1">Monthly
                                    Budget</span>
                                <p class="text-2xl font-black tracking-tighter">
                                    ₦{{ number_format($selectedRequest->budget_min) }} -
                                    ₦{{ number_format($selectedRequest->budget_max) }}
                                </p>
                                <div class="mt-4 pt-4 border-t border-white/20">
                                    <span class="text-[10px] uppercase font-bold opacity-80 block">Duration</span>
                                    <p class="text-sm font-black">{{ $selectedRequest->duration_per_session }} Minutes
                                        per session</p>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                                <label class="text-[10px] uppercase font-black text-slate-400 block mb-3">Tutor
                                    Preference</label>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600 border border-indigo-100 shrink-0">
                                        <i class="fa-solid fa-venus-mars"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800 uppercase">
                                            {{ $selectedRequest->preferred_tutor_gender }}</p>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase">Requested Gender</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Full Width: Notes --}}
                        <div class="lg:col-span-12">
                            <div class="bg-slate-800 rounded-2xl p-6 text-slate-300 relative">
                                <i class="fa-solid fa-quote-left absolute top-4 left-4 text-slate-700 text-4xl"></i>
                                <h4 class="text-[10px] uppercase font-black text-slate-500 mb-2 relative z-10">
                                    Additional Notes</h4>
                                <p class="text-sm italic leading-relaxed relative z-10 pl-4">
                                    {{ $selectedRequest->additional_notes ?? 'No special instructions provided.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-6 border-t border-slate-100 bg-white flex justify-end gap-3">
                    <button wire:click="closeModal"
                        class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl transition-all">
                        Close
                    </button>
                    <button wire:click="openEditModal({{ $selectedRequest->id }})"
                        class="px-8 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-cyan-100">
                        Update Status
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal --}}
    @if ($editModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div
                class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-white/20">
                <div class="bg-amber-500 px-6 py-4 text-white">
                    <h3 class="text-xl font-black italic tracking-tighter">UPDATE STATUS</h3>
                </div>
                <div class="p-6">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Select
                        Status</label>
                    <div class="relative">
                        <select wire:model="newStatus"
                            class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-amber-500 font-bold text-slate-700 appearance-none">
                            <option value="pending">Pending</option>
                            <option value="reviewing">Reviewing</option>
                            <option value="matched">Matched</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    @error('newStatus')
                        <span class="text-rose-500 text-xs mt-2 block font-bold">{{ $message }}</span>
                    @enderror

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button wire:click="updateRequest"
                            class="flex-1 py-3 bg-amber-500 text-white font-black rounded-xl hover:bg-amber-600 transition-all">SAVE</button>
                        <button wire:click="closeModal"
                            class="flex-1 py-3 bg-slate-100 text-slate-500 font-bold rounded-xl">CANCEL</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($deleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="relative bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border-t-8 border-rose-500">
                <div
                    class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-trash-can text-xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800">Delete Request?</h3>
                <p class="text-slate-500 mt-2 text-sm">This action is irreversible.</p>
                <div class="mt-6 flex flex-col gap-2">
                    <button wire:click="deleteRequest"
                        class="w-full py-3 bg-rose-500 text-white font-black rounded-xl hover:bg-rose-600 transition-all">DELETE</button>
                    <button wire:click="closeModal"
                        class="w-full py-3 bg-slate-100 text-slate-600 font-bold rounded-xl">KEEP</button>
                </div>
            </div>
        </div>
    @endif
</div>
