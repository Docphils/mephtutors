<div class="p-6 max-w-7xl mx-auto space-y-6 text-gray-900">
    <x-slot name="header">
        <div
            class="bg-whitep-1 rounded-xl shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">
                    My Booked <span class="text-cyan-600">Lessons</span>
                </h1>
                <p class="text-slate-500 text-sm">Manage, track, and filter your scheduled tutor sessions.</p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white p-2 rounded-xl border border-slate-100 shadow-sm flex flex-wrap gap-2 overflow-x-auto">
        @foreach (['Pending Lessons', 'Accepted Lessons', 'Active Lessons', 'Completed Lessons', 'Closed Lessons'] as $tab)
            <button wire:click="setTab('{{ $tab }}')"
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all whitespace-nowrap
                {{ $activeTab == $tab ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-100' : 'text-slate-500 hover:bg-slate-50' }}">
                <i
                    class="fa-solid 
                    {{ $tab == 'Pending Lessons' ? 'fa-clock-rotate-left' : '' }}
                    {{ $tab == 'Accepted Lessons' ? 'fa-circle-check' : '' }}
                    {{ $tab == 'Active Lessons' ? 'fa-play-circle' : '' }}
                    {{ $tab == 'Completed Lessons' ? 'fa-flag-checkered' : '' }}
                    {{ $tab == 'Closed Lessons' ? 'fa-box-archive' : '' }}
                mr-2 opacity-70"></i>
                {{ str_replace(' Lessons', '', $tab) }}
            </button>
        @endforeach
    </div>

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 border border-red-100 text-red-700 rounded-xl font-bold flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
        </div>
    @endif
    @if (session()->has('success'))
        <div
            class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl font-bold flex items-center gap-3">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($lessons as $booking)
            <div
                class="bg-white rounded-xl border border-slate-100 shadow-sm hover:shadow-xl transition-all p-6 group relative">
                <div class="flex justify-between items-start mb-4">
                    <span
                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                        {{ $booking->status === 'Pending' ? 'bg-amber-100 text-amber-700' : 'bg-cyan-100 text-cyan-700' }}">
                        {{ $booking->status }}
                    </span>

                    <div class="flex gap-2">
                        <button wire:click="showLesson({{ $booking->id }})"
                            class="p-2 text-slate-400 hover:text-cyan-600 hover:bg-cyan-50 rounded-lg transition-all"
                            title="View Details">
                            <i class="fa-solid fa-eye"></i>
                        </button>

                        @if ($booking->status === 'Pending')
                            <button wire:click="editAcceptance({{ $booking->id }})"
                                class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                title="Manage Acceptance">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        @elseif ($booking->status === 'Completed')
                            <button wire:click="editApproval({{ $booking->id }})"
                                class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
                                title="Approve Completion">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 leading-tight">{{ $booking->tutor->name }}</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">Assigned Tutor</p>
                    </div>
                </div>

                <div class="space-y-2 mb-6 border-t border-slate-50 pt-4">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <i class="fa-solid fa-calendar-day text-cyan-500 w-4"></i>
                        <span class="font-medium">{{ $booking->start_date }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <i class="fa-solid fa-book-open text-cyan-500 w-4"></i>
                        <span class="truncate">{{ $booking->subjects }}</span>
                    </div>
                </div>

                <button wire:click="showLesson({{ $booking->id }})"
                    class="w-full py-3 rounded-xl bg-slate-50 group-hover:bg-cyan-600 group-hover:text-white text-slate-600 font-bold text-sm transition-all flex items-center justify-center gap-2">
                    Lesson Dashboard
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
            </div>
        @empty
            <div
                class="col-span-full py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                <i class="fa-solid fa-calendar-xmark text-slate-300 text-4xl mb-4"></i>
                <p class="text-slate-600 font-medium font-black uppercase tracking-widest text-xs">No
                    {{ $activeTab }} Found</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $lessons->links() }}
    </div>

    <div x-data="{ open: @entangle('showModal') }" x-show="open" style="display: none;" class="fixed inset-0 z-[80] overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
        <div class="absolute inset-y-0 right-0 max-w-full flex">
            <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col animate-slide-in-right">
                @if ($selectedLesson)
                    <div class="p-8 bg-slate-900 text-white relative">
                        <button @click="open = false"
                            class="absolute top-6 right-6 text-slate-400 hover:text-white transition-all">
                            <i class="fa-solid fa-xmark text-2xl"></i>
                        </button>
                        <h2 class="text-2xl font-black mb-1">Lesson Details</h2>
                        <p class="text-cyan-400 text-xs font-bold uppercase tracking-widest">Tutor:
                            {{ $selectedLesson->tutor->name }}</p>
                    </div>

                    <div class="p-8 flex-1 overflow-y-auto space-y-6">
                        <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Schedule</label>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Start Date</span>
                                    <span
                                        class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($selectedLesson->start_date)->format('M d, Y') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Frequency</span>
                                    <span
                                        class="font-bold text-slate-800">{{ $selectedLesson->days_times ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Location</label>
                                <p class="text-sm text-slate-700 font-medium"><i
                                        class="fa-solid fa-location-dot mr-2 text-cyan-600"></i>{{ $selectedLesson->location ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Subjects</label>
                                <p class="text-sm text-slate-700 font-medium"><i
                                        class="fa-solid fa-book mr-2 text-cyan-600"></i>{{ $selectedLesson->subjects }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Financial
                                Overview</label>
                            <div
                                class="flex items-center justify-between p-4 bg-cyan-50 rounded-2xl border border-cyan-100">
                                <div>
                                    <p class="text-[10px] text-cyan-600 font-bold uppercase">Total Amount</p>
                                    <p class="text-xl font-black text-cyan-900">
                                        {{ number_format($selectedLesson->amount, 2) }}
                                        {{ $selectedLesson->currency ?? 'NGN' }}</p>
                                </div>
                                <span
                                    class="px-3 py-1 bg-white rounded-lg text-[10px] font-black text-cyan-700 shadow-sm">
                                    {{ $selectedLesson->paymentStatus ?? 'Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="p-6 border-t border-slate-100 bg-slate-50">
                    <button @click="open = false"
                        class="w-full py-4 bg-slate-800 text-white font-black rounded-xl hover:bg-slate-900 transition-all">
                        Close Panel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($showAcceptanceModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-8 text-center bg-slate-50 border-b border-slate-100">
                    <div
                        class="w-16 h-16 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Confirm Commencement</h2>
                    <p class="text-slate-500 text-sm">Please review the schedule and provide remarks.</p>
                </div>

                <div class="p-8 space-y-4">
                    <textarea wire:model="clientAcceptanceRemarks"
                        class="w-full bg-slate-50 border-transparent rounded-2xl p-4 text-sm focus:ring-2 focus:ring-cyan-500"
                        placeholder="Enter your remarks here..."></textarea>

                    <select wire:model="status"
                        class="w-full bg-slate-50 border-transparent rounded-2xl p-4 text-sm font-bold">
                        <option value="">Select Action</option>
                        <option value="Adjust">Request Adjustment</option>
                        <option value="Accepted">Accept Booking</option>
                    </select>

                    <div class="relative group">
                        <input type="file" wire:model="paymentEvidence"
                            class="absolute inset-0 opacity-0 cursor-pointer">
                        <div
                            class="w-full border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center group-hover:border-cyan-400 transition-all">
                            <i class="fa-solid fa-cloud-arrow-up text-slate-400 mb-1"></i>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Upload Payment
                                Evidence</p>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button wire:click="$set('showAcceptanceModal', false)"
                            class="flex-1 py-3 font-bold text-slate-400 hover:text-slate-600">Cancel</button>
                        <button wire:click="submitAcceptance"
                            class="flex-1 py-3 bg-cyan-600 text-white font-black rounded-xl shadow-lg shadow-cyan-100 flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="submitAcceptance">Submit Response</span>
                            <span wire:loading wire:target="submitAcceptance"><i
                                    class="fa-solid fa-circle-notch animate-spin"></i> Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showApprovalModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-8 text-center bg-emerald-50 border-b border-emerald-100">
                    <div
                        class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Approve Lesson</h2>
                    <p class="text-slate-500 text-sm">Closing this lesson confirms the tutor's delivery.</p>
                </div>

                <div class="p-8 space-y-4">
                    <textarea wire:model="clientApprovalRemarks"
                        class="w-full bg-slate-50 border-transparent rounded-2xl p-4 text-sm focus:ring-2 focus:ring-emerald-500"
                        placeholder="Feedback for the tutor..."></textarea>

                    <select wire:model="status"
                        class="w-full bg-slate-50 border-transparent rounded-2xl p-4 text-sm font-bold">
                        <option value="">Select Status</option>
                        <option value="Declined">Decline Approval</option>
                        <option value="Closed">Approve & Close</option>
                    </select>

                    <button wire:click="submitApproval"
                        class="w-full py-4 bg-emerald-600 text-white font-black rounded-2xl shadow-lg shadow-emerald-100 transition-all flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="submitApproval">Submit Approval</span>
                        <span wire:loading wire:target="submitApproval"><i
                                class="fa-solid fa-circle-notch animate-spin"></i> Saving...</span>
                    </button>
                    <button wire:click="$set('showApprovalModal', false)"
                        class="w-full py-2 font-bold text-slate-400">Back to List</button>
                </div>
            </div>
        </div>
    @endif
</div>
