<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Booking Manager</h2>
            <p class="text-sm text-slate-500">Create and manage academic lessons and tutor assignments.</p>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="openAssign"
                class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg shadow-sm transition">New from
                Request</button>
            <button wire:click="openCreate"
                class="bg-white border border-slate-300 px-4 py-2 rounded-lg shadow-sm hover:bg-slate-50 transition">Manual
                Booking</button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <div class="flex-1 min-w-[300px]">
            <input wire:model.debounce.300ms="search" placeholder="Search by learner, client, or tutor name..."
                class="w-full rounded-lg px-4 py-2 border border-slate-300 focus:ring-2 focus:ring-cyan-500 outline-none" />
        </div>
        <select wire:model="status" class="px-4 py-2 rounded-lg border border-slate-300 outline-none">
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="accepted">Accepted</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="declined">Declined</option>
        </select>
    </div>

    {{-- Table List --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div
            class="grid grid-cols-12 gap-2 p-4 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600 border-b">
            <div class="col-span-3">Learners / Subjects</div>
            <div class="col-span-3">Client</div>
            <div class="col-span-2">Tutor</div>
            <div class="col-span-2 text-center">Status</div>
            <div class="col-span-2 text-right">Actions</div>
        </div>

        @forelse($bookings as $booking)
            <div wire:key="booking-{{ $booking->id }}"
                class="grid grid-cols-12 gap-2 p-4 items-center border-b last:border-0 hover:bg-slate-50 transition">
                <div class="col-span-3">
                    <div class="font-medium text-slate-800">{{ $booking->learners ?: 'N/A' }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ $booking->subjects }}</div>
                </div>
                <div class="col-span-3">
                    <div class="text-sm font-semibold text-cyan-700">{{ optional($booking->client)->name }}</div>
                    <div class="text-xs text-slate-400">{{ optional($booking->client)->email }}</div>
                </div>
                <div class="col-span-2">
                    @if ($booking->tutor)
                        <div class="text-sm text-slate-700">{{ $booking->tutor->name }}</div>
                    @else
                        <span class="text-xs italic text-red-400">Not Assigned</span>
                    @endif
                </div>
                <div class="col-span-2 text-center">
                    <span
                        class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase
                        {{ $booking->status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $booking->status }}
                    </span>
                </div>
                <div class="col-span-2 flex justify-end gap-2">
                    <button wire:click="showBooking({{ $booking->id }})"
                        class="p-2 text-slate-500 hover:bg-slate-200 rounded-md" title="View Details">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                    @if ($booking->status === 'Accepted')
                        <button wire:click="editActivation({{ $booking->id }})"
                            class="px-2 py-1 bg-cyan-600 text-white text-[10px] rounded hover:bg-cyan-700 font-bold">ACTIVATE</button>
                    @endif
                    <button wire:click="editBooking({{ $booking->id }})"
                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-md" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-slate-400">No bookings found matching your criteria.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>

    {{-- Main Form Modal --}}
    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                <div class="p-6 border-b flex justify-between items-center bg-cyan-800 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-cyan-50">
                        {{ $editingId ? 'Edit Booking Assignment' : 'New Booking Creation' }}</h3>
                    <button wire:click="$set('showForm', false)"
                        class="text-slate-200 hover:text-slate-50 bg-cyan-700 hover:bg-cyan-600 p-2 rounded-full transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveBooking" class="overflow-y-auto p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Section: Stakeholders --}}
                        <div class="md:col-span-3">
                            <h4 class="text-xs font-bold text-cyan-600 uppercase tracking-widest mb-3">User Assignment
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold mb-1">Client (Payer)</label>
                                    <select wire:model="client_id"
                                        class="w-full rounded-lg border-slate-300 py-2 focus:ring-cyan-500">
                                        <option value="">-- Select Client --</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}
                                                ({{ $client->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold mb-1">Assign Tutor</label>
                                    <select wire:model="tutor_id"
                                        class="w-full rounded-lg border-slate-300 py-2 focus:ring-cyan-500">
                                        <option value="">-- No Tutor Assigned --</option>
                                        @foreach ($tutors as $tutor)
                                            <option value="{{ $tutor->id }}">{{ $tutor->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('tutor_id')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section: Logistics --}}
                        <div class="md:col-span-3 pt-4 border-t">
                            <h4 class="text-xs font-bold text-cyan-600 uppercase tracking-widest mb-3">Scheduling &
                                Location</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Start Date</label>
                            <input type="date" wire:model="start_date"
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('start_date')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">End Date</label>
                            <input type="date" wire:model="end_date"
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('end_date')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Location</label>
                            <input type="text" wire:model="location" placeholder="Address or Online Link"
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('location')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Days & Times</label>
                            <input type="text" wire:model="days_times" placeholder="e.g. Mon, Wed @ 4pm"
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('days_times')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Sessions (Count)</label>
                            <input type="number" wire:model="sessions"
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('sessions')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Duration</label>
                            <input type="text" wire:model="duration" placeholder="e.g. 2 hours"
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('duration')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Section: Academic --}}
                        <div class="md:col-span-3 pt-4 border-t">
                            <h4 class="text-xs font-bold text-cyan-600 uppercase tracking-widest mb-3">Academic Details
                            </h4>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Subjects</label>
                            <input type="text" wire:model="subjects" placeholder="Mathematics, Physics..."
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('subjects')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Curriculum</label>
                            <select wire:model="curriculum" class="w-full rounded-lg border-slate-300 py-2">
                                <option value="British">British</option>
                                <option value="Nigerian">Nigerian</option>
                                <option value="French">French</option>
                                <option value="Blended">Blended</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Learners</label>
                            <input type="text" wire:model="learners" placeholder="Names/Ages"
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('learners')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Level/Class</label>
                            <input type="text" wire:model="classes" placeholder="JSS1, Grade 3..."
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('classes')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tutor Gender</label>
                            <select wire:model="tutorGender" class="w-full rounded-lg border-slate-300 py-2">
                                <option value="Any">Any</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        {{-- Section: Financial --}}
                        <div class="md:col-span-3 pt-4 border-t">
                            <h4 class="text-xs font-bold text-cyan-600 uppercase tracking-widest mb-3">Financials &
                                Status</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Total Amount (₦)</label>
                            <input type="number" wire:model="amount"
                                class="w-full rounded-lg border-slate-300 py-2" />
                            @error('amount')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Payment Status</label>
                            <select wire:model="paymentStatus" class="w-full rounded-lg border-slate-300 py-2">
                                <option value="Pending">Pending</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Booking Status</label>
                            <select wire:model="status_field" class="w-full rounded-lg border-slate-300 py-2">
                                <option value="Pending">Pending</option>
                                <option value="Accepted">Accepted</option>
                                <option value="Active">Active</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 bg-cyan-50 -mx-6 -mb-6 p-6 rounded-b-2xl border-t">
                        <button type="button" wire:click="$set('showForm', false)"
                            class="px-6 py-2 rounded-lg font-semibold text-slate-600 hover:bg-slate-200 transition">Cancel</button>
                        <button type="submit"
                            class="px-8 py-2 rounded-lg font-bold text-white bg-cyan-600 hover:bg-cyan-700 shadow-lg shadow-cyan-200 transition">
                            {{ $editingId ? 'Update Booking' : 'Create Booking' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if ($showDetail && $selectedBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white p-8 rounded-2xl w-full max-w-2xl shadow-2xl relative">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <span class="text-[10px] font-bold text-cyan-600 tracking-tighter uppercase">Booking
                            Details</span>
                        <h3 class="text-2xl font-black text-slate-800">Review Lesson</h3>
                    </div>
                    <button wire:click="closeDetail" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-y-6 gap-x-8 text-sm border-t pt-6">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-[10px] mb-1">Client</label>
                        <p class="font-semibold text-slate-800">{{ optional($selectedBooking->client)->name }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-[10px] mb-1">Tutor</label>
                        <p class="font-semibold text-slate-800">
                            {{ optional($selectedBooking->tutor)->name ?: 'UNASSIGNED' }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-[10px] mb-1">Logistics</label>
                        <p class="text-slate-700">
                            {{ $selectedBooking->location }}<br>{{ $selectedBooking->days_times }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-slate-400 font-bold uppercase text-[10px] mb-1">Duration/Sessions</label>
                        <p class="text-slate-700">{{ $selectedBooking->sessions }} sessions
                            ({{ $selectedBooking->duration }})</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-[10px] mb-1">Amount</label>
                        <p class="text-lg font-black text-cyan-600">₦{{ number_format($selectedBooking->amount) }}</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-[10px] mb-1">Payment</label>
                        <p class="font-bold text-slate-700">{{ $selectedBooking->paymentStatus }}</p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button wire:click="closeDetail"
                        class="px-6 py-2 bg-slate-100 font-bold text-slate-600 rounded-lg hover:bg-slate-200">Close</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Assign Modal --}}
    @if ($showAssign)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div class="bg-white rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl">
                <div class="p-6 bg-slate-50 border-b flex justify-between">
                    <h3 class="font-bold text-slate-800">Pending Tutor Requests</h3>
                    <button wire:click="$set('showAssign', false)" class="text-slate-400">✕</button>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    @forelse($tutorRequests as $tr)
                        <div class="p-4 border-b hover:bg-cyan-50 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-slate-700">#{{ $tr->id }} -
                                    {{ $tr->serviceItem?->name }}</div>
                                <div class="text-xs text-slate-500">{{ Str::limit($tr->additional_notes, 80) }}</div>
                                <div class="mt-1 text-[10px] font-bold text-cyan-600 uppercase">Budget:
                                    ₦{{ number_format($tr->budget_max) }}</div>
                            </div>
                            <button wire:click="selectTutorRequest({{ $tr->id }})"
                                class="px-4 py-2 bg-white border border-cyan-600 text-cyan-600 rounded-lg font-bold text-xs group-hover:bg-cyan-600 group-hover:text-white transition">CONVERT</button>
                        </div>
                    @empty
                        <div class="p-12 text-center text-slate-400">No pending requests available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Activation Confirmation --}}
    @if ($showActivationModal && $selectedBooking)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white p-6 rounded-2xl w-96 text-center">
                <div
                    class="w-16 h-16 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="font-black text-xl text-slate-800 mb-2">Activate Lesson?</h3>
                <p class="text-sm text-slate-500 mb-6">This will notify
                    <strong>{{ $selectedBooking->tutor?->name }}</strong> and mark the lesson as Active.
                </p>
                <div class="flex gap-3">
                    <button wire:click="$set('showActivationModal', false)"
                        class="flex-1 py-2 font-bold text-slate-400 hover:bg-slate-100 rounded-lg">Cancel</button>
                    <button wire:click="submitActivation"
                        class="flex-1 py-2 font-bold text-white bg-cyan-600 rounded-lg shadow-lg shadow-cyan-200">Yes,
                        Activate</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white p-6 rounded-2xl w-96 text-center">
                <div
                    class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="font-black text-xl text-slate-800 mb-2">Delete Booking?</h3>
                <p class="text-sm text-slate-500 mb-6">This will remove all associated records and payments. This
                    cannot be undone.</p>
                <div class="flex gap-3">
                    <button wire:click="$set('showDelete', false)"
                        class="flex-1 py-2 font-bold text-slate-400 hover:bg-slate-100 rounded-lg">Cancel</button>
                    <button wire:click="deleteBooking"
                        class="flex-1 py-2 font-bold text-white bg-red-600 rounded-lg shadow-lg shadow-red-200">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
