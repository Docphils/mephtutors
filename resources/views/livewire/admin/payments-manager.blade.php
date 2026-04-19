<div class="p-4 sm:p-6 bg-cyan-100 min-h-screen rounded">
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <div class="flex items-center gap-3 sm:gap-4">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="group flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200 text-cyan-600 rounded-xl shadow-sm hover:bg-cyan-600 hover:text-white transition-all shrink-0">
                    <i class="fa fa-arrow-left transition-transform group-hover:-translate-x-1 text-xs sm:text-sm"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Tutor <span
                            class="text-cyan-600">Payments</span></h2>
                    <p class="text-slate-500 text-sm font-medium">Manage disbursements and resolve tutor disputes.</p>
                </div>
            </div>
            <button x-on:click="$dispatch('create-payment')"
                class="flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-cyan-200 transition-all">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Add Payment Record</span>
            </button>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
        <div class="lg:col-span-2 relative">
            <input type="text" wire:model.live="search" placeholder="Search by amount or tutor name..."
                class="w-full pl-12 pr-4 py-4 bg-white border-none rounded-2xl shadow-sm focus:ring-4 focus:ring-cyan-500/10 font-medium text-slate-600 placeholder:text-slate-400">
            <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
        </div>
        <select wire:model.live="status"
            class="w-full px-5 py-4 bg-white border-none rounded-2xl shadow-sm focus:ring-4 focus:ring-cyan-500/10 font-bold text-slate-600">
            <option value="">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Earned">Earned</option>
            <option value="Paid">Paid</option>
            <option value="Cancelled">Cancelled</option>
            <option value="Disputed">⚠️ Disputed Only</option>
        </select>
    </div>

    <div class="bg-white rounded-[2rem] shadow-xl shadow-cyan-900/5 border border-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tutor &
                            Booking</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Dispute
                        </th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($payments as $payment)
                        <tr class="group hover:bg-cyan-50/30 transition-colors">
                            <td class="px-8 py-6">
                                <div class="font-black text-slate-800">{{ $payment->tutor->name ?? 'N/A' }}</div>
                                @if ($payment->booking_id)
                                    <div class="text-xs text-cyan-600 font-bold">Booking #{{ $payment->booking_id }}</div>
                                @elseif ($payment->programme_enquiry_assignment_id)
                                    <div class="text-xs text-cyan-600 font-bold">Intervention Assignment #{{ $payment->programme_enquiry_assignment_id }}</div>
                                @else
                                    <div class="text-xs text-slate-400 font-bold">Unlinked payment record</div>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-sm font-black text-slate-700">₦{{ number_format($payment->amount, 2) }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">
                                    {{ $payment->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                    {{ $payment->status === 'Paid' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $payment->status === 'Earned' ? 'bg-cyan-100 text-cyan-700' : '' }}
                                    {{ $payment->status === 'Pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $payment->status === 'Cancelled' ? 'bg-rose-100 text-rose-700' : '' }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                @if ($payment->dispute)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                        {{ $payment->dispute['status'] === 'Pending' ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-slate-100 text-slate-500' }}">
                                        <i class="fa-solid fa-circle text-[6px]"></i>
                                        {{ $payment->dispute['status'] }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-slate-300 font-bold uppercase">None</span>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="showPayment({{ $payment->id }})"
                                        class="p-2 text-slate-400 hover:text-cyan-600 transition-colors"><i
                                            class="fa-solid fa-eye"></i></button>
                                    @if ($payment->dispute)
                                        <button wire:click="resolveDispute({{ $payment->id }})"
                                            class="p-2 text-rose-400 hover:text-rose-600 transition-colors"
                                            title="Resolve Dispute"><i class="fa-solid fa-gavel"></i></button>
                                    @endif
                                    <button wire:click="edit({{ $payment->id }})"
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                        title="Edit"><i class="fa fa-edit"></i></button>
                                    <button wire:click="openDelete({{ $payment->id }})"
                                        class="p-2 text-slate-400 hover:text-rose-600 transition-colors"><i
                                            class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <p class="text-slate-400 font-medium">No payment records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
    </div>

    @if ($showDisputeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModals"></div>
            <div class="relative bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl overflow-hidden">
                <div class="absolute top-0 right-0 p-8">
                    <button wire:click="closeModals" class="text-slate-300 hover:text-slate-500 transition-colors"><i
                            class="fa-solid fa-xmark text-2xl"></i></button>
                </div>

                <div
                    class="w-20 h-20 bg-rose-50 text-rose-500 rounded-3xl flex items-center justify-center mb-8 text-3xl">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>

                <h3 class="text-3xl font-black text-slate-800 mb-2">Resolve Dispute</h3>
                <p class="text-slate-500 text-sm mb-8">Review the tutor's complaint and provide a final resolution
                    record.</p>

                <div class="bg-slate-50 rounded-2xl p-5 mb-8 border border-slate-100">
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Tutor's
                        Reason</label>
                    <p class="text-sm text-slate-700 italic">"{{ $selectedPayment->dispute['reason'] }}"</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase mb-3 ml-2 tracking-widest">Admin
                            Response</label>
                        <textarea wire:model="adminResponse" rows="4"
                            class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500 text-sm"
                            placeholder="Explain the outcome of the investigation..."></textarea>
                        @error('adminResponse')
                            <span class="text-rose-500 text-[10px] font-bold mt-2 ml-2 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase mb-3 ml-2 tracking-widest">New
                            Dispute Status</label>
                        <select wire:model="disputeStatus"
                            class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500 font-bold text-slate-700">
                            <option value="Pending">Keep as Pending</option>
                            <option value="Resolved">Mark as Resolved</option>
                            <option value="Rejected">Reject Dispute</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 mt-10">
                    <button wire:click="closeModals"
                        class="flex-1 py-4 font-black text-slate-400 bg-slate-50 rounded-2xl hover:bg-slate-100 transition-all">Cancel</button>
                    <button wire:click="submitResolution"
                        class="flex-1 py-4 font-black text-white bg-cyan-600 rounded-2xl shadow-lg shadow-cyan-200 hover:bg-cyan-700 transition-all">
                        Save Resolution
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modals: Integrated into a common rounded-3xl style --}}
    @if ($showModal && $selectedPayment)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full overflow-hidden">
                <div class="bg-cyan-600 p-6 text-white text-center">
                    <h3 class="text-xl font-black">Payment Details</h3>
                    <p class="text-cyan-100 text-xs uppercase tracking-widest font-bold">Ref:
                        #PAY-{{ $selectedPayment->id }}</p>
                </div>

                <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto">
                    {{-- Basic Header --}}
                    <div class="flex justify-between border-b border-slate-100 pb-4">
                        <div class="text-center flex-1 border-r border-slate-100 px-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Tutor</p>
                            <p class="font-bold text-slate-800">
                                {{ $selectedPayment->tutor?->tutorProfile?->fullName }}</p>
                            <p class="text-[10px] text-slate-500">
                                {{ $selectedPayment->tutor?->tutorProfile?->phone ?? 'No Phone' }}</p>
                        </div>
                        <div class="text-center flex-1 px-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Amount</p>
                            <p class="font-black text-cyan-600 text-lg">
                                ₦{{ number_format((float) $selectedPayment->amount, 2) }}
                            </p>
                        </div>
                    </div>

                    {{-- Booking / Intervention Details Section --}}
                    <div class="space-y-3">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Context</h4>
                        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">Client</p>
                                <p class="text-xs font-bold text-slate-700">
                                    {{ $selectedPayment->booking?->client?->name ?? $selectedPayment->programmeAssignment?->programmeEnquiry?->user?->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase">Type</p>
                                <p class="text-xs font-bold text-slate-700">
                                    {{ $selectedPayment->booking_id ? 'Lesson Booking' : ($selectedPayment->programme_enquiry_assignment_id ? 'Intervention Assignment' : 'N/A') }}
                                </p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[9px] font-bold text-slate-400 uppercase">Service / Location</p>
                                <p class="text-xs font-bold text-slate-700">
                                    {{ $selectedPayment->booking?->serviceItem?->name ?? $selectedPayment->programmeAssignment?->programmeEnquiry?->programme?->name ?? 'N/A' }}
                                    @if ($selectedPayment->booking?->location || $selectedPayment->programmeAssignment?->programmeEnquiry?->city_area)
                                        |
                                        {{ $selectedPayment->booking?->location ?? ($selectedPayment->programmeAssignment?->programmeEnquiry?->city_area . ', ' . $selectedPayment->programmeAssignment?->programmeEnquiry?->state) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Tutor Profile / Bank Section --}}
                    @if ($selectedPayment->tutor?->tutorProfile)
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Tutor Bank
                                Details</h4>
                            <div class="bg-cyan-50/50 p-4 rounded-2xl border border-cyan-100/50">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase">Bank</p>
                                        <p class="text-xs font-black text-slate-800">
                                            {{ $selectedPayment->tutor?->tutorProfile?->bankName ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase">Account Number</p>
                                        <p class="text-xs font-black text-cyan-700">
                                            {{ $selectedPayment->tutor?->tutorProfile?->accountNumber ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-span-2 mt-1">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase">Account Name</p>
                                        <p class="text-xs font-bold text-slate-700 uppercase">
                                            {{ $selectedPayment->tutor?->tutorProfile?->accountName ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase">Payment Evidence</label>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                            @if ($selectedPayment->evidence)
                                @if (Str::endsWith($selectedPayment->evidence, ['.jpg', '.jpeg', '.png', '.gif']))
                                    <img src="{{ asset('storage/' . $selectedPayment->evidence) }}"
                                        class="w-full h-40 object-cover rounded-xl shadow-sm border border-white">
                                @else
                                    <a href="{{ asset('storage/' . $selectedPayment->evidence) }}" target="_blank"
                                        class="flex items-center gap-3 text-cyan-600 font-bold text-sm">
                                        <i class="fa fa-file-pdf text-2xl text-red-500"></i>
                                        View PDF Evidence
                                    </a>
                                @endif
                            @else
                                <p class="text-xs text-slate-400 italic">No evidence uploaded.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-slate-50 flex justify-center">
                    <button wire:click.prevent="closeModals"
                        class="px-8 py-2 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition-colors">Dismiss</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Form Modal (Create/Edit) --}}
    @if ($createModal || $editModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-8">
                <h3 class="text-2xl font-black text-slate-800 mb-6">
                    {{ $createModal ? 'New Payment' : 'Edit Payment' }}</h3>
                <form wire:submit.prevent="{{ $createModal ? 'store' : 'update' }}" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Tutor</label>
                        <select wire:model.defer="tutor_id"
                            class="w-full bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-cyan-500 text-sm">
                            <option value="">Select a tutor...</option>
                            @foreach ($tutors as $tutor)
                                <option value="{{ $tutor->id }}">{{ $tutor->name }}</option>
                            @endforeach
                        </select>
                        @error('tutor_id')
                            <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Amount (NGN)</label>
                            <input type="number" step="0.01" wire:model.defer="amount"
                                class="w-full bg-slate-50 border-none rounded-xl text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Status</label>
                            <select wire:model.defer="status"
                                class="w-full bg-slate-50 border-none rounded-xl text-sm">
                                <option value="Pending">Pending</option>
                                <option value="Earned">Earned</option>
                                <option value="Paid">Paid</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Lesson Booking</label>
                        <select wire:model.defer="booking_id"
                            class="w-full bg-slate-50 border-none rounded-xl text-sm">
                            <option value="">Select booking...</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}">ID: {{ $booking->id }} -
                                    {{ $booking->client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1 pt-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Evidence Attachment</label>
                        <input type="file" wire:model="{{ $createModal ? 'evidence' : 'newEvidence' }}"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100 cursor-pointer">
                    </div>

                    <div class="flex gap-3 pt-6">
                        <button type="button" wire:click.prevent="closeModals"
                            class="flex-1 p-2  font-bold text-slate-500 bg-slate-100 rounded-xl">Cancel</button>
                        <button type="submit"
                            class="flex-1 p-2 font-bold text-white bg-cyan-600 rounded-xl shadow-lg shadow-cyan-100">{{ $createModal ? 'Create Record' : 'Save Changes' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
