<div class="min-h-screen bg-slate-50 p-3 sm:p-4 lg:p-6">
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <h2 class="text-2xl font-black tracking-tight text-slate-800">Intervention <span class="text-cyan-600">Enquiries</span></h2>
            <p class="text-sm text-slate-500">Review incoming intervention requests, assign tutors, and update delivery status.</p>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="grid gap-3 md:grid-cols-4">
                <div class="md:col-span-2">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search learner, parent, contact, or intervention"
                        class="w-full rounded-2xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500" />
                </div>
                <select wire:model.live="status" class="w-full rounded-2xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">All statuses</option>
                    <option value="new">New</option>
                    <option value="pending">Pending</option>
                    <option value="reviewing">Reviewing</option>
                    <option value="matched">Matched</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select wire:model.live="paymentStatus" class="w-full rounded-2xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">All payment states</option>
                    <option value="pending">Payment Pending</option>
                    <option value="paid">Payment Paid</option>
                    <option value="failed">Payment Failed</option>
                </select>
            </div>

            <div class="mt-3 flex justify-end">
                <select wire:model.live="perPage" class="rounded-xl border-slate-200 text-xs font-bold text-slate-600">
                    <option value="10">10 per page</option>
                    <option value="15">15 per page</option>
                    <option value="30">30 per page</option>
                </select>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($enquiries as $enquiry)
                @php
                    $statusTone = match ($enquiry->status) {
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-rose-100 text-rose-700',
                        'matched', 'in_progress' => 'bg-cyan-100 text-cyan-700',
                        default => 'bg-amber-100 text-amber-700',
                    };
                    $paymentTone = ($enquiry->payment_status ?? 'pending') === 'paid'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-slate-100 text-slate-600';
                    $assignedTutor = $enquiry->activeAssignment?->tutor;
                @endphp

                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-700">
                                #INT-{{ str_pad((string) $enquiry->id, 5, '0', STR_PAD_LEFT) }}
                            </p>
                            <h3 class="mt-1 text-lg font-black text-slate-900">{{ $enquiry->programme?->name ?? 'Intervention' }}</h3>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $statusTone }}">
                            {{ str_replace('_', ' ', $enquiry->status) }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[10px] font-black uppercase text-slate-400">Learner</p>
                            <p class="mt-1 font-bold text-slate-800">{{ $enquiry->learner_name }}</p>
                            @if ($enquiry->class_level)
                                <p class="text-xs text-slate-500">{{ $enquiry->class_level }}</p>
                            @endif
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[10px] font-black uppercase text-slate-400">Parent / Guardian</p>
                            <p class="mt-1 font-bold text-slate-800">{{ $enquiry->user?->name ?? $enquiry->parent_name ?: 'Not provided' }}</p>
                            <p class="text-xs text-slate-500">{{ $enquiry->user?->userProfile?->phone ?? $enquiry->parent_phone ?: 'No phone' }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-[10px] font-black uppercase text-slate-400">Subjects</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @forelse ($enquiry->subjects ?? [] as $subject)
                                <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-bold uppercase text-slate-600">{{ $subject }}</span>
                            @empty
                                <span class="text-xs text-slate-400">No subjects listed</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs text-slate-600">
                        <div>
                            <p class="font-black uppercase text-slate-400">Preference</p>
                            <p class="mt-1">{{ ucfirst($enquiry->lesson_mode) }} | {{ $enquiry->preferred_frequency ?: 'Flexible' }}</p>
                            <p>{{ $enquiry->preferred_duration ?: 'Flexible duration' }}</p>
                        </div>
                        <div>
                            <p class="font-black uppercase text-slate-400">Location</p>
                            <p class="mt-1">{{ $enquiry->city_area ?: 'City not set' }}, {{ $enquiry->state ?: 'State not set' }}</p>
                            <p>{{ $enquiry->created_at?->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-4">
                        <div class="space-y-1">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $paymentTone }}">
                                {{ ucfirst($enquiry->payment_status ?? 'pending') }} payment
                            </span>
                            <p class="text-xs text-slate-500">
                                {{ $assignedTutor ? 'Tutor: ' . ($assignedTutor->tutorProfile?->fullName ?? $assignedTutor->name ?? 'Not assigned') : 'Tutor pending assignment' }}
                            </p>
                        </div>
                        <button wire:click="openDetails({{ $enquiry->id }})"
                            class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-bold text-white transition hover:bg-black">
                            Manage
                        </button>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-slate-500">
                    No intervention enquiries found for the selected filters.
                </div>
            @endforelse
        </section>

        <div>
            {{ $enquiries->links() }}
        </div>
    </section>

    @if ($showDetailsModal && $selectedEnquiry)
        @php
            $selectedId = $selectedEnquiry->id;
            $latestSelectedAssignment = $selectedEnquiry->assignments->sortByDesc('id')->first();
            $statusTone = match ($selectedEnquiry->status) {
                'completed' => 'bg-emerald-100 text-emerald-700',
                'cancelled' => 'bg-rose-100 text-rose-700',
                'matched', 'in_progress' => 'bg-cyan-100 text-cyan-700',
                default => 'bg-amber-100 text-amber-700',
            };
            $paymentTone = ($selectedEnquiry->payment_status ?? 'pending') === 'paid'
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-slate-100 text-slate-600';
        @endphp

        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/65 backdrop-blur-sm" wire:click="closeDetails"></div>
            <div class="relative flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 bg-slate-900 px-6 py-5 text-white">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200">
                            #INT-{{ str_pad((string) $selectedEnquiry->id, 5, '0', STR_PAD_LEFT) }}
                        </p>
                        <h3 class="mt-1 text-2xl font-black">{{ $selectedEnquiry->programme?->name ?? 'Intervention' }}</h3>
                        <p class="mt-1 text-xs text-slate-300">Submitted {{ $selectedEnquiry->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $statusTone }}">
                            {{ str_replace('_', ' ', $selectedEnquiry->status) }}
                        </span>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $paymentTone }}">
                            {{ $selectedEnquiry->payment_status ?? 'pending' }} payment
                        </span>
                        <button wire:click="closeDetails" class="rounded-lg px-2 py-1 text-xl leading-none text-slate-300 transition hover:bg-white/10 hover:text-white">&times;</button>
                    </div>
                </div>

                <div class="grid gap-6 overflow-y-auto p-6 lg:grid-cols-3">
                    <section class="space-y-4 lg:col-span-2">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-black uppercase text-slate-400">Learner Profile</p>
                                <p class="mt-2 text-sm font-bold text-slate-800">{{ $selectedEnquiry->learner_name }}</p>
                                <p class="text-xs text-slate-600">{{ $selectedEnquiry->class_level ?: 'Class level not provided' }}</p>
                                <p class="mt-2 text-xs text-slate-600">School: {{ $selectedEnquiry->school_name ?: 'Not provided' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-black uppercase text-slate-400">Parent / Account Contact</p>
                                <p class="mt-2 text-sm font-bold text-slate-800">{{ $selectedEnquiry->user?->name ?? $selectedEnquiry->parent_name ?: 'Not provided' }}</p>
                                <p class="text-xs text-slate-600">Email: {{ $selectedEnquiry->user?->email ?: 'Not available' }}</p>
                                <p class="text-xs text-slate-600">Phone: {{ $selectedEnquiry->user?->userProfile?->phone ?? $selectedEnquiry->parent_phone ?: 'Not available' }}</p>
                                <p class="mt-2 text-xs text-slate-600">Address: {{ $selectedEnquiry->parent_address ?: 'Not provided' }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Subjects and Learning Focus</p>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @forelse ($selectedEnquiry->subjects ?? [] as $subject)
                                    <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-bold uppercase text-slate-600">{{ $subject }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">No subjects provided.</span>
                                @endforelse
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-2 text-xs text-slate-700">
                                <div>
                                    <p class="font-black uppercase text-slate-400">Weak Areas</p>
                                    <p class="mt-1">{{ $selectedEnquiry->weak_areas ?: 'No weak areas provided.' }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Recent Performance Notes</p>
                                    <p class="mt-1">{{ $selectedEnquiry->recent_performance_notes ?: 'No performance notes provided.' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Schedule and Location</p>
                            <div class="mt-3 grid gap-3 text-xs text-slate-700 md:grid-cols-3">
                                <div>
                                    <p class="font-black uppercase text-slate-400">Lesson Mode</p>
                                    <p class="mt-1">{{ ucfirst($selectedEnquiry->lesson_mode) }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Frequency</p>
                                    <p class="mt-1">{{ $selectedEnquiry->preferred_frequency ?: 'Flexible' }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Duration</p>
                                    <p class="mt-1">{{ $selectedEnquiry->preferred_duration ?: 'Flexible' }}</p>
                                </div>
                            </div>

                            @if (!empty($selectedEnquiry->preferred_days) && is_array($selectedEnquiry->preferred_days))
                                <div class="mt-3">
                                    <p class="text-[10px] font-black uppercase text-slate-400">Preferred Days</p>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($selectedEnquiry->preferred_days as $day)
                                            <span class="rounded-lg bg-cyan-50 px-2 py-1 text-[10px] font-bold uppercase text-cyan-700">{{ is_array($day) ? ($day['day'] ?? 'Selected') : ($day ?: 'Selected') }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <p class="mt-3 text-xs text-slate-600">
                                Location: {{ $selectedEnquiry->city_area ?: 'City not provided' }}, {{ $selectedEnquiry->state ?: 'State not provided' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Assignment History</p>
                            <div class="mt-3 space-y-2">
                                @forelse ($selectedEnquiry->assignments->sortByDesc('id') as $assignment)
                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                        <div>
                                            <p class="font-bold text-slate-800">
                                                {{ $assignment->tutor?->tutorProfile?->fullName ?? $assignment->tutor?->name ?? 'Unassigned tutor' }}
                                            </p>
                                            <p class="text-slate-500">Assigned by {{ $assignment->assignedBy?->name ?? 'System' }}</p>
                                            <p class="text-slate-500">Planned start: {{ $assignment->start_date?->format('d M Y') ?? 'Not set' }}</p>
                                        </div>
                                        <span class="rounded-full bg-slate-200 px-2 py-1 text-[10px] font-black uppercase text-slate-700">
                                            {{ str_replace('_', ' ', $assignment->status) }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400">No assignment history yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    <aside class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Update Request Status</p>
                            <select wire:model="statusInputs.{{ $selectedId }}" class="mt-2 w-full rounded-xl border-slate-200 text-sm">
                                <option value="new">New</option>
                                <option value="pending">Pending</option>
                                <option value="reviewing">Reviewing</option>
                                <option value="matched">Matched</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <button wire:click="updateStatus({{ $selectedId }})" wire:loading.attr="disabled" wire:target="updateStatus"
                                class="mt-3 w-full rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-black disabled:opacity-60">
                                Save Status
                            </button>
                            @error('statusInputs.' . $selectedId)
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Quote and Planned Start</p>
                            <label class="mt-3 block text-[10px] font-black uppercase text-slate-400">Quote (NGN)</label>
                            <input type="number" step="0.01" wire:model.live="priceInputs.{{ $selectedId }}"
                                class="mt-1 w-full rounded-xl border-slate-200 text-sm" placeholder="Enter approved quote" />

                            <label class="mt-3 block text-[10px] font-black uppercase text-slate-400">Planned Start Date</label>
                            <input type="date" wire:model="startDateInputs.{{ $selectedId }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm" />
                            @error('startDateInputs.' . $selectedId)
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror

                            <button wire:click="saveQuoteAndDate({{ $selectedId }})" wire:loading.attr="disabled" wire:target="saveQuoteAndDate"
                                class="mt-3 w-full rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-black disabled:opacity-60">
                                Save Quote and Date
                            </button>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Reassign Tutor</p>
                            <p class="mt-2 text-[11px] text-slate-500">Reassignment cancels the current active assignment and creates a new one.</p>

                            <label class="mt-3 block text-[10px] font-black uppercase text-slate-400">Tutor</label>
                            <select wire:model="tutorInputs.{{ $selectedId }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm">
                                <option value="">Select a tutor</option>
                                @foreach ($tutors as $tutor)
                                    <option value="{{ $tutor->id }}">{{ $tutor->tutorProfile?->fullName ?? $tutor->name ?? 'Tutor' }}</option>
                                @endforeach
                            </select>

                            <label class="mt-3 block text-[10px] font-black uppercase text-slate-400">Admin Note (optional)</label>
                            <textarea wire:model.defer="adminNotes.{{ $selectedId }}" rows="3" class="mt-1 w-full rounded-xl border-slate-200 text-sm"
                                placeholder="Add onboarding or assignment notes for tutor"></textarea>

                            <button wire:click="reassignTutor({{ $selectedId }})" wire:loading.attr="disabled" wire:target="reassignTutor"
                                class="mt-3 w-full rounded-xl bg-cyan-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-cyan-700 disabled:opacity-60">
                                Reassign Tutor
                            </button>
                            @error('tutorInputs.' . $selectedId)
                                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
                            <p class="font-black uppercase text-slate-400">Payment and Quote Summary</p>
                            <p class="mt-2">Quote: {{ $selectedEnquiry->price_quote ? 'NGN ' . number_format((float) $selectedEnquiry->price_quote, 2) : 'Not set' }}</p>
                            <p class="mt-1">Payment: {{ ucfirst($selectedEnquiry->payment_status ?? 'pending') }}</p>
                            <p class="mt-1">Reference: {{ $selectedEnquiry->payment_reference ?: 'Not available' }}</p>
                            <p class="mt-1">Planned Start: {{ $latestSelectedAssignment?->start_date?->format('d M Y') ?? 'Not set' }}</p>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    @endif
</div>
