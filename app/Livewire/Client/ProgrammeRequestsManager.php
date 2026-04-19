<?php

namespace App\Livewire\Client;

use App\Models\ProgrammeEnquiry;
use App\Models\Payment;
use App\Services\PaystackService;
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
    public string $clientReviewMessage = '';

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
            session()->flash('error', 'Payment is not available for cancelled or completed requests.');
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
        $assignment = $this->selectedRequest->assignments
            ->where('status', 'pending_client_review')
            ->sortByDesc('id')
            ->first();

        if ($assignment && $assignment->completed_at) {
            $deadline = $assignment->completed_at->addDay();
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
        $this->selectedRequest = null;
        $this->clientReviewMessage = '';
    }

    public function openReviewModal(): void
    {
        $this->showReviewModal = true;
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
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
        }

        session()->flash('success', 'Intervention request cancelled.');
    }

    public function approveReview(int $id): void
    {
        $enquiry = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with('assignments')
            ->findOrFail($id);

        $assignment = $enquiry->assignments
            ->where('status', 'pending_client_review')
            ->sortByDesc('id')
            ->first();

        if (! $assignment || $assignment->status !== 'pending_client_review') {
            session()->flash('error', 'No pending review found.');
            return;
        }

        $assignment->update(['status' => 'completed']);
        $enquiry->update(['status' => 'completed']);
        $this->markAssignmentPaymentEarned($enquiry, $assignment);

        session()->flash('success', 'Session approved. The session is now closed and tutor will be paid.');
        $this->closeDetails();
    }

    public function requestReview(int $id): void
    {
        $enquiry = ProgrammeEnquiry::query()
            ->where('user_id', Auth::id())
            ->with('assignments')
            ->findOrFail($id);

        $assignment = $enquiry->assignments
            ->where('status', 'pending_client_review')
            ->sortByDesc('id')
            ->first();

        if (! $assignment || $assignment->status !== 'pending_client_review') {
            session()->flash('error', 'No pending review found.');
            return;
        }

        $assignment->update([
            'status' => 'client_declined',
            'tutor_notes' => trim($this->clientReviewMessage) ?: null,
            'declined_at' => now(),
        ]);

        $enquiry->update(['status' => 'reviewing']);

        session()->flash('success', 'Review requested. Support will follow up and payment is on hold.');
        $this->clientReviewMessage = '';
        $this->showReviewModal = false;
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
