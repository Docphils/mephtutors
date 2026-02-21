<div class="relative p-2 sm:p-6 lg:p-8 bg-slate-50 min-h-screen text-slate-800 w-full">
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

            @if (session('success'))
                <div
                    class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-lg text-xs font-bold animate-fade-in-down">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-4 sm:mt-8">

        {{-- Filters --}}
        <div class="bg-white border border-slate-200 p-2 sm:p-4 rounded-xl sm:rounded-2xl shadow-sm mb-4 sm:mb-6">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                <nav class="flex overflow-x-auto no-scrollbar items-center p-1 bg-cyan-600 rounded-lg w-full lg:w-auto"
                    x-data="{ activeStatus: @entangle('status') }">
                    @foreach (['all', 'pending', 'matched', 'cancelled'] as $status)
                        <button wire:click.prevent="$set('status', '{{ $status }}')"
                            :class="activeStatus === '{{ $status }}' ? 'bg-white text-cyan-600 shadow-sm' :
                                'text-white hover:text-cyan-100'"
                            class="flex-1 lg:flex-none px-3 sm:px-5 py-1.5 text-[10px] sm:text-sm font-bold rounded-md transition-all whitespace-nowrap capitalize">
                            {{ $status }}
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
                            <th
                                class="px-3 sm:px-6 py-3 text-[9px] sm:text-[10px] font-black uppercase tracking-widest">
                                Client/Service</th>
                            <th class="hidden md:table-cell px-4 py-3 text-[10px] font-black uppercase tracking-widest">
                                Created</th>
                            <th class="hidden sm:table-cell px-4 py-3 text-[10px] font-black uppercase tracking-widest">
                                Location</th>
                            <th
                                class="px-2 sm:px-6 py-3 text-[9px] sm:text-[10px] font-black uppercase tracking-widest">
                                Status</th>
                            <th
                                class="px-3 sm:px-6 py-3 text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-right">
                                Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $request)
                            <tr wire:key="row-{{ $request->id }}" class="hover:bg-slate-50 transition-colors group">
                                <td class="px-3 sm:px-6 py-3 sm:py-4">
                                    <div
                                        class="font-bold text-slate-800 text-[11px] sm:text-sm truncate max-w-[100px] sm:max-w-none">
                                        {{ $request->user?->name ?? 'Guest' }}
                                    </div>
                                    <div
                                        class="text-[10px] sm:text-xs text-cyan-600 font-medium truncate max-w-[100px] sm:max-w-none">
                                        {{ $request->serviceItem?->name }}
                                    </div>
                                </td>

                                <td
                                    class="hidden md:table-cell px-4 py-4 whitespace-nowrap text-xs text-slate-600 italic">
                                    {{ $request->created_at->format('M d, Y') }}
                                </td>

                                <td class="hidden sm:table-cell px-4 py-4">
                                    <div class="flex items-center text-xs text-slate-600">
                                        <i class="fa-solid fa-location-dot mr-1.5 text-slate-300 text-[10px]"></i>
                                        <span class="truncate max-w-[120px]">
                                            {{ $request->delivery_mode === 'online'
                                                ? 'Online'
                                                : Str::limit(
                                                    $request->lesson_address ??
                                                        trim(
                                                            ($request->user->userProfile->address ?? '') .
                                                                ' ' .
                                                                ($request->user->userProfile->city ?? '') .
                                                                ' ' .
                                                                ($request->user->userProfile->state ?? ''),
                                                        ),
                                            
                                                    40,
                                                ) }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-2 sm:px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-[8px] sm:text-[10px] font-black uppercase tracking-tighter
                                        {{ in_array($request->status, ['matched', 'completed']) ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ in_array($request->status, ['pending', 'reviewing']) ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $request->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}">
                                        {{ $request->status }}
                                    </span>
                                </td>

                                <td class="px-3 sm:px-6 py-4 text-right">
                                    <div class="flex justify-end gap-0 sm:gap-1">
                                        <button wire:click.prevent="showRequest({{ $request->id }})"
                                            class="p-1.5 text-cyan-500 hover:bg-cyan-50 rounded-lg">
                                            <i class="fa-solid fa-eye text-xs sm:text-sm"></i>
                                        </button>
                                        <button wire:click.prevent="openEditModal({{ $request->id }})"
                                            class="p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg">
                                            <i class="fa-solid fa-pen-to-square text-xs sm:text-sm"></i>
                                        </button>
                                        <button wire:click.prevent="openDeleteModal({{ $request->id }})"
                                            class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg">
                                            <i class="fa-solid fa-trash-can text-xs sm:text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <i class="fa-solid fa-inbox text-2xl text-slate-200 mb-2 block"></i>
                                    <span class="text-slate-400 font-medium text-xs">No requests found.</span>
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div
                class="relative bg-white rounded-3xl sm:rounded-[2.5rem] shadow-2xl w-full max-w-3xl max-h-[95vh] overflow-y-auto border border-white/20">
                <div
                    class="bg-cyan-600 px-6 py-4 sm:px-8 sm:py-6 text-white flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-lg sm:text-2xl font-black italic tracking-tighter uppercase">Request Profile</h3>
                    <span
                        class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-bold uppercase">{{ $selectedRequest->status }}</span>
                </div>

                <div class="p-4 sm:p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div class="bg-slate-50 p-4 rounded-2xl">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Scheduling</label>
                            <p class="text-sm font-bold text-slate-700 italic">
                                <i
                                    class="fa-solid fa-calendar-days mr-2 text-cyan-500"></i>{{ $selectedRequest->sessions_per_week }}
                                Sessions/Week
                            </p>
                            <p class="text-sm font-bold text-slate-400 italic mt-1">
                                <i class="fa-regular fa-clock mr-2"></i>{{ $selectedRequest->duration_per_session }}
                                Mins each
                            </p>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-2xl">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Logistics</label>
                            <p class="text-sm font-bold text-slate-700">
                                <i
                                    class="fa-solid fa-truck-ramp-box mr-2 text-cyan-500"></i>{{ ucfirst($selectedRequest->delivery_mode) }}
                            </p>
                            <p class="text-xs font-bold text-slate-700 mt-1">
                                <i
                                    class="fa-solid fa-map-pin mr-2 text-cyan-500"></i>{{ $selectedRequest->lesson_address
                                        ? $selectedRequest->lesson_address
                                        : $request->lesson_address ??
                                            (trim(
                                                ($request->user->userProfile->address . ',' ?? '') .
                                                    ' ' .
                                                    ($request->user->userProfile->city . ',' ?? '') .
                                                    ' ' .
                                                    ($request->user->userProfile->state ?? ''),
                                            ) ??
                                                'Online') }}
                            </p>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-2xl">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase mb-2">Preferences</label>
                            <p class="text-sm font-bold text-slate-700">
                                <i
                                    class="fa-solid fa-venus-mars mr-2 text-cyan-500"></i>{{ ucfirst($selectedRequest->preferred_tutor_gender) }}
                            </p>
                            <p class="text-sm font-bold text-slate-700 mt-1">
                                <i
                                    class="fa-solid fa-people-group mr-2 text-cyan-500"></i>{{ ucfirst($selectedRequest->session_type) }}
                            </p>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-slate-100 p-4 rounded-2xl">
                                <h4 class="text-[10px] font-black text-slate-800 uppercase mb-2 flex items-center">
                                    <i class="fa-solid fa-graduation-cap mr-2 text-cyan-600"></i> Service Details
                                </h4>
                                <p class="text-slate-600 text-sm font-medium">
                                    {{ $selectedRequest->serviceItem?->name }} -
                                    {{ $selectedRequest->level?->name ?? 'N/A' }}
                                </p>
                                <p class="mt-2 text-cyan-700 text-sm font-bold">Client:
                                    {{ $selectedRequest->user?->name }}</p>
                            </div>
                            <div class="border border-slate-100 p-4 rounded-2xl">
                                <h4 class="text-[10px] font-black text-slate-800 uppercase mb-2 flex items-center">
                                    <i class="fa-solid fa-receipt mr-2 text-cyan-600"></i> Budget (Max)
                                </h4>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <p class="text-2xl font-black text-slate-800">
                                            ₦{{ number_format($selectedRequest->budget_max, 2) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $selectedRequest->examType?->name ?? 'General' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-slate-900 text-slate-300 p-5 rounded-2xl relative overflow-hidden">
                        <h4 class="text-[10px] font-black text-cyan-400 uppercase mb-2">Additional Notes</h4>
                        <p class="text-sm italic leading-relaxed">
                            {{ $selectedRequest->additional_notes ?? 'No extra notes.' }}</p>
                    </div>

                    <div class="mt-6">
                        <button wire:click="closeModal"
                            class="w-full py-3 bg-slate-100 text-slate-600 font-black rounded-xl hover:bg-slate-200 transition-all uppercase tracking-widest text-xs">
                            Dismiss
                        </button>
                    </div>
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
