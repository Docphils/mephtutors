<div class="p-6 bg-cyan-100 min-h-screen">
    {{-- Header Section --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-cyan-200">
                    <i class="fa-solid fa-building-shield text-xl"></i>
                </div>
                <div>
                    <h2 class="font-black text-2xl lg:text-3xl text-slate-800 tracking-tight">Institution <span
                            class="text-cyan-600">Requests</span></h2>
                    <p class="text-slate-500 text-sm font-medium">Manage Schools and Institutional service requests.
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Filters Card --}}
    <div
        class="bg-white/80 backdrop-blur-md rounded-[2.5rem] p-4 mb-6 border border-white shadow-sm grid md:grid-cols-2 items-center gap-4">
        <div class="flex-1 relative group">
            <i
                class="fa fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-cyan-500 transition-colors"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Search by Institution, Client or Service..."
                class="w-full pl-11 text-sm pr-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
        </div>

        <select wire:model.live="status"
            class="bg-slate-50 border-transparent rounded-2xl px-6 py-3 focus:ring-2 focus:ring-cyan-500">
            <option value="all">All Statuses</option>
            <option value="new">New</option>
            <option value="contacted">Contacted</option>
            <option value="proposal_sent">Proposal Sent</option>
            <option value="negotiating">Negotiating</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="deployed">Deployed</option>
            <option value="closed">Closed</option>
        </select>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 px-3 py-2 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-lg text-xs font-bold">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 px-3 py-2 bg-rose-50 border border-rose-100 text-rose-700 rounded-lg text-xs font-bold">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Data Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($requests as $req)
            <div
                class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 hover:shadow-xl hover:shadow-cyan-900/5 transition-all group">
                <div class="flex justify-between items-start mb-4">
                    <span
                        class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full 
                        {{ $req->status == 'new' ? 'bg-emerald-100 text-emerald-600' : 'bg-cyan-100 text-cyan-600' }}">
                        {{ str_replace('_', ' ', $req->status) }}
                    </span>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="viewRequest({{ $req->id }})"
                            class="p-2 hover:bg-cyan-50 text-cyan-600 rounded-xl transition-colors"><i
                                class="fa fa-eye"></i></button>
                        <button wire:click="openEdit({{ $req->id }})"
                            class="p-2 hover:bg-amber-50 text-amber-600 rounded-xl transition-colors"><i
                                class="fa fa-edit"></i></button>
                        <button wire:click="confirmDelete({{ $req->id }})"
                            class="p-2 hover:bg-red-50 text-red-600 rounded-xl transition-colors"><i
                                class="fa fa-trash"></i></button>
                    </div>
                </div>

                <h4 class="font-black text-slate-800 text-lg mb-1">{{ $req->institution_name }}</h4>
                <p class="text-slate-500 text-sm mb-4 flex items-center gap-2">
                    <i class="fa fa-location-dot text-cyan-500"></i> {{ Str::limit($req->institution_address, 40) }}
                </p>

                <div class="bg-slate-50 rounded-2xl p-4 space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400 font-bold uppercase tracking-tighter">Service Category</span>
                        <span class="text-slate-700 font-bold">{{ $req->serviceItem?->service?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-400 font-bold uppercase tracking-tighter">Service Item</span>
                        <span class="text-cyan-600 font-bold">{{ $req->serviceItem?->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs border-t border-slate-200 pt-2">
                        <span class="text-slate-400 font-bold uppercase tracking-tighter">Tutors Required</span>
                        <span class="text-slate-700 font-bold">{{ $req->number_of_tutors_required }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs border-t border-slate-200 pt-2">
                        <span class="text-slate-400 font-bold uppercase tracking-tighter">Payment</span>
                        <span class="text-slate-700 font-bold uppercase">{{ $req->payment_status }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div
                class="col-span-full py-20 text-center bg-white/50 rounded-[3rem] border-2 border-dashed border-cyan-200">
                <i class="fa-solid fa-folder-open text-5xl text-cyan-200 mb-4"></i>
                <p class="text-slate-500 font-bold">No institution requests found.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $requests->links() }}
    </div>

    {{-- Detail View Modal (Expanded Booking Manager Style) --}}
    @if ($showDetail && $selectedRequest)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-[3rem] shadow-2xl max-w-3xl w-full overflow-hidden">
                {{-- Modal Header --}}
                <div class="bg-cyan-600 p-8 text-white relative">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="px-2 py-0.5 bg-white/20 rounded text-[10px] font-bold uppercase tracking-widest">
                                    ID: #CRM-{{ str_pad($selectedRequest->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                                <span
                                    class="px-2 py-0.5 bg-cyan-400/30 rounded text-[10px] font-bold uppercase tracking-widest">
                                    {{ str_replace('_', ' ', $selectedRequest->status) }}
                                </span>
                            </div>
                            <p class="text-cyan-100 text-xs font-bold uppercase tracking-widest">Institution Request
                                Detail</p>
                            <h3 class="text-3xl font-black leading-tight">{{ $selectedRequest->institution_name }}</h3>
                        </div>
                        <button wire:click="$set('showDetail', false)"
                            class="text-white/50 hover:text-white transition-colors">
                            <i class="fa fa-times text-2xl"></i>
                        </button>
                    </div>
                </div>

                <div class="p-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        {{-- Section: Client Information --}}
                        <div class="space-y-6">
                            <h4
                                class="text-cyan-600 font-black text-xs uppercase tracking-[0.2em] border-b border-slate-100 pb-2">
                                Primary Contact</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                                        <i class="fa fa-user"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                            Contact Name</p>
                                        <p class="text-slate-800 font-bold">{{ $selectedRequest->user->name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                                        <i class="fa fa-envelope"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Email
                                            Address</p>
                                        <p class="text-slate-800 font-bold">{{ $selectedRequest->user->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                                        <i class="fa fa-phone"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Phone
                                            Number</p>
                                        <p class="text-slate-800 font-bold">
                                            {{ $selectedRequest->user->userProfile->phone ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Service Summary --}}
                        <div class="space-y-6">
                            <h4
                                class="text-cyan-600 font-black text-xs uppercase tracking-[0.2em] border-b border-slate-100 pb-2">
                                Service Details</h4>
                            <div class="bg-slate-50 rounded-3xl p-5 space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Category</span>
                                    <span
                                        class="text-slate-800 font-bold">{{ $selectedRequest->serviceItem->service->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Specific
                                        Service</span>
                                    <span
                                        class="text-cyan-600 font-bold">{{ $selectedRequest->serviceItem->name }}</span>
                                </div>
                                <div class="flex justify-between border-t border-slate-200 pt-3">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Delivery Mode</span>
                                    <span
                                        class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded text-[10px] font-bold uppercase">
                                        {{ $selectedRequest->delivery_mode }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Engagement Specs --}}
                        <div
                            class="col-span-1 md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4 bg-cyan-50/50 p-6 rounded-[2rem] border border-cyan-100">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase">Engagement</label>
                                <p class="text-slate-800 font-bold text-sm">
                                    {{ str_replace('_', ' ', $selectedRequest->engagement_type) }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase">Tutors Req.</label>
                                <p class="text-slate-800 font-black text-lg">
                                    {{ $selectedRequest->number_of_tutors_required }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase">Sessions / Week</label>
                                <p class="text-slate-800 font-black text-lg">{{ $selectedRequest->sessions_per_week }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase">Created Date</label>
                                <p class="text-slate-800 font-bold text-sm">
                                    {{ $selectedRequest->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-slate-50 rounded-2xl p-4">
                                <p class="text-[10px] font-black text-slate-400 uppercase">Quote Amount</p>
                                <p class="text-lg font-black text-slate-800">
                                    {{ $selectedRequest->quote_amount ? '₦' . number_format($selectedRequest->quote_amount, 2) : 'Not set' }}
                                </p>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4">
                                <p class="text-[10px] font-black text-slate-400 uppercase">Payment Status</p>
                                <p class="text-lg font-black text-slate-800 uppercase">
                                    {{ $selectedRequest->payment_status }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4">
                                <p class="text-[10px] font-black text-slate-400 uppercase">Payment Ref</p>
                                <p class="text-sm font-bold text-slate-800">
                                    {{ $selectedRequest->payment_reference ?: 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-span-1 md:col-span-2 space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Quote
                                Notes</label>
                            <div class="bg-slate-50 p-4 rounded-2xl text-sm text-slate-700">
                                {{ $selectedRequest->quote_notes ?: 'No quote note available.' }}
                            </div>
                        </div>

                        <div class="col-span-1 md:col-span-2 space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Contract
                                Terms</label>
                            <div class="bg-slate-50 p-4 rounded-2xl text-sm text-slate-700 whitespace-pre-line">
                                {{ $selectedRequest->contract_terms ?: 'No contract term recorded yet.' }}
                            </div>
                        </div>

                        {{-- Section: Address & Requirements --}}
                        <div class="col-span-1 md:col-span-2 space-y-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Full
                                    Institution Address</label>
                                <div
                                    class="flex gap-2 items-start text-slate-700 bg-white border border-slate-100 p-4 rounded-2xl shadow-sm">
                                    <i class="fa fa-map-marker-alt text-cyan-500 mt-1"></i>
                                    <p class="font-medium">{{ $selectedRequest->institution_address }}</p>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Additional
                                    Requirements</label>
                                <div
                                    class="bg-slate-800 text-slate-200 p-6 rounded-[2rem] text-sm leading-relaxed italic shadow-inner">
                                    <i class="fa fa-quote-left text-slate-600 mb-2 block"></i>
                                    {{ $selectedRequest->requirements ?: 'No additional requirements or custom remarks were provided for this request.' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-span-1 md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Assigned
                                Team</label>
                            <div class="bg-slate-50 rounded-2xl p-4">
                                @forelse ($selectedRequest->assignments as $assignment)
                                    <div
                                        class="flex items-center justify-between py-2 border-b border-slate-200 last:border-b-0">
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">
                                                {{ $assignment->assignee?->name }}</p>
                                            <p class="text-[10px] uppercase font-black text-slate-400">
                                                {{ $assignment->role }} | {{ $assignment->status }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $assignment->assignee?->email }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">No assignments yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer Actions --}}
                <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-between items-center">
                    <p class="text-[10px] text-slate-400 font-medium">Last updated:
                        {{ $selectedRequest->updated_at->diffForHumans() }}</p>
                    <div class="flex gap-3">
                        <button wire:click="closeModal"
                            class="px-6 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-100 transition-all">
                            Close
                        </button>
                        <button wire:click="openEdit({{ $selectedRequest->id }})"
                            class="px-6 py-2 bg-cyan-600 text-white font-bold rounded-xl shadow-lg shadow-cyan-200 hover:bg-cyan-700 transition-all">
                            Update Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- Edit Status Modal --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm">
            </div>
            <div class="relative bg-white rounded-[2rem] p-8 max-w-3xl w-full shadow-xl max-h-[90vh] overflow-y-auto">
                <button wire:click="closeModal" class="absolute top-4 right-4 text-slate-500 hover:text-slate-800">
                    <i class="fa fa-times text-lg"></i>
                </button>
                <h3 class="text-xl font-black text-slate-800 mb-6">Update Request Status</h3>
                <div class="space-y-5">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Select New
                            Status</label>
                        <select wire:model.defer="newStatus"
                            class="w-full mt-1 bg-slate-50 border-transparent rounded-2xl py-3 focus:ring-2 focus:ring-cyan-500">
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="proposal_sent">Proposal Sent</option>
                            <option value="negotiating">Negotiating</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="deployed">Deployed</option>
                            <option value="closed">Closed</option>
                        </select>
                        @error('newStatus')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Quote
                                Amount</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="quoteAmount"
                                class="w-full mt-1 bg-slate-50 border-transparent rounded-2xl py-3 focus:ring-2 focus:ring-cyan-500">
                            @error('quoteAmount')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Payment
                                Status</label>
                            <select wire:model.defer="paymentStatus"
                                class="w-full mt-1 bg-slate-50 border-transparent rounded-2xl py-3 focus:ring-2 focus:ring-cyan-500">
                                <option value="pending">Pending</option>
                                <option value="part_paid">Part Paid</option>
                                <option value="paid">Paid</option>
                                <option value="waived">Waived</option>
                            </select>
                            @error('paymentStatus')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Payment
                            Reference</label>
                        <input type="text"
                            value="{{ $selectedRequest?->payment_reference ?: 'Auto-generated on quote save' }}"
                            disabled
                            class="w-full mt-1 bg-slate-100 border-transparent rounded-2xl py-3 text-slate-500">
                    </div>

                    <div wire:ignore wire:key="quote-trix-{{ $selectedRequest?->id }}">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Quote
                            Notes</label>
                        <input id="quote_notes_editor_{{ $selectedRequest?->id }}" type="hidden" value="{{ $quoteNotes }}">
                        <trix-editor input="quote_notes_editor_{{ $selectedRequest?->id }}"
                            class="trix-content w-full mt-1 bg-slate-50 border-transparent rounded-2xl text-sm p-3 min-h-[140px]"
                            x-data
                            x-on:trix-change.debounce.500ms="$wire.set('quoteNotes', $event.target.value)">
                        </trix-editor>
                    </div>

                    <div wire:ignore wire:key="contract-trix-{{ $selectedRequest?->id }}">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Contract
                            Terms</label>
                        <input id="contract_terms_editor_{{ $selectedRequest?->id }}" type="hidden" value="{{ $contractTerms }}">
                        <trix-editor input="contract_terms_editor_{{ $selectedRequest?->id }}"
                            class="trix-content w-full mt-1 bg-slate-50 border-transparent rounded-2xl text-sm p-3 min-h-[170px]"
                            x-data
                            x-on:trix-change.debounce.500ms="$wire.set('contractTerms', $event.target.value)">
                        </trix-editor>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-700 mb-3">Tutor Assignment
                            (Standalone)</h4>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Assign
                            Tutors</label>
                        <select wire:model.defer="selectedTutors" multiple
                            class="w-full mt-1 bg-slate-50 border-transparent rounded-2xl py-3 focus:ring-2 focus:ring-cyan-500 min-h-32">
                            @foreach ($availableTutors as $tutor)
                                <option value="{{ $tutor->id }}">{{ $tutor->name }} ({{ $tutor->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Hold Ctrl/Cmd to select multiple tutors.</p>
                        @error('selectedTutors')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        @error('selectedTutors.*')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Assignment
                            Notes</label>
                        <textarea wire:model.defer="assignmentNotes" rows="2"
                            class="w-full mt-1 bg-slate-50 border-transparent rounded-2xl py-3 focus:ring-2 focus:ring-cyan-500"
                            placeholder="Onboarding instruction or deployment context"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="updateStatus" wire:loading.attr="disabled" wire:target="updateStatus"
                            class="w-full py-4 bg-cyan-600 text-white font-bold rounded-2xl shadow-lg shadow-cyan-200 hover:bg-cyan-700 transition-all">
                            Save Client Stage
                        </button>
                        <button wire:click="saveTutorAssignments" wire:loading.attr="disabled"
                            wire:target="saveTutorAssignments"
                            class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">
                            Save Tutor Assignment
                        </button>
                    </div>
                    <div>
                        <button wire:click="closeModal" wire:loading.attr="disabled"
                            class="w-full py-4 bg-slate-200 text-slate-700 font-bold rounded-2xl hover:bg-slate-300 transition-all">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="bg-red-100 p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-red-700 mb-4">Delete Request</h2>
                <p class="text-gray-700 mb-6">Are you sure you want to delete this request? This action cannot be
                    undone.</p>

                <div class="flex justify-end space-x-4">
                    <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Yes, Delete
                    </button>
                    <button wire:click="closeModal"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
