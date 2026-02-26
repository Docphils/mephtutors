<div class="p-4 sm:p-6 bg-cyan-100 min-h-screen rounded">
    <x-slot name="header">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <div class="flex items-center gap-3 sm:gap-4">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="group flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200 text-cyan-600 rounded-xl shadow-sm hover:bg-cyan-600 hover:text-white transition-all shrink-0">
                    <i class="fa fa-arrow-left transition-transform group-hover:-translate-x-1 text-xs sm:text-sm"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Tutor <span
                            class="text-cyan-600">Payments</span></h2>
                    <p class="text-slate-500 text-sm font-medium">Manage disbursements and payment evidence for active
                        lessons.
                    </p>
                </div>
            </div>
            <button x-on:click="$dispatch('createPayment')"
                class="flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-cyan-200 transition-all">
                <i class="fa fa-plus text-xs"></i>
                <span>Record Payment</span>
            </button>
        </div>
    </x-slot>

    {{-- Stats & Filters --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-2 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
            {{-- Tabs --}}
            <nav class="flex p-1 bg-cyan-700 rounded-xl w-full lg:w-auto overflow-x-auto sm:overflow-x-hidden">
                @foreach (['' => 'All', 'Pending' => 'Pending', 'Earned' => 'Earned', 'Paid' => 'Paid'] as $val => $label)
                    <button wire:click.prevent="$set('status', '{{ $val }}')"
                        class="flex-1 lg:flex-none px-2 sm:px-6 py-2 rounded-lg text-sm font-bold transition-all {{ ($status ?? '') === $val ? 'bg-white text-cyan-600 shadow-sm' : 'text-slate-100 hover:text-slate-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>

            {{-- Search --}}
            <div class="relative w-full lg:w-96 px-2 lg:px-0">
                <i class="fa fa-search absolute left-4 lg:left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" wire:model.live="search" placeholder="Search by amount, tutor or location..."
                    class="w-full pl-10 pr-4 py-2 bg-cyan-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-cyan-500 text-cyan-900">
            </div>
        </div>
    </div>

    {{-- Desktop Table / Mobile Cards --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Desktop View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cyan-700 border-b border-slate-100 text-slate-100">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest">Reference
                            / Tutor</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest">Booking
                            Info</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest">Amount
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest">Status
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-cyan-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-600 font-bold text-xs uppercase">
                                        {{ substr($payment->tutor->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">{{ $payment->tutor->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium">REF: #PAY-{{ $payment->id }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-700">Booking #{{ $payment->booking_id }}</p>
                                <p class="text-xs text-slate-500">{{ Str::limit($payment->booking->location, 30) }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-black text-slate-800">
                                ₦{{ number_format((float) $payment->amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tighter
                                    {{ $payment->status === 'Paid' ? 'bg-emerald-100 text-emerald-600' : ($payment->status === 'Earned' ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600') }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-1">
                                <button wire:click="showPayment({{ $payment->id }})"
                                    class="p-2 text-slate-400 hover:text-cyan-600 hover:bg-cyan-50 rounded-lg transition-all"
                                    title="View Details"><i class="fa fa-eye"></i></button>
                                <button wire:click="edit({{ $payment->id }})"
                                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                    title="Edit"><i class="fa fa-edit"></i></button>
                                <button wire:click="openDelete({{ $payment->id }})"
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                    title="Delete"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium italic">No
                                payment records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden divide-y divide-slate-100">
            @foreach ($payments as $payment)
                <div class="p-4 flex flex-col gap-3">
                    <div class="flex justify-between items-start">
                        <div class="flex gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $payment->tutor->name }}</p>
                                <p class="text-[10px] text-slate-400 uppercase">Booking #{{ $payment->booking_id }}</p>
                            </div>
                        </div>
                        <span
                            class="text-sm font-black text-slate-800">₦{{ number_format((float) $payment->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span
                            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest {{ $payment->status === 'Paid' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                            {{ $payment->status }}
                        </span>
                        <div class="flex gap-2">
                            <button wire:click="showPayment({{ $payment->id }})"
                                class="w-8 h-8 flex items-center justify-center bg-slate-50 rounded-lg text-slate-400"><i
                                    class="fa fa-eye text-xs"></i></button>
                            <button wire:click="edit({{ $payment->id }})"
                                class="w-8 h-8 flex items-center justify-center bg-slate-50 rounded-lg text-slate-400"><i
                                    class="fa fa-edit text-xs"></i></button>
                            <button wire:click="openDelete({{ $payment->id }})"
                                class="w-8 h-8 flex items-center justify-center bg-slate-50 rounded-lg text-red-400"><i
                                    class="fa fa-trash text-xs"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6">
        {{ $payments->links() }}
    </div>

    {{-- Modals: Integrated into a common rounded-3xl style --}}
    @if ($showModal && $selectedPayment)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full overflow-hidden">
                <div class="bg-cyan-600 p-6 text-white text-center">
                    <h3 class="text-xl font-black">Payment Receipt</h3>
                    <p class="text-cyan-100 text-xs uppercase tracking-widest font-bold">Ref:
                        #PAY-{{ $selectedPayment->id }}</p>
                </div>
                <div class="p-8 space-y-6">
                    <div class="flex justify-between border-b border-slate-100 pb-4">
                        <div class="text-center flex-1 border-r border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Tutor</p>
                            <p class="font-bold text-slate-800">{{ $selectedPayment->tutor->name }}</p>
                        </div>
                        <div class="text-center flex-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Amount</p>
                            <p class="font-black text-cyan-600 text-lg">
                                ₦{{ number_format((float) $selectedPayment->amount, 2) }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase">Evidence Document</label>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                            @if ($selectedPayment->evidence)
                                @if (Str::endsWith($selectedPayment->evidence, ['.jpg', '.jpeg', '.png', '.gif']))
                                    <img src="{{ asset('storage/' . $selectedPayment->evidence) }}"
                                        class="w-full h-48 object-cover rounded-xl shadow-sm">
                                @else
                                    <a href="{{ asset('storage/' . $selectedPayment->evidence) }}" target="_blank"
                                        class="flex items-center gap-3 text-cyan-600 font-bold text-sm">
                                        <i class="fa fa-file-pdf text-2xl text-red-500"></i>
                                        View PDF Evidence
                                    </a>
                                @endif
                            @else
                                <p class="text-xs text-slate-400 italic">No evidence uploaded for this transaction.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="p-6 bg-slate-50 flex justify-center">
                    <button wire:click.prevent="closeModals"
                        class="px-8 py-2 bg-slate-800 text-white font-bold rounded-xl">Dismiss</button>
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
                            class="flex-1 py-3 font-bold text-slate-500 bg-slate-100 rounded-xl">Cancel</button>
                        <button type="submit"
                            class="flex-1 py-3 font-bold text-white bg-cyan-600 rounded-xl shadow-lg shadow-cyan-100">{{ $createModal ? 'Create Record' : 'Save Changes' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
