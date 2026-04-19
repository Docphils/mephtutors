<div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 sm:px-6">
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-black text-slate-800">Intervention <span class="text-cyan-600">Requests</span></h1>
                <p class="mt-1 text-sm text-slate-500">Track progress, payment, tutor assignment, and delivery milestones for each request.</p>
            </div>
            <a wire:navigate href="{{ route('client.interventions.create') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-4 py-2 text-sm font-black text-white transition hover:bg-cyan-700">
                New Intervention Request
            </a>
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
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search learner or intervention"
                    class="rounded-2xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                <select wire:model.live="statusFilter" class="rounded-2xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">All statuses</option>
                    <option value="new">New</option>
                    <option value="pending">Pending</option>
                    <option value="reviewing">Reviewing</option>
                    <option value="matched">Matched</option>
                    <option value="in_progress">In Progress</option>
                    <option value="pending_client_review">Pending Client Review</option>
                    <option value="completed">Closed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select wire:model.live="paymentFilter" class="rounded-2xl border-slate-200 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">All payment states</option>
                    <option value="pending">Payment Pending</option>
                    <option value="paid">Payment Paid</option>
                    <option value="failed">Payment Failed</option>
                </select>
                <a wire:navigate href="{{ route('client.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-bold text-cyan-700 transition hover:border-cyan-200 hover:bg-cyan-50">
                    Back to Dashboard
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($requests as $request)
                @php
                    $latestAssignment = $request->assignments->sortByDesc('id')->first() ?? $request->activeAssignment;
                    $cardTutor = $latestAssignment?->tutor;
                    $cardTutorProfile = $cardTutor?->tutorProfile;
                    $statusTone = match ($request->status) {
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-rose-100 text-rose-700',
                        'matched', 'in_progress', 'pending_client_review' => 'bg-cyan-100 text-cyan-700',
                        default => 'bg-amber-100 text-amber-700',
                    };
                    $paymentTone = ($request->payment_status ?? 'pending') === 'paid'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-slate-100 text-slate-600';
                    $requestStatusLabel = $request->status === 'completed' ? 'closed' : $request->status;
                    $canPayNow = $request->status === 'matched'
                        && ($request->payment_status ?? 'pending') !== 'paid'
                        && (float) ($request->price_quote ?? 0) > 0
                        && $latestAssignment;
                @endphp

                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-700">{{ $request->programme?->name ?? 'Intervention' }}</p>
                            <h3 class="mt-1 text-lg font-black text-slate-900">{{ $request->learner_name ?: 'Learner pending' }}</h3>
                            <p class="text-xs text-slate-500">Submitted {{ $request->created_at?->format('d M Y') }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $statusTone }}">
                            {{ str_replace('_', ' ', $requestStatusLabel) }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs text-slate-700">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="font-black uppercase text-slate-400">Subjects</p>
                            <p class="mt-1">{{ !empty($request->subjects) ? implode(', ', $request->subjects) : 'General support' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="font-black uppercase text-slate-400">Tutor</p>
                            <p class="mt-1">
                                {{ $cardTutorProfile?->fullName ?? $cardTutor?->name ?? 'Pending assignment' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs text-slate-600">
                        <div>
                            <p class="font-black uppercase text-slate-400">Schedule</p>
                            <p class="mt-1">{{ $request->preferred_frequency ?: 'Flexible' }}</p>
                            <p>{{ $request->preferred_duration ?: 'Flexible duration' }}</p>
                        </div>
                        <div>
                            <p class="font-black uppercase text-slate-400">Quote</p>
                            @if (in_array($request->status, ['matched', 'in_progress', 'pending_client_review', 'completed'], true) && $request->price_quote)
                                <p class="mt-1">NGN {{ number_format((float) $request->price_quote, 2) }}</p>
                            @else
                                <p class="mt-1 text-slate-400">Provided after review</p>
                            @endif
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Planned Start: {{ $latestAssignment?->start_date?->format('d M Y') ?: 'Not set yet' }}</p>

                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-4">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $paymentTone }}">
                            {{ ucfirst($request->payment_status ?? 'pending') }} payment
                        </span>
                        <div class="flex flex-wrap justify-end gap-2">
                            <button wire:click="openDetails({{ $request->id }})" wire:loading.attr="disabled" wire:target="openDetails"
                                class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200">
                                View
                            </button>
                            @if ($canPayNow)
                                <button wire:click="payNow({{ $request->id }})" wire:loading.attr="disabled" wire:target="payNow"
                                    class="rounded-xl bg-cyan-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-cyan-700 disabled:opacity-60">
                                    Pay Now
                                </button>
                            @endif
                            @if (in_array($request->status, ['new', 'pending', 'reviewing'], true))
                                <button wire:click="cancelRequest({{ $request->id }})" wire:confirm="Cancel this intervention request?"
                                    wire:loading.attr="disabled" wire:target="cancelRequest"
                                    class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                    Cancel
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white py-14 text-center text-slate-500">
                    No intervention requests yet.
                </div>
            @endforelse
        </section>

        <div>
            {{ $requests->links() }}
        </div>
    </div>

    @if ($showDetails && $selectedRequest)
        @php
            $latestAssignment = $selectedRequest->assignments->sortByDesc('id')->first() ?? $selectedRequest->activeAssignment;
            $pendingReviewAssignment = $selectedRequest->status === 'pending_client_review'
                ? $selectedRequest->assignments
                    ->sortByDesc('id')
                    ->first(fn ($assignment) => in_array($assignment->status, ['completed', 'pending_client_review'], true) && $assignment->completed_at)
                : null;
            $canPayFromDetails = $selectedRequest->status === 'matched'
                && ($selectedRequest->payment_status ?? 'pending') !== 'paid'
                && (float) ($selectedRequest->price_quote ?? 0) > 0
                && $latestAssignment;
            $clientAdjustmentNote = $selectedRequest->meta['client_adjustment_note'] ?? null;
            $adminAdjustmentNote = $latestAssignment?->admin_notes ?? ($selectedRequest->meta['admin_adjustment_note'] ?? null);
            $tutor = $latestAssignment?->tutor;
            $tutorProfile = $tutor?->tutorProfile;
            $tutorPhone = $tutorProfile?->phone ?: $tutor?->userProfile?->phone ?: 'Not available yet';
            $tutorGender = $tutorProfile?->gender ?: $tutor?->userProfile?->gender ?: 'Not specified';
            $tutorImage = $tutorProfile?->image ?: $tutor?->userProfile?->image;
            $tutorImageUrl = $tutorImage
                ? (\Illuminate\Support\Str::startsWith($tutorImage, ['http://', 'https://', '/']) ? $tutorImage : asset('storage/' . ltrim($tutorImage, '/')))
                : null;
            $statusTone = match ($selectedRequest->status) {
                'completed' => 'bg-emerald-100 text-emerald-700',
                'cancelled' => 'bg-rose-100 text-rose-700',
                'matched', 'in_progress', 'pending_client_review' => 'bg-cyan-100 text-cyan-700',
                default => 'bg-amber-100 text-amber-700',
            };
            $selectedRequestStatusLabel = $selectedRequest->status === 'completed' ? 'closed' : $selectedRequest->status;
            $paymentTone = ($selectedRequest->payment_status ?? 'pending') === 'paid'
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-slate-100 text-slate-600';
            $assignmentStatusLabel = $latestAssignment
                ? ($latestAssignment->status === 'completed' && $selectedRequest->status === 'completed' ? 'closed' : $latestAssignment->status)
                : null;
        @endphp

        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/65 backdrop-blur-sm" wire:click="closeDetails"></div>
            <div class="relative flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 bg-slate-900 px-6 py-5 text-white">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200">Request #{{ str_pad((string) $selectedRequest->id, 5, '0', STR_PAD_LEFT) }}</p>
                        <h2 class="mt-1 text-2xl font-black">{{ $selectedRequest->programme?->name ?? 'Intervention' }}</h2>
                        <p class="mt-1 text-xs text-slate-300">Submitted {{ $selectedRequest->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $statusTone }}">
                            {{ str_replace('_', ' ', $selectedRequestStatusLabel) }}
                        </span>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $paymentTone }}">
                            {{ ucfirst($selectedRequest->payment_status ?? 'pending') }} payment
                        </span>
                        <button wire:click="closeDetails" class="rounded-lg px-2 py-1 text-xl leading-none text-slate-300 transition hover:bg-white/10 hover:text-white">&times;</button>
                    </div>
                </div>

                <div class="grid gap-6 overflow-y-auto p-6 lg:grid-cols-3">
                    <section class="space-y-4 lg:col-span-2">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-black uppercase text-slate-400">Learner Profile</p>
                                <p class="mt-2 text-sm font-bold text-slate-800">{{ $selectedRequest->learner_name ?: 'Learner pending' }}</p>
                                <p class="text-xs text-slate-600">{{ $selectedRequest->class_level ?: 'Class level not provided' }}</p>
                                <p class="mt-2 text-xs text-slate-600">School: {{ $selectedRequest->school_name ?: 'Not provided' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-black uppercase text-slate-400">Account Contact</p>
                                <p class="mt-2 text-sm font-bold text-slate-800">{{ auth()->user()?->name }}</p>
                                <p class="text-xs text-slate-600">Email: {{ auth()->user()?->email }}</p>
                                <p class="text-xs text-slate-600">Phone: {{ auth()->user()?->userProfile?->phone ?: 'Not available' }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Subjects and Focus</p>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @forelse ($selectedRequest->subjects ?? [] as $subject)
                                    <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-bold uppercase text-slate-600">{{ $subject }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">No subjects listed.</span>
                                @endforelse
                            </div>
                            <div class="mt-4 grid gap-4 md:grid-cols-2 text-xs text-slate-700">
                                <div>
                                    <p class="font-black uppercase text-slate-400">Weak Areas</p>
                                    <p class="mt-1">{{ $selectedRequest->weak_areas ?: 'No weak areas provided.' }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Recent Performance Notes</p>
                                    <p class="mt-1">{{ $selectedRequest->recent_performance_notes ?: 'No performance notes provided.' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4 text-xs text-slate-700">
                            <p class="text-[10px] font-black uppercase text-slate-400">Schedule and Location</p>
                            <div class="mt-3 grid gap-3 md:grid-cols-3">
                                <div>
                                    <p class="font-black uppercase text-slate-400">Mode</p>
                                    <p class="mt-1">{{ ucfirst($selectedRequest->lesson_mode) }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Frequency</p>
                                    <p class="mt-1">{{ $selectedRequest->preferred_frequency ?: 'Flexible' }}</p>
                                </div>
                                <div>
                                    <p class="font-black uppercase text-slate-400">Duration</p>
                                    <p class="mt-1">{{ $selectedRequest->preferred_duration ?: 'Flexible' }}</p>
                                </div>
                            </div>

                            @if (!empty($selectedRequest->preferred_days) && is_array($selectedRequest->preferred_days))
                                <div class="mt-3">
                                    <p class="text-[10px] font-black uppercase text-slate-400">Preferred Days</p>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($selectedRequest->preferred_days as $day)
                                            <span class="rounded-lg bg-cyan-50 px-2 py-1 text-[10px] font-bold uppercase text-cyan-700">{{ is_array($day) ? ($day['day'] ?? 'Selected') : ($day ?: 'Selected') }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <p class="mt-3">Address: {{ $selectedRequest->parent_address ?: 'Not provided' }}</p>
                            <p class="mt-1">City/State: {{ $selectedRequest->city_area ?: 'City not set' }}, {{ $selectedRequest->state ?: 'State not set' }}</p>
                        </div>
                    </section>

                    <aside class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-700">
                            <p class="text-[10px] font-black uppercase text-slate-400">Assignment and Tutor</p>
                            <div class="mt-2 flex items-center gap-3">
                                @if ($tutorImageUrl)
                                    <img src="{{ $tutorImageUrl }}" alt="Tutor image" class="h-12 w-12 rounded-full border border-slate-200 object-cover" />
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 bg-slate-100 text-[10px] font-black text-slate-500">N/A</div>
                                @endif
                                <div>
                                    <p class="font-bold text-slate-800">{{ $tutorProfile?->fullName ?? $tutor?->name ?? 'Pending assignment' }}</p>
                                    <p class="text-xs text-slate-500">Gender: {{ $tutorGender }}</p>
                                </div>
                            </div>
                            <p class="mt-2">Tutor Phone: {{ $tutorPhone }}</p>
                            <p class="mt-1">Assignment Status: {{ $assignmentStatusLabel ? str_replace('_', ' ', $assignmentStatusLabel) : 'Pending' }}</p>
                            <p class="mt-1">Planned Start Date: {{ $latestAssignment?->start_date?->format('d M Y') ?: 'Not set by admin' }}</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4 text-xs text-slate-700">
                            <p class="text-[10px] font-black uppercase text-slate-400">Payment Summary</p>
                            <p class="mt-2">Quote: {{ $selectedRequest->price_quote ? 'NGN ' . number_format((float) $selectedRequest->price_quote, 2) : 'Pending quote' }}</p>
                            <p class="mt-1">Payment Status: {{ ucfirst($selectedRequest->payment_status ?? 'pending') }}</p>
                            <p class="mt-1">Reference: {{ $selectedRequest->payment_reference ?: 'Not generated' }}</p>
                        </div>

                        @if ($canPayFromDetails)
                            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 text-xs text-cyan-900">
                                <p class="font-bold">Match confirmed. You can accept this offer and pay now, or request adjustment.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button wire:click="payNow({{ $selectedRequest->id }})"
                                        class="rounded-xl bg-cyan-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-cyan-700">
                                        Proceed to Payment
                                    </button>
                                    <button wire:click="openAdjustmentModal"
                                        class="rounded-xl bg-white px-3 py-2 text-xs font-bold text-cyan-700 transition hover:bg-cyan-100">
                                        Request Adjustment
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($clientAdjustmentNote || $adminAdjustmentNote)
                            <div class="rounded-2xl border border-slate-200 p-4 text-xs text-slate-700 space-y-2">
                                <p class="text-[10px] font-black uppercase text-slate-400">Adjustment Trail</p>
                                @if ($clientAdjustmentNote)
                                    <p><span class="font-black text-slate-500">Client Note:</span> {{ $clientAdjustmentNote }}</p>
                                @endif
                                @if ($adminAdjustmentNote)
                                    <p><span class="font-black text-slate-500">Admin Update:</span> {{ $adminAdjustmentNote }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="rounded-2xl border border-slate-200 p-4 text-xs text-slate-700">
                            <p class="text-[10px] font-black uppercase text-slate-400">Timeline</p>
                            <p class="mt-2">Request Submitted: {{ $selectedRequest->created_at?->format('d M Y, H:i') ?: 'Not available' }}</p>
                            <p class="mt-1">Last Updated: {{ $selectedRequest->updated_at?->format('d M Y, H:i') ?: 'Not available' }}</p>
                            <p class="mt-1">Planned Start Date: {{ $latestAssignment?->start_date?->format('d M Y') ?: 'Not set by admin' }}</p>
                            <p class="mt-1">Tutor Started: {{ $latestAssignment?->started_at?->format('d M Y, H:i') ?: 'Not started' }}</p>
                            <p class="mt-1">Tutor Completed: {{ $latestAssignment?->completed_at?->format('d M Y, H:i') ?: 'Not completed' }}</p>
                        </div>

                        @if ($pendingReviewAssignment)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                                <p class="font-bold">Tutor marked this intervention complete and awaits your confirmation.</p>
                                <p class="mt-2">Deadline: {{ $pendingReviewAssignment->completed_at ? $pendingReviewAssignment->completed_at->copy()->addDay()->format('d M Y, H:i') : 'Not available' }}</p>
                                <div class="mt-3 flex gap-2">
                                    <button wire:click="approveReview({{ $selectedRequest->id }})"
                                        class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-700">
                                        Approve Completion
                                    </button>
                                    <button wire:click="openReviewModal"
                                        class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
                                        Request Review
                                    </button>
                                </div>
                            </div>
                        @endif
                    </aside>
                </div>
            </div>
        </div>

        @if ($showReviewModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/65" wire:click="closeReviewModal"></div>
                <div class="relative w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-black text-slate-900">Request Intervention Review</h3>
                    <p class="mt-1 text-sm text-slate-500">Share the issue so support can investigate before payout is finalized.</p>
                    <textarea wire:model="clientReviewMessage" rows="5" class="mt-4 w-full rounded-2xl border-slate-200 text-sm"
                        placeholder="Describe what needs review (delivery quality, attendance, coverage, etc.)"></textarea>
                    @error('clientReviewMessage')
                        <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-4 flex justify-end gap-2">
                        <button wire:click="closeReviewModal" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200">
                            Cancel
                        </button>
                        <button wire:click="requestReview({{ $selectedRequest->id }})"
                            class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-rose-700">
                            Submit Review
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if ($showAdjustmentModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/65" wire:click="closeAdjustmentModal"></div>
                <div class="relative w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <h3 class="text-lg font-black text-slate-900">Request Offer Adjustment</h3>
                    <p class="mt-1 text-sm text-slate-500">State the changes you need for tutor fit, schedule, or quote.</p>
                    <textarea wire:model.defer="clientAdjustmentMessage" rows="5" class="mt-4 w-full rounded-2xl border-slate-200 text-sm"
                        placeholder="Describe the adjustment you want before payment."></textarea>
                    @error('clientAdjustmentMessage')
                        <p class="mt-1 text-xs font-bold text-rose-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-4 flex justify-end gap-2">
                        <button wire:click="closeAdjustmentModal"
                            class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200">
                            Cancel
                        </button>
                        <button wire:click="requestAdjustment({{ $selectedRequest->id }})"
                            class="rounded-xl bg-cyan-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-cyan-700">
                            Submit Adjustment
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
