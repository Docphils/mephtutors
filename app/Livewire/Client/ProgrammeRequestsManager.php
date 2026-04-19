<?php

namespace App\Livewire\Client;

use App\Models\ProgrammeEnquiry;
use App\Models\ProgrammeEnquiryAssignment;
use App\Models\Payment;
use App\Services\PaystackService;
use App\Support\InterventionStatusNotifier;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('My Intervention Requests')]
class ProgrammeRequestsManager extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $paymentFilter = '';
    public string $search = '';
    public ?ProgrammeEnquiry $selectedRequest = null;
    public bool $showDetails = false;
    public bool $showReviewModal = false;
    public bool $showAdjustmentModal = false;
    public string $clientReviewMessage = '';
    public string $clientAdjustmentMessage = '';

    public function payNow(int $id, PaystackService $paystack)
    {
        $enquiry = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with('user')
            ->findOrFail($id);

        if ($enquiry->payment_status === 'paid') {
            session()->flash('success', 'Payment has already been completed for this request.');
            return null;
        }

        if (in_array($enquiry->status, ['cancelled', 'completed'], true)) {
            session()->flash('error', 'Payment is not available for cancelled or closed requests.');
            return null;
        }

        if ($enquiry->status !== 'matched') {
            session()->flash('error', 'Payment is available only after admin matching and quote confirmation.');
            return null;
        }

        if (! $enquiry->activeAssignment) {
            session()->flash('error', 'No active tutor assignment is linked to this request yet.');
            return null;
        }

        if (! $enquiry->price_quote || (float) $enquiry->price_quote <= 0) {
            session()->flash('error', 'Admin quote is pending. Please request an adjustment if needed.');
            return null;
        }

        $redirectUrl = $paystack->initializeProgrammeEnquiryPayment($enquiry);
        return redirect()->away($redirectUrl);
    }

    public function openDetails(int $id): void
    {
        $this->selectedRequest = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with([
                'programme',
                'activeAssignment.tutor.tutorProfile',
                'activeAssignment.tutor.userProfile',
                'assignments.tutor.tutorProfile',
                'assignments.tutor.userProfile',
            ])
            ->findOrFail($id);

        // Auto-approve if pending_client_review and completed_at older than 24 hours.
        $assignment = $this->getPendingReviewAssignment($this->selectedRequest);

        if ($assignment && $assignment->completed_at) {
            $deadline = $assignment->completed_at->copy()->addDay();
            if (now()->greaterThan($deadline)) {
                $this->approveReview($this->selectedRequest->id);
                $this->selectedRequest = ProgrammeEnquiry::query()
                    ->where('user_id', Auth::id())
                    ->with([
                        'programme',
                        'activeAssignment.tutor.tutorProfile',
                        'activeAssignment.tutor.userProfile',
                        'assignments.tutor.tutorProfile',
                        'assignments.tutor.userProfile',
                    ])
                    ->findOrFail($id);
            }
        }

        $this->showDetails = true;
    }

    public function closeDetails(): void
    {
        $this->showDetails = false;
        $this->showReviewModal = false;
        $this->showAdjustmentModal = false;
        $this->selectedRequest = null;
        $this->clientReviewMessage = '';
        $this->clientAdjustmentMessage = '';
    }

    public function openReviewModal(): void
    {
        $this->showReviewModal = true;
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
    }

    public function openAdjustmentModal(): void
    {
        $this->showAdjustmentModal = true;
    }

    public function closeAdjustmentModal(): void
    {
        $this->showAdjustmentModal = false;
    }

    public function cancelRequest(int $id): void
    {
        $enquiry = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with('activeAssignment')
            ->findOrFail($id);

        if (!in_array($enquiry->status, ['new', 'pending', 'reviewing'], true)) {
            session()->flash('error', 'Only new, pending, or reviewing requests can be cancelled.');
            return;
        }

        if (($enquiry->payment_status ?? 'pending') === 'paid') {
            session()->flash('error', 'Paid requests cannot be cancelled from dashboard. Contact support for case review.');
            return;
        }

        $enquiry->update(['status' => 'cancelled']);
        if ($enquiry->activeAssignment) {
            $enquiry->activeAssignment->update(['status' => 'cancelled']);
            Payment::query()
                ->where('programme_enquiry_assignment_id', $enquiry->activeAssignment->id)
                ->whereIn('status', ['Pending', 'Earned'])
                ->update(['status' => 'Cancelled']);

            $enquiry->loadMissing(['programme', 'user.userProfile', 'activeAssignment.tutor.tutorProfile']);
            InterventionStatusNotifier::notifyTutor(
                $enquiry,
                $enquiry->activeAssignment->tutor,
                InterventionStatusNotifier::TUTOR_REASSIGNED_OR_CANCELLED,
                [
                    'note' => 'Client cancelled this intervention request.',
                    'payment_status' => 'cancelled',
                ]
            );
        }

        $enquiry->loadMissing(['programme', 'user.userProfile']);
        InterventionStatusNotifier::notifyAdmins(
            $enquiry,
            InterventionStatusNotifier::ADMIN_CANCELLATION_OR_ADJUSTMENT_REQUESTED,
            ['note' => 'Client cancelled the intervention request from dashboard.']
        );

        session()->flash('success', 'Intervention request cancelled.');
    }

    public function approveReview(int $id): void
    {
        $enquiry = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with('assignments')
            ->findOrFail($id);

        $assignment = $enquiry->assignments
            ->sortByDesc('id')
            ->first(function ($item) use ($enquiry) {
                return $enquiry->status === 'pending_client_review'
                    && in_array($item->status, ['completed', 'pending_client_review'], true);
            });

        if (! $assignment) {
            session()->flash('error', 'No pending review found.');
            return;
        }

        $assignment->update(['status' => 'completed']);
        $enquiry->update(['status' => 'completed']);
        $this->markAssignmentPaymentEarned($enquiry, $assignment);
        $enquiry->loadMissing(['programme', 'user.userProfile']);
        $assignment->loadMissing(['tutor.tutorProfile', 'tutor.userProfile']);

        InterventionStatusNotifier::notifyClient($enquiry, InterventionStatusNotifier::CLIENT_CLOSED);
        InterventionStatusNotifier::notifyTutor(
            $enquiry,
            $assignment->tutor,
            InterventionStatusNotifier::TUTOR_CLOSED_EARNED,
            ['payment_status' => 'earned']
        );
        InterventionStatusNotifier::notifyAdmins(
            $enquiry,
            InterventionStatusNotifier::ADMIN_CLOSED_APPROVED
        );

        session()->flash('success', 'Session approved. The session is now closed and tutor will be paid.');
        $this->closeDetails();
    }

    public function requestReview(int $id): void
    {
        $this->validate([
            'clientReviewMessage' => ['required', 'string', 'min:8', 'max:3000'],
        ]);

        $enquiry = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with('assignments')
            ->findOrFail($id);

        $assignment = $enquiry->assignments
            ->sortByDesc('id')
            ->first(function ($item) use ($enquiry) {
                return $enquiry->status === 'pending_client_review'
                    && in_array($item->status, ['completed', 'pending_client_review'], true);
            });

        if (! $assignment) {
            session()->flash('error', 'No pending review found.');
            return;
        }

        $declineNote = trim($this->clientReviewMessage) ?: 'Client requested additional review.';
        $meta = (array) ($enquiry->meta ?? []);
        $meta['client_completion_decline_note'] = $declineNote;
        $meta['client_completion_declined_at'] = now()->toIso8601String();

        $assignment->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);

        $enquiry->update([
            'status' => 'reviewing',
            'meta' => $meta,
        ]);
        $enquiry->loadMissing(['programme', 'user.userProfile']);
        $assignment->loadMissing(['tutor.tutorProfile', 'tutor.userProfile']);

        InterventionStatusNotifier::notifyTutor(
            $enquiry,
            $assignment->tutor,
            InterventionStatusNotifier::TUTOR_DECLINED,
            ['note' => $declineNote]
        );
        InterventionStatusNotifier::notifyAdmins(
            $enquiry,
            InterventionStatusNotifier::ADMIN_DECLINED_APPROVAL,
            ['note' => $declineNote]
        );

        session()->flash('success', 'Review requested. Support will follow up and payment is on hold.');
        $this->clientReviewMessage = '';
        $this->showReviewModal = false;
        $this->closeDetails();
    }

    public function requestAdjustment(int $id): void
    {
        $this->validate([
            'clientAdjustmentMessage' => ['required', 'string', 'min:8', 'max:3000'],
        ]);

        $enquiry = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with('activeAssignment')
            ->findOrFail($id);

        if ($enquiry->status !== 'matched' || ($enquiry->payment_status ?? 'pending') === 'paid') {
            session()->flash('error', 'Adjustment can only be requested on matched requests before payment.');
            return;
        }

        $adjustmentNote = trim($this->clientAdjustmentMessage);
        $meta = (array) ($enquiry->meta ?? []);
        $meta['client_adjustment_note'] = $adjustmentNote;
        $meta['client_adjustment_requested_at'] = now()->toIso8601String();

        $enquiry->update([
            'status' => 'reviewing',
            'meta' => $meta,
        ]);

        $enquiry->loadMissing(['programme', 'user.userProfile', 'activeAssignment.tutor.tutorProfile']);
        InterventionStatusNotifier::notifyAdmins(
            $enquiry,
            InterventionStatusNotifier::ADMIN_CANCELLATION_OR_ADJUSTMENT_REQUESTED,
            ['note' => $adjustmentNote]
        );

        session()->flash('success', 'Adjustment request sent. Admin will review and return an updated offer.');
        $this->clientAdjustmentMessage = '';
        $this->showAdjustmentModal = false;
        $this->closeDetails();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentFilter(): void
    {
        $this->resetPage();
    }

    protected function markAssignmentPaymentEarned(ProgrammeEnquiry $enquiry, $assignment): void
    {
        $calculatedAmount = round(max((float) ($enquiry->price_quote ?? 0), 0) * 0.7, 2);

        $payment = Payment::query()->firstOrNew([
            'programme_enquiry_assignment_id' => $assignment->id,
        ]);

        $payment->tutor_id = $assignment->tutor_id;
        $payment->booking_id = null;

        if (! $payment->exists || $payment->status === 'Pending') {
            $payment->amount = $calculatedAmount;
        }

        if ($payment->status !== 'Paid') {
            $payment->status = 'Earned';
        }

        $payment->save();
    }

    protected function getPendingReviewAssignment(ProgrammeEnquiry $enquiry): ?ProgrammeEnquiryAssignment
    {
        if ($enquiry->status !== 'pending_client_review') {
            return null;
        }

        return $enquiry->assignments
            ->sortByDesc('id')
            ->first(function (ProgrammeEnquiryAssignment $assignment) {
                return in_array($assignment->status, ['completed', 'pending_client_review'], true)
                    && $assignment->completed_at;
            });
    }

    public function render()
    {
        $requests = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with([
                'programme',
                'activeAssignment.tutor.tutorProfile',
                'activeAssignment.tutor.userProfile',
                'assignments.tutor.tutorProfile',
                'assignments.tutor.userProfile',
            ])
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->paymentFilter, fn ($query) => $query->where('payment_status', $this->paymentFilter))
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('learner_name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('programme', fn ($p) => $p->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.client.programme-requests-manager', [
            'requests' => $requests,
        ]);
    }
}
