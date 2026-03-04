<div class="p-3 sm:p-4 lg:p-6 bg-cyan-100 min-h-screen">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="group flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 bg-white border border-slate-200 text-cyan-600 rounded-xl shadow-sm hover:bg-cyan-600 hover:text-white transition-all shrink-0">
                    <i class="fa fa-arrow-left transition-transform group-hover:-translate-x-1 text-xs sm:text-sm"></i>
                </a>
                <div>
                    <h2 class="font-black text-lg sm:text-2xl lg:text-3xl text-slate-800 tracking-tight leading-tight">
                        Lesson <span class="text-cyan-600">Manager</span>
                    </h2>
                    <p class="hidden sm:block text-slate-500 text-xs sm:text-sm font-medium mt-1">
                        Create and manage academic lessons and tutor assignments.
                    </p>
                </div>
            </div>

            <button x-on:click="$dispatch('open-new-booking')"
                class="w-full sm:w-auto bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-cyan-200 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>New Booking</span>
            </button>
        </div>
    </x-slot>
    @if (session('success'))
        <div
            class="flex items-center mb-1 gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-lg text-xs font-bold animate-fade-in-down">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div
            class="flex items-center mb-1 gap-2 px-3 py-1.5 bg-red-50 border border-red-100 text-red-700 rounded-lg text-xs font-bold animate-fade-in-down">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('error') }}
        </div>
    @endif
    <div class=" hidden flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">

        <div class="flex items-center gap-3">

            <button wire:click="openCreate"
                class="bg-white border border-slate-200 text-slate-700 px-6 py-2.5 rounded-xl font-bold shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Manual Booking</span>
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div
        class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-wrap gap-3 sm:gap-4 mb-6 sm:mb-8">
        <div class="flex-1 min-w-full sm:min-w-[300px] relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input wire:model.live.debounce.300ms="search" placeholder="Search learners, clients, or tutors..."
                class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 outline-none transition-all" />
        </div>
        <select wire:model.live="status"
            class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-cyan-500/20 font-medium text-slate-600">
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="accepted">Accepted</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
        </select>
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-cyan-700 border-b border-slate-100">
                    <tr>
                        <th class="px-3 sm:px-6 py-4 text-xs font-bold text-slate-100 uppercase">Learners & Service</th>
                        <th class="px-3 sm:px-6 py-4 text-xs font-bold text-slate-100 uppercase">Client / Tutor</th>
                        <th class="px-3 sm:px-6 py-4 text-xs font-bold text-slate-100 uppercase">Schedule</th>
                        <th class="px-3 sm:px-6 py-4 text-xs font-bold text-slate-100 uppercase">Status</th>
                        <th class="px-3 sm:px-6 py-4 text-xs font-bold text-slate-100 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-3 sm:px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $booking->learners_string }}</div>
                                <div class="text-xs text-cyan-600 font-semibold">
                                    {{ $booking->serviceItem->name ?? 'Academic Tuition' }}</div>
                                <div class="text-xs text-slate-400 mt-1">{{ $booking->subjects_string }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-medium text-slate-700">
                                        <i class="fa-solid fa-user text-slate-300 mr-1"></i>
                                        {{ $booking->client->name ?? 'N/A' }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-500">
                                        <i class="fa-solid fa-chalkboard-user text-slate-300 mr-1"></i>
                                        {{ $booking->tutor->name ?? 'Not Assigned' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-4">
                                <div class="text-sm text-slate-600 font-medium">
                                    {{ \Carbon\Carbon::parse($booking->start_date)->format('M d') }} -
                                    {{ \Carbon\Carbon::parse($booking->end_date)->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-slate-400">{{ $booking->sessions }} sessions •
                                    {{ $booking->duration }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-4">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-bold uppercase
                                    {{ $booking->status === 'Active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $booking->status === 'Pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $booking->status === 'Accepted' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $booking->status === 'Completed' ? 'bg-slate-100 text-slate-700' : '' }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="showBooking({{ $booking->id }})"
                                        class="p-2 text-slate-400 hover:text-cyan-600 transition-colors"
                                        title="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    @if ($booking->status !== 'Closed')
                                        <button wire:click="editBooking({{ $booking->id }})"
                                            class="p-2 text-slate-400 hover:text-amber-600 transition-colors"
                                            title="Edit Booking">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    @endif
                                    @if ($booking->status === 'Accepted')
                                        <button wire:click="editActivation({{ $booking->id }})"
                                            class="p-2 text-slate-400 hover:text-emerald-600 transition-colors"
                                            title="Activate Lesson">
                                            <i class="fa-solid fa-play"></i>
                                        </button>
                                    @endif
                                    @if ($booking->status === 'Pending')
                                        <button wire:click="openDelete({{ $booking->id }})"
                                            class="p-2 text-slate-400 hover:text-rose-600 transition-colors"
                                            title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 sm:px-6 py-12 text-center">
                                <i class="fa-solid fa-folder-open text-slate-200 text-5xl mb-4"></i>
                                <p class="text-slate-400 font-medium">No bookings found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bookings->hasPages())
            <div class="px-3 sm:px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    {{-- Form Modal (Create/Edit) --}}
    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showForm', false)"></div>
            <div
                class="relative bg-white rounded-2xl sm:rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl">
                {{-- Modal Header --}}
                <div
                    class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 gap-3">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">{{ $editingId ? 'Edit' : 'New' }} Booking</h3>
                        <p class="text-xs text-slate-500 font-medium">Configure lesson details, learners, and schedule.
                        </p>
                    </div>
                    <button wire:click="$set('showForm', false)"
                        class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition-all">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-4 sm:p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <form wire:submit.prevent="saveBooking" class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Basic Info Section --}}
                        <div class="space-y-4">
                            <h4
                                class="text-sm font-bold text-cyan-600 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-circle-info"></i> Assignment Info
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Client</label>
                                    <select wire:model.live="client_id"
                                        class="w-full rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                                        <option value="">Select Client</option>
                                        @foreach ($clients as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tutor</label>
                                    <select wire:model.live="tutor_id"
                                        class="w-full rounded-xl border-slate-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                                        <option value="">Assign Later</option>
                                        @foreach ($tutors as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('tutor_id')
                                        <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Start
                                        Date</label>
                                    <input type="date" wire:model="start_date"
                                        class="w-full rounded-xl border-slate-200 text-sm" />
                                    @error('start_date')
                                        <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">End
                                        Date</label>
                                    <input type="date" wire:model="end_date"
                                        class="w-full rounded-xl border-slate-200 text-sm" />
                                    @error('end_date')
                                        <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Lesson
                                    Address</label>
                                <input type="text" wire:model="location" placeholder="Physical or Online address"
                                    class="w-full rounded-xl border-slate-200 text-sm" />
                                @error('location')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @if ($this->serviceUses('level'))
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase mb-1">Classes/Level</label>
                                        <input type="text" wire:model="classes" placeholder="e.g. Year 7"
                                            class="w-full rounded-xl border-slate-200 text-sm" />
                                        @error('classes')
                                            <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                                @if ($this->serviceUses('curriculum'))
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 uppercase mb-1">Curriculum</label>
                                        <select wire:model="curriculum"
                                            class="w-full rounded-xl border-slate-200 text-sm">
                                            <option value="British">British</option>
                                            <option value="Nigerian">Nigerian</option>
                                            <option value="Blended">Blended</option>
                                            <option value="N/A">N/A</option>
                                        </select>
                                        @error('curriculum')
                                            <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Subjects & Learners --}}
                        <div class="space-y-6">
                            {{-- Subjects --}}
                            @if ($this->serviceUses('subjects'))
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-bold text-cyan-600 uppercase tracking-wider">Subjects
                                        </h4>
                                        <button type="button" wire:click="addSubject"
                                            class="text-xs font-bold text-cyan-600 hover:text-cyan-700">
                                            <i class="fa-solid fa-plus-circle"></i> Add Subject
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach ($subjects as $index => $subject)
                                            <div class="flex gap-2">
                                                <input type="text" wire:model="subjects.{{ $index }}.name"
                                                    placeholder="Subject Name"
                                                    class="flex-1 rounded-xl border-slate-200 text-sm" />
                                                @error("subjects.$index.name")
                                                    <span
                                                        class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                                @enderror
                                                @if (count($subjects) > 1)
                                                    <button type="button"
                                                        wire:click="removeSubject({{ $index }})"
                                                        class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                        @error('subjects')
                                            <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            {{-- Learners --}}
                            @if ($this->serviceUses('learners'))
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-bold text-cyan-600 uppercase tracking-wider">Learners
                                        </h4>
                                        <button type="button" wire:click="addLearner"
                                            class="text-xs font-bold text-cyan-600 hover:text-cyan-700">
                                            <i class="fa-solid fa-plus-circle"></i> Add Learner
                                        </button>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach ($learners as $index => $learner)
                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <input type="text" wire:model="learners.{{ $index }}.name"
                                                    placeholder="Name"
                                                    class="flex-1 rounded-xl border-slate-200 text-sm" />
                                                @error("learners.$index.name")
                                                    <span
                                                        class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                                @enderror
                                                <input type="text" wire:model="learners.{{ $index }}.age"
                                                    placeholder="Age"
                                                    class="w-full sm:w-20 rounded-xl border-slate-200 text-sm" />
                                                @error("learners.$index.age")
                                                    <span
                                                        class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                                @enderror
                                                @if (count($learners) > 1)
                                                    <button type="button"
                                                        wire:click="removeLearner({{ $index }})"
                                                        class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                        @error('learners')
                                            <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Schedule Section --}}
                        <div class="md:col-span-2 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                                <h4
                                    class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fa-solid fa-calendar-days text-cyan-600"></i> Weekly Schedule
                                </h4>
                                <button type="button" wire:click="addDayTime"
                                    class="bg-white px-3 py-1 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                                    <i class="fa-solid fa-plus text-cyan-600 mr-1"></i> Add Slot
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach ($days_times as $index => $dt)
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center gap-2 bg-white p-2 rounded-xl border border-slate-200">
                                        <select wire:model="days_times.{{ $index }}.day"
                                            class="flex-1 border-none bg-transparent text-sm focus:ring-0 p-1 font-semibold text-slate-700">
                                            <option value="">Day</option>
                                            <option value="Monday">Monday</option>
                                            <option value="Tuesday">Tuesday</option>
                                            <option value="Wednesday">Wednesday</option>
                                            <option value="Thursday">Thursday</option>
                                            <option value="Friday">Friday</option>
                                            <option value="Saturday">Saturday</option>
                                            <option value="Sunday">Sunday</option>
                                        </select>
                                        @error("days_times.$index.day")
                                            <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                        @enderror
                                        <div class="hidden sm:block h-4 w-px bg-slate-100"></div>
                                        <input type="time" wire:model="days_times.{{ $index }}.time"
                                            class="flex-1 border-none bg-transparent text-sm focus:ring-0 p-1" />
                                        @error("days_times.$index.time")
                                            <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                        @enderror
                                        @if (count($days_times) > 1)
                                            <button type="button" wire:click="removeDayTime({{ $index }})"
                                                class="text-slate-300 hover:text-rose-500 transition-colors">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                                @error('days_times')
                                    <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Financials & Settings --}}
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Total Amount
                                    (₦)</label>
                                <input type="number" wire:model="amount"
                                    class="w-full rounded-xl border-slate-200 text-sm font-bold text-cyan-700" />
                                @error('amount')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Duration</label>
                                <input type="text" wire:model="duration" placeholder="e.g. 2 hours"
                                    class="w-full rounded-xl border-slate-200 text-sm" />
                                @error('duration')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Payment
                                    Status</label>
                                <select type="text" wire:model="paymentStatus"
                                    class="w-full rounded-xl border-slate-200 text-sm">
                                    <option value="Pending">Pending</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Failed">Failed</option>
                                </select>
                                @error('paymentStatus')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Lesson
                                    Status</label>
                                <select wire:model="status_field"
                                    class="w-full rounded-xl border-slate-200 text-sm font-bold">
                                    <option value="Pending">Pending</option>
                                    <option value="Accepted">Accepted</option>
                                    <option value="Active">Active</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Closed">Closed</option>
                                </select>
                                @error('status_field')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div
                            class="md:col-span-2 flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" wire:click="$set('showForm', false)"
                                class="w-full sm:w-auto px-4 py-2 rounded-xl font-bold text-slate-500 hover:bg-slate-50 transition-all">Cancel</button>
                            <button wire:loading.class='hidden' type="submit"
                                class="w-full sm:w-auto bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-cyan-200 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>{{ $editingId ? 'Update' : 'Create' }} Booking</span>
                            </button>
                            <p wire:loading wire:target="saveBooking"
                                class="w-full sm:w-auto bg-cyan-600 hover:bg-cyan-700 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-cyan-200 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-spinner animate-spin"></i>
                                <span>{{ $editingId ? 'Updating' : 'Creating' }} Booking ...</span>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if ($showDetail && $selectedBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeDetail"></div>
            <div
                class="relative bg-white rounded-2xl sm:rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">

                {{-- Header --}}
                <div class="bg-cyan-600 p-4 sm:p-6 text-white relative shrink-0">
                    <button wire:click="closeDetail"
                        class="absolute top-4 right-4 text-white/60 hover:text-white transition-colors">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                    <div class="flex items-start sm:items-center gap-3 sm:gap-4 pr-8 sm:pr-0">
                        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg sm:text-xl font-black">{{ $selectedBooking->learners_string }}</h3>
                                <span
                                    class="px-2 py-0.5 rounded-lg bg-white/20 text-[10px] font-bold uppercase tracking-wider">ID:
                                    #{{ $selectedBooking->id }}</span>
                            </div>
                            <p class="text-cyan-100 font-medium text-sm">{{ $selectedBooking->subjects_string }}</p>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-4 sm:p-6 lg:p-8 overflow-y-auto grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">

                    {{-- Column 1: Assignment & Logistics --}}
                    <div class="space-y-6">
                        <section>
                            <h4
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-cyan-500"></i> Assignment
                            </h4>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Client</div>
                                    <div class="text-sm font-bold text-slate-700">
                                        {{ $selectedBooking->client->name ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Assigned Tutor</div>
                                    <div class="text-sm font-bold text-slate-700">
                                        {{ $selectedBooking->tutor->name ?? 'Not Assigned' }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Service Category</div>
                                    <div class="text-sm font-semibold text-cyan-600">
                                        {{ $selectedBooking->serviceItem->name ?? 'Tuition' }}</div>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h4
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-cyan-500"></i> Logistics
                            </h4>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Location</div>
                                    <div class="text-sm text-slate-600 leading-relaxed">
                                        {{ $selectedBooking->location }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Dates</div>
                                    <div class="text-sm text-slate-600 font-medium">
                                        {{ \Carbon\Carbon::parse($selectedBooking->start_date)->format('M d, Y') }} —
                                        {{ \Carbon\Carbon::parse($selectedBooking->end_date)->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    {{-- Column 2: Academics & Learners --}}
                    <div class="space-y-6">
                        <section>
                            <h4
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-book text-cyan-500"></i> Academic Profile
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Classes</div>
                                    <div class="text-sm font-bold text-slate-700">{{ $selectedBooking->classes }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Curriculum</div>
                                    <div class="text-sm font-bold text-slate-700">{{ $selectedBooking->curriculum }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Tutor Gender</div>
                                    <div class="text-sm font-bold text-slate-700">{{ $selectedBooking->tutorGender }}
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h4
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-users text-cyan-500"></i> Learner Details
                            </h4>
                            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 space-y-2">
                                @foreach ($selectedBooking->formatted_learners as $learner)
                                    <div
                                        class="flex justify-between items-center text-sm border-b border-slate-200/50 last:border-0 pb-1 last:pb-0">
                                        <span class="font-medium text-slate-700">{{ $learner['name'] }}</span>
                                        <span class="text-slate-400 text-xs">{{ $learner['age'] }} Years</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    {{-- Column 3: Financials & Schedule --}}
                    <div class="space-y-6">
                        <section>
                            <h4
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-naira-sign text-cyan-500"></i> Billing & Pace
                            </h4>
                            <div class="bg-slate-900 rounded-2xl p-4 text-white shadow-lg">
                                <div class="text-[10px] text-slate-400 font-bold uppercase mb-1">Contract Total</div>
                                <div class="text-2xl font-black mb-2">₦{{ number_format($selectedBooking->amount) }}
                                </div>
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-[10px] font-bold px-2 py-0.5 rounded-lg {{ $selectedBooking->client_payment_status === 'Paid' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">
                                        {{ $selectedBooking->client_payment_status }}
                                    </span>
                                    <span class="text-xs text-slate-400">{{ $selectedBooking->sessions }}
                                        sess/wk</span>
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-slate-500 italic">
                                Session Duration: <span
                                    class="font-bold text-slate-700">{{ $selectedBooking->duration }}</span>
                            </div>
                        </section>

                        <section>
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Schedule
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($selectedBooking->formatted_days_times as $slot)
                                    <span
                                        class="bg-white border border-slate-200 px-2 py-1 rounded-lg text-[11px] font-bold text-slate-600 flex items-center gap-1.5 shadow-sm">
                                        <i class="fa-solid fa-clock text-cyan-500 text-[10px]"></i>
                                        {{ substr($slot['day'], 0, 3) }} at {{ $slot['time'] }}
                                    </span>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    {{-- Remarks Row (Full Width) --}}
                    <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-slate-100 pt-6">
                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100/50">
                            <h5 class="text-[10px] font-black text-amber-600 uppercase mb-2">Internal Tutor Remarks
                            </h5>
                            <p class="text-sm text-amber-800 italic">
                                "{{ $selectedBooking->tutorRemarks ?? 'No remarks provided by tutor.' }}"</p>
                        </div>
                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100/50">
                            <h5 class="text-[10px] font-black text-blue-600 uppercase mb-2">Client Feedback / Approvals
                            </h5>
                            <p class="text-sm text-blue-800 italic">
                                "{{ in_array($selectedBooking->status, ['Pending', 'Adjust', 'Accepted', 'Active', 'Completed']) ? $selectedBooking->clientAcceptanceRemarks : $selectedBooking->clientApprovalRemarks ?? 'No special instructions from client.' }}"
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div
                    class="p-4 sm:p-6 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">
                            Status: <span class="text-cyan-600">{{ $selectedBooking->status }}</span>
                        </div>
                        @if ($selectedBooking->completed_at)
                            <div class="text-[10px] font-bold text-slate-400 uppercase">
                                Completed: <span
                                    class="text-slate-600">{{ \Carbon\Carbon::parse($selectedBooking->completed_at)->format('d M Y') }}</span>
                            </div>
                        @endif
                    </div>
                    <button wire:click="closeDetail"
                        class="w-full sm:w-auto bg-slate-900 hover:bg-black text-white px-4 py-2 rounded-xl font-bold transition-all shadow-lg shadow-slate-200">
                        Close Details
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation --}}
    @if ($showDelete)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div
                class="relative bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-8 max-w-sm w-full text-center shadow-2xl">
                <div
                    class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Delete Booking?</h3>
                <p class="text-slate-500 text-sm mb-8">This action will permanently remove this lesson record and
                    associated payments. This cannot be undone.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button wire:click="$set('showDelete', false)"
                        class="py-3 px-4 bg-slate-100 text-slate-500 font-bold rounded-xl hover:bg-slate-200 transition-all">Cancel</button>
                    <button wire:click="deleteBooking"
                        class="py-3 px-4 bg-rose-500 text-white font-bold rounded-xl hover:bg-rose-600 shadow-lg shadow-rose-200 transition-all">Delete</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Request Selection Modal --}}
    @if ($showAssign)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showAssign', false)"></div>
            <div
                class="relative bg-white rounded-2xl sm:rounded-3xl w-full max-w-3xl max-h-[80vh] overflow-hidden shadow-2xl">
                <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between gap-3">
                    <h3 class="text-xl font-black text-slate-800">Select Pending Request</h3>
                    <button wire:click="$set('showAssign', false)" class="text-slate-400 hover:text-slate-600"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-4 sm:p-6 overflow-y-auto max-h-[calc(80vh-100px)]">
                    <div class="space-y-3">
                        @forelse($tutorRequests as $req)
                            <button wire:click="selectTutorRequest({{ $req->id }})"
                                class="w-full text-left p-4 rounded-2xl border border-slate-100 hover:border-cyan-500 hover:bg-cyan-50/30 transition-all group">
                                <div class="flex flex-col sm:flex-row sm:justify-between items-start gap-2">
                                    <div>
                                        <div class="font-bold text-slate-800 group-hover:text-cyan-700">
                                            {{ $req->user->name ?? 'Unknown User' }}</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            <i class="fa-solid fa-book-open mr-1"></i>
                                            {{ $req->serviceItem->name ?? 'Tuition' }} •
                                            {{ $req->delivery_mode === 'online' ? 'Online' : $req->lesson_address }}
                                        </div>
                                    </div>
                                    <div class="text-xs font-black text-cyan-600 uppercase">Select <i
                                            class="fa-solid fa-chevron-right ml-1"></i></div>
                                </div>
                            </button>
                        @empty
                            <div class="text-center py-10">
                                <i class="fa-solid fa-clipboard-question text-slate-200 text-4xl mb-2"></i>
                                <p class="text-slate-400 font-medium">No pending tutor requests found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Activation Modal --}}
    @if ($showActivationModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-8 max-w-md w-full text-center">
                <div
                    class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Activate Lesson?</h3>
                <p class="text-slate-500 text-sm mb-8">This will mark the lesson as Active and notify the tutor to
                    begin sessions according to the schedule.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button wire:click="$set('showActivationModal', false)"
                        class="py-3 font-bold text-slate-500 bg-slate-100 rounded-xl">Cancel</button>
                    <button wire:click="submitActivation"
                        class="py-2 px-2 font-bold text-white bg-emerald-500 rounded-xl shadow-lg shadow-emerald-100">Activate
                        Now</button>
                </div>
            </div>
        </div>
    @endif
</div>
