<div class="p-4 sm:p-6 bg-cyan-100 min-h-screen rounded-2xl">
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div
                class="w-12 h-12 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-cyan-200">
                <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
            </div>
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Payment <span
                        class="text-cyan-600">History</span></h2>
                <p class="text-slate-500 text-sm font-medium">Track your earnings, payouts, and resolve payment issues.
                </p>
            </div>
        </div>
    </x-slot>

    @if (session()->has('success'))
        <div
            class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-xl text-sm font-bold animate-fade-in">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-2 mb-8">
        @foreach (['All Payments', 'Pending Payments', 'Earned Payments', 'Completed Payments', 'Disputed'] as $tab)
            <button wire:click="setTab('{{ $tab }}')"
                class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all {{ $activeTab === $tab ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-200' : 'bg-white text-slate-500 hover:bg-slate-100 border border-slate-100' }}">
                {{ str_replace(' Payments', '', $tab) }}
            </button>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead
                    class="bg-cyan-700 border-b border-slate-100 text-xs font-bold text-slate-100 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Client & Service
                        </th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Dispute Status
                        </th>
                        <th class="px-6 py-4 text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $payment->booking->client->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-cyan-600 font-semibold">
                                    {{ $payment->booking->serviceItem->name ?? 'Tuition' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-black text-slate-700">₦{{ number_format($payment->amount, 2) }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Ref: #{{ $payment->id }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                    {{ $payment->status === 'Pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $payment->status === 'Earned' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $payment->status === 'Paid' ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($payment->dispute)
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                        {{ ($payment->dispute['status'] ?? '') === 'Pending' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500' }}">
                                        Dispute: {{ $payment->dispute['status'] ?? 'Active' }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-300 italic">No disputes</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="showPayment({{ $payment->id }})"
                                        class="p-2 text-slate-400 hover:text-cyan-600 transition-colors"
                                        title="View Details">
                                        <i class="fa-solid fa-circle-info text-lg"></i>
                                    </button>
                                    @if (!$payment->dispute)
                                        <button wire:click="openDispute({{ $payment->id }})"
                                            class="p-2 text-slate-400 hover:text-rose-500 transition-colors"
                                            title="Dispute Payment">
                                            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div
                                    class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200 text-2xl">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <p class="text-slate-400 font-medium">No payment records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
    </div>

    <div x-data="{ open: @entangle('showModal') }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div
            class="relative bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-cyan-700 sticky top-0 z-10">
                <h3 class="text-xl font-black text-slate-50">Payment <span class="text-cyan-200">Details</span></h3>
                <button @click="open = false" class="text-slate-200 hover:text-slate-300 transition-colors"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <div class="p-6 overflow-y-auto space-y-6">
                @if ($selectedPayment)
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Amount
                                Paid</label>
                            <div class="text-2xl font-black text-cyan-600">
                                ₦{{ number_format($selectedPayment->amount, 2) }}</div>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Status</label>
                            <div class="text-slate-800 font-bold uppercase text-sm">{{ $selectedPayment->status }}
                            </div>
                        </div>
                    </div>

                    @if ($selectedPayment->evidence)
                        <div class="p-5 border border-slate-100 rounded-2xl">
                            <h4
                                class="text-xs font-black text-slate-400 uppercase mb-4 tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-camera text-cyan-500"></i> Payment Evidence
                            </h4>
                            <div class="flex flex-col items-center justify-center bg-slate-50 p-4 rounded-xl">
                                @php
                                    $extension = pathinfo($selectedPayment->evidence, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                                    $isPdf = strtolower($extension) === 'pdf';
                                @endphp

                                @if ($isImage)
                                    <img src="{{ asset('storage/' . $selectedPayment->evidence) }}"
                                        class="max-w-full h-auto rounded-lg shadow-sm border border-slate-200">
                                    <a href="{{ asset('storage/' . $selectedPayment->evidence) }}" target="_blank"
                                        class="mt-3 text-[10px] font-bold text-cyan-600 uppercase hover:underline">
                                        <i class="fa-solid fa-expand mr-1"></i> Open full size
                                    </a>
                                @elseif ($isPdf)
                                    <div class="w-full h-96 rounded-lg overflow-hidden border border-slate-200">
                                        <iframe src="{{ asset('storage/' . $selectedPayment->evidence) }}"
                                            class="w-full h-full" frameborder="0"></iframe>
                                    </div>
                                    <a href="{{ asset('storage/' . $selectedPayment->evidence) }}" target="_blank"
                                        class="mt-3 text-[10px] font-bold text-cyan-600 uppercase hover:underline">
                                        <i class="fa-solid fa-download mr-1"></i> Download PDF
                                    </a>
                                @else
                                    <a href="{{ asset('storage/' . $selectedPayment->evidence) }}" target="_blank"
                                        class="flex items-center gap-2 text-cyan-600 font-bold underline">
                                        <i class="fa-solid fa-file text-2xl"></i> View Attached File
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($selectedPayment->dispute)
                        <div class="p-5 bg-rose-50 border border-rose-100 rounded-2xl">
                            <h4 class="text-xs font-black text-rose-600 uppercase mb-3 tracking-widest">Dispute History
                            </h4>
                            <div class="space-y-3">
                                <div>
                                    <span class="block text-[10px] text-rose-400 font-bold uppercase">My Reason</span>
                                    <p class="text-sm text-slate-700 leading-relaxed">
                                        {{ $selectedPayment->dispute['reason'] }}</p>
                                </div>
                                @if ($selectedPayment->dispute['admin_response'])
                                    <div class="pt-3 border-t border-rose-100">
                                        <span class="block text-[10px] text-emerald-600 font-bold uppercase">Admin
                                            Resolution</span>
                                        <p class="text-sm text-slate-700 leading-relaxed italic">
                                            {{ $selectedPayment->dispute['admin_response'] }}</p>
                                        <div class="text-[10px] text-slate-400 mt-1">Resolved on:
                                            {{ \Carbon\Carbon::parse($selectedPayment->dispute['resolved_at'])->format('M d, Y') }}
                                        </div>
                                    </div>
                                @else
                                    <div
                                        class="flex items-center gap-2 text-[10px] font-bold text-rose-500 uppercase mt-2">
                                        <i class="fa-solid fa-clock animate-pulse"></i> Awaiting Admin Response
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div class="p-6 bg-slate-50 border-t border-cyan-100">
                <button @click="open = false"
                    class="w-full py-3 bg-white border border-cyan-200 text-cyan-600 font-bold rounded-xl hover:bg-cyan-100 transition-all">Close
                    Window</button>
            </div>
        </div>
    </div>

    @if ($showDisputeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showDisputeModal', false)">
            </div>
            <div class="relative bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
                <div
                    class="w-16 h-16 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mb-6 text-2xl">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Dispute Payment</h3>
                <p class="text-slate-500 text-sm mb-6">Explain why you are disputing this payment record. The
                    administrator will investigate and respond.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Reason for Dispute</label>
                        <textarea wire:model="disputeReason" rows="5"
                            class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 placeholder:text-slate-300"
                            placeholder="Please provide specific details about the missing amount or incorrect status..."></textarea>
                        @error('disputeReason')
                            <span class="text-rose-500 text-[10px] font-bold mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-8">
                    <button wire:click="$set('showDisputeModal', false)"
                        class="py-3 font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
                    <button wire:click="submitDispute"
                        class="py-3 font-bold text-white bg-rose-500 rounded-xl shadow-lg shadow-rose-200 hover:bg-rose-600 transition-all">
                        <span wire:loading.remove wire:target="submitDispute">Submit for Review</span>
                        <span wire:loading wire:target="submitDispute"><i
                                class="fa-solid fa-spinner animate-spin"></i> Submitting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
