<div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 sm:px-6">
    <x-slot name="header">
        <div>
            <h1 class="text-3xl font-black text-slate-800">Intervention <span class="text-cyan-600">Assignments</span>
            </h1>
            <p class="mt-1 text-sm text-slate-500">Track active interventions, resolve review feedback, and update
                completion status.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if (session()->has('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm font-bold text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Search learner or intervention"
                    class="rounded-2xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                <select wire:model.live="statusFilter"
                    class="rounded-2xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed (Awaiting Client)</option>
                    <option value="declined">Declined</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="closed">Closed</option>
                </select>
                <a wire:navigate href="{{ route('tutor.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-bold text-cyan-700 transition hover:border-cyan-200 hover:bg-cyan-50">
                    Back to Dashboard
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($assignments as $assignment)
                @php
                    $enquiry = $assignment->programmeEnquiry;
                    $assignmentTone = match ($assignment->status) {
                        'completed', 'closed' => 'bg-emerald-100 text-emerald-700',
                        'declined', 'cancelled' => 'bg-rose-100 text-rose-700',
                        default => 'bg-cyan-100 text-cyan-700',
                    };
                    $payoutStatus = $assignment->payment?->status ?? 'Not created';
                    $payoutTone = match ($payoutStatus) {
                        'Earned' => 'bg-emerald-100 text-emerald-700',
                        'Pending' => 'bg-amber-100 text-amber-700',
                        'Paid' => 'bg-blue-100 text-blue-700',
                        'Cancelled' => 'bg-rose-100 text-rose-700',
                        default => 'bg-slate-100 text-slate-600',
                    };
                    $displayAssignmentStatus = $assignment->status === 'completed' && $enquiry?->status === 'completed'
                        ? 'closed'
                        : $assignment->status;
                    $declineNote = $enquiry->meta['client_completion_decline_note'] ?? null;
                    $showCompleteAction = in_array($assignment->status, $canBeMarkedComplete, true);
                @endphp

                <article
                    class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-700">
                                {{ $enquiry->programme?->name ?? 'Intervention' }}</p>
                            <h3 class="mt-1 text-lg font-black text-slate-900">
                                {{ $enquiry->learner_name ?: 'Learner pending' }}</h3>
                            <p class="text-xs text-slate-500">
                                {{ $enquiry->class_level ?: 'Learner profile in request details' }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $assignmentTone }}">
                            {{ str_replace('_', ' ', $displayAssignmentStatus) }}
                        </span>
                    </div>

                    <div class="mt-4 rounded-2xl bg-slate-50 p-3 text-xs text-slate-700">
                        <p><span class="font-black uppercase text-slate-400">Subjects:</span>
                            {{ !empty($enquiry->subjects) ? implode(', ', $enquiry->subjects) : 'General support' }}</p>
                        <p class="mt-1"><span class="font-black uppercase text-slate-400">Schedule:</span>
                            {{ $enquiry->preferred_frequency ?: 'Flexible' }} |
                            {{ $enquiry->preferred_duration ?: 'Flexible duration' }}</p>
                        <p class="mt-1"><span class="font-black uppercase text-slate-400">Location:</span>
                            {{ $enquiry->city_area ?: 'City not set' }}, {{ $enquiry->state ?: 'State not set' }}</p>
                        <p class="mt-1"><span class="font-black uppercase text-slate-400">Planned start:</span>
                            {{ $assignment->start_date?->format('d M Y') ?: 'Not set by admin' }}</p>
                        @if ($assignment->status === 'declined' && $declineNote)
                            <p class="mt-1"><span class="font-black uppercase text-slate-400">Client note:</span>
                                {{ $declineNote }}</p>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-4">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $payoutTone }}">
                            Payout: {{ $payoutStatus }}
                        </span>
                        <div class="flex flex-wrap justify-end gap-2">
                            <button wire:click="openDetails({{ $assignment->id }})"
                                class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200">
                                View Details
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div
                    class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white py-14 text-center text-slate-500">
                    No intervention assignments found.
                </div>
            @endforelse
        </section>

        <div>
            {{ $assignments->links() }}
        </div>
    </div>

    @if ($showDetailsModal && $selectedAssignment)
        @php
            $enquiry = $selectedAssignment->programmeEnquiry;
            $displaySelectedAssignmentStatus = $selectedAssignment->status === 'completed' && $enquiry?->status === 'completed'
                ? 'closed'
                : $selectedAssignment->status;
            $statusTone = match ($selectedAssignment->status) {
                'completed', 'closed' => 'bg-emerald-100 text-emerald-700',
                'declined', 'cancelled' => 'bg-rose-100 text-rose-700',
                default => 'bg-cyan-100 text-cyan-700',
            };
            $selectedPayoutAmount = $selectedAssignment->payment?->amount ?? 'Not created';
            $selectedPayoutStatus = $selectedAssignment->payment?->status ?? 'Not created';
        @endphp

        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/65 backdrop-blur-sm" wire:click="closeDetails"></div>
            <div
                class="relative flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div
                    class="flex items-start justify-between gap-3 border-b border-slate-100 bg-slate-900 px-6 py-5 text-white">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200">
                            Assignment #{{ str_pad((string) $selectedAssignment->id, 5, '0', STR_PAD_LEFT) }}
                        </p>
                        <h3 class="mt-1 text-2xl font-black">{{ $enquiry->programme?->name ?? 'Intervention' }}</h3>
                        <p class="mt-1 text-xs text-slate-300">Learner: {{ $enquiry->learner_name }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $statusTone }}">
                            {{ str_replace('_', ' ', $displaySelectedAssignmentStatus) }}
                        </span>
                        <button wire:click="closeDetails"
                            class="rounded-lg px-2 py-1 text-xl leading-none text-slate-300 transition hover:bg-white/10 hover:text-white">&times;</button>
                    </div>
                </div>

                <div class="grid gap-6 overflow-y-auto p-6 lg:grid-cols-3">
                    <section class="space-y-4 lg:col-span-2">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-black uppercase text-slate-400">Learner Snapshot</p>
                                <p class="mt-2 text-sm font-bold text-slate-800">
                                    {{ $enquiry->learner_name ?: 'Learner pending' }}</p>
                                <p class="text-xs text-slate-600">
                                    {{ $enquiry->class_level ?: 'Class level not provided' }}</p>
                                <p class="mt-2 text-xs text-slate-600">School:
                                    {{ $enquiry->school_name ?: 'Not provided' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-black uppercase text-slate-400">Parent / Client Contact</p>
                                <p class="mt-2 text-sm font-bold text-slate-800">
                                    {{ $enquiry->user?->name ?? $enquiry->parent_name ?: 'Not provided' }}</p>
                                <p class="text-xs text-slate-600">Email:
                                    {{ $enquiry->user?->email ?: 'Not available' }}</p>
                                <p class="text-xs text-slate-600">Phone:
                                    {{ $enquiry->user?->userProfile?->phone ?? $enquiry->parent_phone ?: 'Not available' }}
                                </p>
                                <p class="mt-2 text-xs text-slate-600">Address:
                                    {{ $enquiry->parent_address ?: 'Not provided' }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Subjects and Focus Areas</p>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @forelse ($enquiry->subjects ?? [] as $subject)
                                    <span
                                        class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-bold uppercase text-slate-600">{{ $subject }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">No subjects listed.</span>
                                @endforelse
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-2 text-xs text-slate-700">
                                <div>
                                    <p class="font-black uppercase text-slate-400">Weak Areas</p>
                                    <p class="mt-1">{{ $enquiry->weak_areas ?: 'No weak areas provided.' }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Recent Performance Notes</p>
                                    <p class="mt-1">
                                        {{ $enquiry->recent_performance_notes ?: 'No performance notes provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4 text-xs text-slate-700">
                            <p class="text-[10px] font-black uppercase text-slate-400">Delivery Plan</p>
                            <div class="mt-3 grid gap-3 md:grid-cols-3">
                                <div>
                                    <p class="font-black uppercase text-slate-400">Mode</p>
                                    <p class="mt-1">{{ ucfirst($enquiry->lesson_mode) }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Frequency</p>
                                    <p class="mt-1">{{ $enquiry->preferred_frequency ?: 'Flexible' }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Duration</p>
                                    <p class="mt-1">{{ $enquiry->preferred_duration ?: 'Flexible' }}</p>
                                </div>
                            </div>
                            @if (!empty($enquiry->preferred_days) && is_array($enquiry->preferred_days))
                                <div class="mt-3">
                                    <p class="text-[10px] font-black uppercase text-slate-400">Preferred Days</p>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($enquiry->preferred_days as $day)
                                            <span
                                                class="rounded-lg bg-cyan-50 px-2 py-1 text-[10px] font-bold uppercase text-cyan-700">{{ is_array($day) ? $day['day'] ?? 'Selected' : ($day ?: 'Selected') }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <p class="mt-3">Location: {{ $enquiry->city_area ?: 'City not set' }},
                                {{ $enquiry->state ?: 'State not set' }}</p>
                        </div>
                    </section>

                    <aside class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-700">
                            <p class="text-[10px] font-black uppercase text-slate-400">Assignment Status</p>
                            <p class="mt-2">Status: {{ str_replace('_', ' ', $displaySelectedAssignmentStatus) }}</p>
                            <p class="mt-1">Request Stage: {{ str_replace('_', ' ', $enquiry->status === 'completed' ? 'closed' : $enquiry->status) }}</p>
                            <p class="mt-1">Tutor Payout: {{ $selectedPayoutStatus }}</p>
                            <p class="mt-1">Earning Amount:
                                {{ $selectedPayoutAmount ? 'NGN ' . number_format((float) $selectedPayoutAmount, 2) : 'Pending quote' }}
                            </p>
                            <p class="mt-1">Planned Start Date:
                                {{ $selectedAssignment->start_date?->format('d M Y') ?: 'Not set by admin' }}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Assignment Timeline</p>
                            <div class="mt-3 space-y-2 text-xs text-slate-700">
                                <p>Assigned:
                                    {{ $selectedAssignment->created_at?->format('d M Y, H:i') ?: 'Not available' }}</p>
                                <p>Planned Start Date:
                                    {{ $selectedAssignment->start_date?->format('d M Y') ?: 'Not set by admin' }}</p>
                                <p>Started:
                                    {{ $selectedAssignment->started_at?->format('d M Y, H:i') ?: 'Not started' }}</p>
                                <p>Completed:
                                    {{ $selectedAssignment->completed_at?->format('d M Y, H:i') ?: 'Not completed' }}
                                </p>
                            </div>
                        </div>

                        @php
                            $modalShowComplete = in_array($selectedAssignment->status, $canBeMarkedComplete, true);
                        @endphp

                        @if ($modalShowComplete)
                            <button wire:click="complete({{ $selectedAssignment->id }})" wire:loading.attr="disabled"
                                wire:target="complete"
                                class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-700">
                                Mark Assignment Complete
                            </button>
                        @elseif ($selectedAssignment->status === 'completed' && $enquiry->status === 'pending_client_review')
                            <div class="rounded-xl bg-amber-50 p-3 text-xs font-bold text-amber-700">
                                Marked complete. Waiting for client approval.
                            </div>
                        @elseif ($selectedAssignment->status === 'completed' && $enquiry->status === 'completed')
                            <div class="rounded-xl bg-emerald-50 p-3 text-xs font-bold text-emerald-700">
                                Closed. Client approved completion.
                            </div>
                        @endif
                    </aside>
                </div>
            </div>
        </div>
    @endif

    @if ($showCompletedModal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/65 backdrop-blur-sm" wire:click="closeCompleteModal"></div>
            <div class="relative w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl">
                <h3 class="text-xl font-black text-slate-900">Complete Intervention Session</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Provide a clear completion remark for the client review.
                </p>
                <textarea wire:model.defer="completionRemark" rows="5" class="mt-4 w-full rounded-2xl border-slate-200 text-sm"
                    placeholder="Summarize session delivery, coverage, outcomes, and any follow-up needed."></textarea>
                @error('completionRemark')
                    <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                @enderror
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="closeCompleteModal"
                        class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200">
                        Cancel
                    </button>
                    <button wire:click="submitCompletion" wire:loading.attr="disabled" wire:target="submitCompletion"
                        class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-700">
                        Submit Completion
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
