<div class="p-6 max-w-7xl mx-auto space-y-6 text-gray-900">
    <x-slot name="header">
        <div
            class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Institution <span
                        class="text-cyan-600">Bookings</span></h1>
                <p class="text-slate-500 text-sm">Manage your corporate and school-level tutor deployments.</p>
            </div>
            <button x-on:click="$dispatch('openCrmCreate')"
                class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl font-bold transition-all shadow-lg shadow-cyan-100 flex items-center gap-2 text-sm ">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Booking Request
            </button>
        </div>
    </x-slot>

    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex flex-col lg:flex-row gap-4 items-center">
        <div class="relative w-full lg:flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Search by institution name or requirements..."
                class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-cyan-500 transition-all text-sm">
        </div>

        <div class="flex flex-wrap md:flex-nowrap gap-3 w-full lg:w-auto">
            <div
                class="flex items-center gap-2 bg-slate-50 px-3 rounded-xl border border-transparent focus-within:border-cyan-500 focus-within:bg-white transition-all w-full md:w-48">
                <i class="fa-solid fa-filter text-slate-400 text-xs"></i>
                <select wire:model.live="statusFilter"
                    class="bg-transparent border-none focus:ring-0 text-sm py-2.5 w-full">
                    <option value="">All Statuses</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="approved">Approved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <div
                class="flex items-center gap-2 bg-slate-50 px-3 rounded-xl border border-transparent focus-within:border-cyan-500 focus-within:bg-white transition-all w-full md:w-48">
                <i class="fa-solid fa-sort text-slate-400 text-xs"></i>
                <select wire:model.live="sortBy" class="bg-transparent border-none focus:ring-0 text-sm py-2.5 w-full">
                    <option value="created_at">Latest First</option>
                    <option value="institution_name">Institution A-Z</option>
                    <option value="status">Status</option>
                </select>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-cyan-50 border border-cyan-100 text-cyan-700 rounded-2xl font-bold animate-pulse">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($items as $i)
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm hover:shadow-xl transition-all p-6 group">
                <div class="flex justify-between items-center mb-4 bg-cyan-600 p-2 rounded text-white">
                    <span
                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                        {{ $i->status === 'new' ? 'bg-cyan-50 text-cyan-600' : 'bg-slate-100 text-slate-600' }}">
                        {{ str_replace('_', ' ', $i->status) }}
                    </span>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="openEdit({{ $i->id }})"
                            class="p-2 bg-slate-50 text-slate-600 hover:text-cyan-600 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button wire:confirm="Delete this request?" wire:click="delete({{ $i->id }})"
                            class="p-2 bg-slate-50 text-slate-600 hover:text-red-600 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <h3 class="text-xl font-bold text-slate-800 leading-tight mb-1">{{ $i->institution_name }}</h3>
                <p class="text-xs text-slate-600 font-medium mb-4">{{ $i->created_at->diffForHumans() }}</p>

                <div class="space-y-3 mb-6">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        @if ($i->serviceItem)
                            {{ $i->serviceItem->name }}
                        @else
                            <span class="text-red-500 text-xs italic">Service removed</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500 line-clamp-2 italic">"{{ $i->requirements }}"</p>
                </div>

                <button wire:click="openView({{ $i->id }})"
                    class="w-full py-3 rounded-xl bg-cyan-50 hover:bg-cyan-600 hover:text-white text-slate-600 font-bold text-sm transition-all">
                    View Proposal Details
                </button>
            </div>
        @empty
            <div
                class="col-span-full py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                <p class="text-slate-600 font-medium">No institution requests yet.</p>
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl h-5/6 overflow-y-auto">
                <div class="px-8 py-6 border-b border-cyan-800 flex justify-between items-center">
                    <h2 class="text-2xl font-black text-cyan-800">{{ $isEditing ? 'Update' : 'Create' }} Institution
                        Request</h2>
                    <button wire:click="$set('showModal', false)"
                        class="p-2 hover:bg-red-200 hover:text-white rounded-full transition-all">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="p-8 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Institution
                            Name</label>
                        <input wire:model="institution_name" type="text"
                            class="w-full bg-slate-50 border-transparent rounded-2xl py-3 focus:bg-white focus:ring-2 focus:ring-cyan-500 transition-all text-gray-900">
                        @error('institution_name')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Service
                            Required</label>
                        <select wire:model="service_item_id"
                            class="w-full bg-slate-50 border-transparent rounded-2xl py-3 focus:ring-2 focus:ring-cyan-500 text-gray-900">
                            <option value="">Select Service</option>
                            @foreach ($serviceItems as $si)
                                <option value="{{ $si->id }}">{{ $si->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Tutors
                            Needed</label>
                        <input wire:model="number_of_tutors_required" type="number"
                            class="w-full bg-slate-50 border-transparent rounded-2xl py-3 text-gray-900">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Delivery
                            Mode</label>
                        <select wire:model="delivery_mode"
                            class="w-full bg-slate-50 border-transparent rounded-2xl py-3 text-gray-900">
                            <option value="onsite">Onsite</option>
                            <option value="online">Online</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Engagement
                            Type</label>
                        <select wire:model="engagement_type"
                            class="w-full bg-slate-50 border-transparent rounded-2xl py-3 text-gray-900">
                            <option value="">Select Type</option>
                            <option value="short_term">Short Term</option>
                            <option value="long_term">Long Term</option>
                            <option value="contract">Contract</option>
                            <option value="club_management">Club Management</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Institution
                            Address</label>
                        <input wire:model="institution_address" type="text"
                            class="w-full bg-slate-50 border-transparent rounded-2xl py-3 text-gray-900">
                    </div>

                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Detailed
                            Requirements</label>
                        <textarea wire:model="requirements" rows="3"
                            class="w-full bg-slate-50 border-transparent rounded-2xl py-3 text-gray-900"></textarea>
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-4 mt-4">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="px-6 py-3 font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                        <button type="submit"
                            class="px-10 py-3 bg-cyan-600 text-white font-black rounded-xl hover:bg-cyan-700 shadow-xl transition-all">
                            {{ $isEditing ? 'Save Changes' : 'Post Request' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDetails && $activeRequest)
        <div class="fixed inset-0 z-[80] overflow-hidden">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showDetails', false)">
            </div>
            <div class="absolute inset-y-0 right-0 max-w-full flex">
                <div
                    class="w-screen max-w-md bg-white shadow-2xl flex flex-col animate-slide-in-right overflow-y-auto">
                    <div class="p-8 bg-cyan-900 text-white">
                        <div class="flex justify-between items-start mb-6">
                            <h2 class="text-2xl font-black">{{ $activeRequest->institution_name }}</h2>
                            <button wire:click="$set('showDetails', false)" class="text-cyan-200 hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <span
                            class="px-3 py-1 bg-cyan-500 rounded-full text-[10px] font-bold uppercase tracking-widest">
                            ID: #INST-{{ $activeRequest->id }}
                        </span>
                    </div>

                    <div class="p-8 flex-1 overflow-y-auto space-y-8">
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-600 uppercase tracking-widest block mb-2">Service
                                Breakdown</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-slate-50 rounded-2xl">
                                    <p class="text-[10px] text-slate-600 font-bold uppercase">Tutors</p>
                                    <p class="font-black text-slate-800 text-lg">
                                        {{ $activeRequest->number_of_tutors_required }}</p>
                                </div>
                                <div class="p-4 bg-slate-50 rounded-2xl">
                                    <p class="text-[10px] text-slate-600 font-bold uppercase">Delivery</p>
                                    <p class="font-black text-slate-800 text-lg uppercase">
                                        {{ $activeRequest->delivery_mode }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                class="text-[10px] font-black text-slate-600 uppercase tracking-widest block mb-2">Location</label>
                            <p class="text-slate-700 font-medium leading-relaxed">
                                {{ $activeRequest->institution_address }}</p>
                        </div>

                        <div>
                            <label
                                class="text-[10px] font-black text-slate-600 uppercase tracking-widest block mb-2">Requirements</label>
                            <div
                                class="p-6 border-l-4 border-cyan-500 bg-cyan-50/30 rounded-r-2xl italic text-slate-600">
                                "{{ $activeRequest->requirements }}"
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100">
                            <p class="text-[10px] text-slate-600 font-bold uppercase mb-2">Timeline Status</p>
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full bg-cyan-500"></div>
                                <p class="text-sm font-bold text-slate-800">Currently in {{ $activeRequest->status }}
                                    phase</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
