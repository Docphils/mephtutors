<div class="p-4 sm:p-6 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div
                class="w-12 h-12 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-cyan-200">
                <i class="fa-solid fa-graduation-cap text-xl"></i>
            </div>
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">My <span
                        class="text-cyan-600">Lessons</span></h2>
                <p class="text-slate-500 text-sm font-medium">Manage your schedule and track student progress.</p>
            </div>
        </div>
    </x-slot>

    @if (session()->has('error'))
        <div
            class="mb-6 flex items-center gap-3 bg-rose-50 border border-rose-100 text-rose-700 px-4 py-3 rounded-xl text-sm font-bold animate-fade-in">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif
    @if (session()->has('success'))
        <div
            class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-xl text-sm font-bold animate-fade-in">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-2 mb-8">
        @foreach (['Active Lessons', 'Completed Lessons', 'Accepted Lessons', 'Closed Lessons', 'Declined Lessons', 'All Lessons'] as $tab)
            <button wire:click="setTab('{{ $tab }}')"
                class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all {{ $activeTab === $tab ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-200' : 'bg-white text-slate-500 hover:bg-slate-100 border border-slate-100' }}">
                {{ str_replace(' Lessons', '', $tab) }}
            </button>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead
                    class="bg-cyan-700 border-b border-slate-100 text-slate-100 text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Learners &
                            Service</th>
                        <th class="px-6 py-4">Schedule</th>
                        <th class="px-6 py-4">Subjects</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($lessons as $booking)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $booking->learners_string }}</div>
                                <div class="text-xs text-cyan-600 font-semibold">
                                    {{ $booking->serviceItem->name ?? 'Tuition' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-700">
                                    {{ $booking->start_date->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $booking->duration }} •
                                    {{ $booking->sessions }} sessions</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-600 max-w-[150px] truncate"
                                    title="{{ $booking->subjects_string }}">
                                    {{ $booking->subjects_string }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                    {{ $booking->status === 'Active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $booking->status === 'Completed' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $booking->status === 'Closed' ? 'bg-slate-100 text-slate-500' : '' }}
                                    {{ $booking->status === 'Accepted' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $booking->status === 'Declined' ? 'bg-rose-100 text-rose-700' : '' }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="showLesson({{ $booking->id }})"
                                        class="p-2 text-slate-400 hover:text-cyan-600 transition-colors"
                                        title="View Details">
                                        <i class="fa-solid fa-circle-info text-lg"></i>
                                    </button>
                                    @if ($booking->status === 'Active' || $booking->status === 'Declined')
                                        <button wire:click="editCompleted({{ $booking->id }})"
                                            class="p-2 text-slate-400 hover:text-emerald-600 transition-colors"
                                            title="Mark Completed">
                                            <i class="fa-solid fa-check-double text-lg"></i>
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
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <p class="text-slate-400 font-medium">No lessons found in this category.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($lessons->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $lessons->links() }}
            </div>
        @endif
    </div>

    <div x-data="{ open: @entangle('showModal') }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div
            class="relative bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-cyan-700 sticky top-0 z-10">
                <h3 class="text-xl font-black text-slate-100">Lesson <span class="text-cyan-200">Details</span></h3>
                <button @click="open = false" class="text-slate-100 hover:text-slate-300 transition-colors"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <div class="p-6 overflow-y-auto space-y-6">
                @if ($selectedLesson)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-cyan-50 rounded-2xl border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Learners</label>
                            <div class="text-slate-800 font-bold">{{ $selectedLesson->learners_string }}</div>
                        </div>
                        <div class="p-4 bg-cyan-50 rounded-2xl border border-slate-100">
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Location</label>
                            <div class="text-slate-800 font-bold">{{ $selectedLesson->location }}</div>
                        </div>
                    </div>

                    <div class="p-4 bg-cyan-50 rounded-2xl border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Your Earning</label>
                        <div class="text-slate-800 font-extrabold">
                            ₦{{ number_format($selectedLesson->payments?->amount) }}
                        </div>
                    </div>

                    <div class="p-5 border border-slate-100 rounded-2xl">
                        <h4
                            class="text-xs font-black text-slate-400 uppercase mb-4 tracking-widest flex items-center gap-2">
                            <i class="fa-solid fa-calendar-day text-cyan-500"></i> Schedule & Logistics
                        </h4>
                        <div class="grid grid-cols-2 gap-y-4">
                            <div>
                                <span class="block text-xs text-slate-500">Start Date</span>
                                <span
                                    class="font-bold text-slate-800">{{ $selectedLesson->start_date->format('l, F j, Y') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-slate-500">End Date</span>
                                <span
                                    class="font-bold text-slate-800">{{ $selectedLesson->end_date ? $selectedLesson->end_date->format('l, F j, Y') : 'Ongoing' }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="block text-xs text-slate-500">Days & Times</span>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach ($selectedLesson->formatted_days_times as $dt)
                                        <span
                                            class="px-2 py-1 bg-cyan-50 text-cyan-700 rounded-lg text-xs font-bold border border-cyan-100">
                                            {{ $dt['day'] }} ({{ $dt['time'] }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 border border-slate-100 rounded-2xl">
                        <h4
                            class="text-xs font-black text-slate-400 uppercase mb-4 tracking-widest flex items-center gap-2">
                            <i class="fa-solid fa-book-open text-cyan-500"></i> Academic Information
                        </h4>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-xs text-slate-500">Service Category</span>
                                    <span
                                        class="font-bold text-slate-800">{{ $selectedLesson->serviceItem->service->name }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-500">Specific Service</span>
                                    <span
                                        class="font-bold text-slate-800">{{ $selectedLesson->serviceItem->name }}</span>
                                </div>
                            </div>
                            @if ($selectedLesson->serviceItem->has_subjects)
                                <div>
                                    <span class="block text-xs text-slate-500">Subjects</span>
                                    <span
                                        class="font-bold text-slate-800">{{ $selectedLesson->subjects_string }}</span>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                @if ($selectedLesson->serviceItem->requires_exam_type)
                                    <div>
                                        <span class="block text-xs text-slate-500">Exam Type</span>
                                        <span
                                            class="font-bold text-slate-800">{{ $selectedLesson->examType->name ?? 'N/A' }}</span>
                                    </div>
                                @endif
                                @if ($selectedLesson->serviceItem->requires_level)
                                    <div>
                                        <span class="block text-xs text-slate-500">Level</span>
                                        <span
                                            class="font-bold text-slate-800">{{ $selectedLesson->level->name ?? 'N/A' }}
                                            Lessons</span>
                                    </div>
                                @endif
                                @if ($selectedLesson->serviceItem->requires_curriculum)
                                    <div>
                                        <span class="block text-xs text-slate-500">Curriculum</span>
                                        <span
                                            class="font-bold text-slate-800">{{ $selectedLesson->curriculum }}</span>
                                    </div>
                                @endif
                                <div>
                                    <span class="block text-xs text-slate-500">Sessions</span>
                                    <span class="font-bold text-slate-800">{{ $selectedLesson->sessions }}
                                        Lessons</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($selectedLesson->tutorRemarks)
                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-1">My Completion
                                Remarks</label>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $selectedLesson->tutorRemarks }}</p>
                        </div>
                    @endif
                @else
                    <div class="py-10 text-center text-slate-400">Loading lesson details...</div>
                @endif
            </div>

            <div class="p-6 bg-cyan-50 border-t border-cyan-100 flex gap-3">
                <button @click="open = false"
                    class="flex-1 py-3 bg-white border border-cyan-200 text-cyan-600 font-bold rounded-xl hover:bg-cyan-100 transition-all">Close</button>
            </div>
        </div>
    </div>

    @if ($showCompletedModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                wire:click="$set('showCompletedModal', false)"></div>
            <div class="relative bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
                <div
                    class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center mb-6 text-2xl">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Complete Lesson</h3>
                <p class="text-slate-500 text-sm mb-6">Summarize the student's progress and finalize the lesson record.
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Final Remarks</label>
                        <textarea wire:model="tutorRemarks" rows="4"
                            class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 placeholder:text-slate-300"
                            placeholder="e.g. Student improved in Calculus and completed the syllabus..."></textarea>
                        @error('tutorRemarks')
                            <span class="text-rose-500 text-[10px] font-bold mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Update Status</label>
                        <select wire:model="status"
                            class="w-full rounded-2xl border-slate-200 focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500 text-slate-700 font-medium">
                            <option value="">Choose Status...</option>
                            <option value="Completed">Mark as Completed</option>
                        </select>
                        @error('status')
                            <span class="text-rose-500 text-[10px] font-bold mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-8">
                    <button wire:click="$set('showCompletedModal', false)"
                        class="py-3 font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
                    <button wire:click="submitCompleted"
                        class="relative py-3 font-bold text-white bg-emerald-500 rounded-xl shadow-lg shadow-emerald-200 hover:bg-emerald-600 transition-all">
                        <span wire:loading.remove wire:target="submitCompleted">Submit Final</span>
                        <span wire:loading wire:target="submitCompleted"><i
                                class="fa-solid fa-spinner animate-spin"></i> Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
