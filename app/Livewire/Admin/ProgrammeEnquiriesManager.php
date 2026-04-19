<?php

namespace App\Livewire\Admin;

use App\Models\ProgrammeEnquiry;
use App\Models\ProgrammeEnquiryAssignment;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Intervention Enquiries - MephEd Admin')]
class ProgrammeEnquiriesManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $paymentStatus = '';
    public int $perPage = 15;
    public array $statusInputs = [];
    public array $tutorInputs = [];
    public array $priceInputs = [];
    public array $adminNotes = [];
    public array $startDateInputs = [];
    public ?ProgrammeEnquiry $selectedEnquiry = null;
    public bool $showDetailsModal = false;

    protected array $statusTransitions = [
        'new' => ['pending', 'reviewing', 'cancelled'],
        'pending' => ['reviewing', 'cancelled'],
        'reviewing' => ['pending', 'matched', 'cancelled'],
        'matched' => ['reviewing', 'in_progress', 'cancelled'],
        'pending_client_review' => ['reviewing', 'in_progress', 'completed', 'cancelled'],
        'in_progress' => ['completed', 'cancelled', 'reviewing'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function markAsRead(int $id): void
    {
        ProgrammeEnquiry::query()->findOrFail($id)->update(['status' => 'reviewing']);
    }

    public function openDetails(int $id): void
    {
        $this->selectedEnquiry = ProgrammeEnquiry::query()
            ->with([
                'programme',
                'user.userProfile',
                'activeAssignment.tutor.tutorProfile',
                'activeAssignment.tutor.userProfile',
                'assignments.tutor.tutorProfile',
                'assignments.tutor.userProfile',
                'assignments.assignedBy',
            ])
            ->findOrFail($id);

        $latestAssignment = $this->getCurrentAssignment($this->selectedEnquiry->id)
            ?? $this->selectedEnquiry->assignments->sortByDesc('id')->first();
        $this->statusInputs[$id] = $this->statusInputs[$id] ?? $this->selectedEnquiry->status;
        $this->tutorInputs[$id] = $this->tutorInputs[$id] ?? ($latestAssignment?->tutor_id ?? null);
        $this->priceInputs[$id] = $this->priceInputs[$id] ?? ($this->selectedEnquiry->price_quote ?? null);
        $this->startDateInputs[$id] = $this->startDateInputs[$id] ?? $latestAssignment?->start_date?->format('Y-m-d');
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->selectedEnquiry = null;
    }

    public function reassignTutor(int $enquiryId): void
    {
        $enquiry = ProgrammeEnquiry::query()->findOrFail($enquiryId);
        if (in_array($enquiry->status, ['cancelled', 'completed'], true)) {
            $this->addError("tutorInputs.{$enquiryId}", 'Cannot assign tutor on cancelled or completed requests.');
            return;
        }
        $tutorId = (int) ($this->tutorInputs[$enquiryId] ?? 0);
        $adminNote = trim((string) ($this->adminNotes[$enquiryId] ?? ''));

        if (!$tutorId) {
            $this->addError("tutorInputs.{$enquiryId}", 'Please select a tutor.');
            return;
        }

        $tutor = User::query()
            ->where('id', $tutorId)
            ->where('role', 'tutor')
            ->whereHas('tutorProfile', fn ($query) => $query->where('status', 'Approved'))
            ->first();

        if (!$tutor) {
            $this->addError("tutorInputs.{$enquiryId}", 'Selected tutor is not available for assignment.');
            return;
        }

        $currentAssignments = ProgrammeEnquiryAssignment::query()
            ->where('programme_enquiry_id', $enquiry->id)
            ->whereIn('status', ['assigned', 'accepted', 'active'])
            ->get();

        $hadCurrentAssignment = $currentAssignments->isNotEmpty();
        if ($hadCurrentAssignment) {
            // Keep payout audit trail: mark affected assignment payments as cancelled.
            Payment::query()
                ->whereIn('programme_enquiry_assignment_id', $currentAssignments->pluck('id'))
                ->whereIn('status', ['Pending', 'Earned'])
                ->update(['status' => 'Cancelled']);

            ProgrammeEnquiryAssignment::query()
                ->whereIn('id', $currentAssignments->pluck('id'))
                ->update(['status' => 'cancelled']);
        }

        $newAssignment = ProgrammeEnquiryAssignment::query()->create([
            'programme_enquiry_id' => $enquiry->id,
            'tutor_id' => $tutor->id,
            'assigned_by' => Auth::id(),
            'status' => 'assigned',
            'admin_notes' => $adminNote ?: null,
        ]);

        $this->createOrUpdateAssignmentPayment($enquiry, $newAssignment, 'Pending');

        $this->startDateInputs[$enquiryId] = null;
        $this->refreshSelectedEnquiry();
        session()->flash('success', $hadCurrentAssignment
            ? 'Tutor reassigned. Existing active assignment was cancelled.'
            : 'Tutor assigned. No previous active assignment existed.');
    }

    public function saveQuoteAndDate(int $enquiryId): void
    {
        $enquiry = ProgrammeEnquiry::query()->findOrFail($enquiryId);
        $providedPrice = isset($this->priceInputs[$enquiryId]) ? (float) $this->priceInputs[$enquiryId] : null;

        if ($providedPrice !== null && $providedPrice > 0) {
            $enquiry->update(['price_quote' => $providedPrice]);
        }

        $startDateRaw = trim((string) ($this->startDateInputs[$enquiryId] ?? ''));
        $startDate = null;
        if ($startDateRaw !== '') {
            try {
                $startDate = Carbon::parse($startDateRaw)->startOfDay();
            } catch (\Throwable $e) {
                $this->addError("startDateInputs.{$enquiryId}", 'Please provide a valid start date.');
                return;
            }
        }

        $currentAssignment = $this->getCurrentAssignment($enquiry->id);
        if ($currentAssignment) {
            $currentAssignment->update(['start_date' => $startDate]);
            $this->createOrUpdateAssignmentPayment($enquiry, $currentAssignment, 'Pending');
        } elseif ($startDateRaw !== '') {
            $this->addError("startDateInputs.{$enquiryId}", 'Assign a tutor before setting a start date.');
            return;
        }

        $this->refreshSelectedEnquiry();
        session()->flash('success', 'Quote and start date updated.');
    }

    public function updateStatus(int $enquiryId): void
    {
        $enquiry = ProgrammeEnquiry::query()->with('activeAssignment')->findOrFail($enquiryId);
        $status = (string) ($this->statusInputs[$enquiryId] ?? $enquiry->status);

        $validStatuses = ['new', 'pending', 'reviewing', 'matched', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses, true)) {
            $this->addError("statusInputs.{$enquiryId}", 'Invalid status selected.');
            return;
        }

        if ($status === $enquiry->status) {
            return;
        }

        $allowedNextStates = $this->statusTransitions[$enquiry->status] ?? [];
        if (!in_array($status, $allowedNextStates, true)) {
            $this->addError("statusInputs.{$enquiryId}", "Cannot move from {$enquiry->status} to {$status}.");
            return;
        }

        // Require payment only for in_progress and completed. Admin may set 'matched' before payment.
        if (in_array($status, ['in_progress', 'completed'], true) && ($enquiry->payment_status ?? 'pending') !== 'paid') {
            $this->addError("statusInputs.{$enquiryId}", 'Payment must be completed before this status update.');
            return;
        }

        if ($status === 'matched' && !$enquiry->activeAssignment) {
            $this->addError("statusInputs.{$enquiryId}", 'Assign a tutor before setting status to matched.');
            return;
        }

        if ($status === 'in_progress' && !$enquiry->activeAssignment) {
            $this->addError("statusInputs.{$enquiryId}", 'Assign a tutor before setting in progress.');
            return;
        }

        if ($status === 'completed' && (!$enquiry->activeAssignment || !in_array($enquiry->activeAssignment->status, ['active', 'completed'], true))) {
            $this->addError("statusInputs.{$enquiryId}", 'Only active assignments can be completed.');
            return;
        }

        $enquiry->update(['status' => $status]);

        if ($enquiry->activeAssignment) {
            if ($status === 'in_progress') {
                $enquiry->activeAssignment->update([
                    'status' => 'active',
                    'started_at' => $enquiry->activeAssignment->started_at ?: now(),
                ]);
                $this->createOrUpdateAssignmentPayment($enquiry, $enquiry->activeAssignment, 'Pending');
            } elseif ($status === 'completed') {
                $enquiry->activeAssignment->update([
                    'status' => 'completed',
                    'completed_at' => $enquiry->activeAssignment->completed_at ?: now(),
                ]);
                $this->createOrUpdateAssignmentPayment($enquiry, $enquiry->activeAssignment, 'Earned');
            } elseif ($status === 'cancelled') {
                $enquiry->activeAssignment->update(['status' => 'cancelled']);
                $this->markAssignmentPaymentCancelled($enquiry->activeAssignment);
            } elseif ($status === 'reviewing') {
                $enquiry->activeAssignment->update(['status' => 'cancelled']);
                $this->markAssignmentPaymentCancelled($enquiry->activeAssignment);
            }
        }

        $this->statusInputs[$enquiryId] = $status;
        $this->refreshSelectedEnquiry();
        session()->flash('success', 'Intervention request status updated.');
    }

    public function mount(): void
    {
        Gate::authorize('Admin');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function refreshSelectedEnquiry(): void
    {
        if (! $this->selectedEnquiry) {
            return;
        }

        $this->selectedEnquiry = ProgrammeEnquiry::query()
            ->with([
                'programme',
                'user.userProfile',
                'activeAssignment.tutor.tutorProfile',
                'activeAssignment.tutor.userProfile',
                'assignments.tutor.tutorProfile',
                'assignments.tutor.userProfile',
                'assignments.assignedBy',
            ])
            ->find($this->selectedEnquiry->id);
    }

    protected function getCurrentAssignment(int $enquiryId): ?ProgrammeEnquiryAssignment
    {
        return ProgrammeEnquiryAssignment::query()
            ->where('programme_enquiry_id', $enquiryId)
            ->whereIn('status', ['assigned', 'accepted', 'active'])
            ->latest('id')
            ->first();
    }

    protected function createOrUpdateAssignmentPayment(
        ProgrammeEnquiry $enquiry,
        ProgrammeEnquiryAssignment $assignment,
        string $status = 'Pending'
    ): void {
        $calculatedAmount = round(max((float) ($enquiry->price_quote ?? 0), 0) * 0.7, 2);

        $payment = Payment::query()->firstOrNew([
            'programme_enquiry_assignment_id' => $assignment->id,
        ]);

        $payment->tutor_id = $assignment->tutor_id;
        $payment->booking_id = null;

        if (! $payment->exists || $payment->status === 'Pending') {
            $payment->amount = $calculatedAmount;
        }

        if (! $payment->exists) {
            $payment->status = $status;
        } elseif ($status === 'Earned' && $payment->status === 'Pending') {
            $payment->status = 'Earned';
        } elseif ($status === 'Pending' && $payment->status === 'Cancelled') {
            $payment->status = 'Pending';
        }

        $payment->save();
    }

    protected function markAssignmentPaymentCancelled(ProgrammeEnquiryAssignment $assignment): void
    {
        Payment::query()
            ->where('programme_enquiry_assignment_id', $assignment->id)
            ->whereIn('status', ['Pending', 'Earned'])
            ->update(['status' => 'Cancelled']);
    }

    public function render()
    {
        $enquiries = ProgrammeEnquiry::query()
            ->with([
                'programme',
                'user.userProfile',
                'activeAssignment.tutor.tutorProfile',
                'activeAssignment.tutor.userProfile',
            ])
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->paymentStatus, fn ($query) => $query->where('payment_status', $this->paymentStatus))
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('learner_name', 'like', '%' . $this->search . '%')
                        ->orWhere('parent_name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%')
                                ->orWhereHas('userProfile', fn ($profileQuery) => $profileQuery->where('phone', 'like', '%' . $this->search . '%'));
                        })
                        ->orWhereHas('programme', fn ($p) => $p->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->latest()
            ->paginate($this->perPage);

        $tutors = User::query()
            ->where('role', 'tutor')
            ->whereHas('tutorProfile', fn ($query) => $query->where('status', 'Approved'))
            ->with('tutorProfile:id,user_id,fullName')
            ->orderBy('name')
            ->get(['id', 'name']);

        foreach ($enquiries as $enquiry) {
            $currentAssignment = $this->getCurrentAssignment($enquiry->id);
            $this->statusInputs[$enquiry->id] = $this->statusInputs[$enquiry->id] ?? $enquiry->status;
            $this->tutorInputs[$enquiry->id] = $this->tutorInputs[$enquiry->id] ?? ($currentAssignment?->tutor_id ?? null);
            $this->priceInputs[$enquiry->id] = $this->priceInputs[$enquiry->id] ?? ($enquiry->price_quote ?? null);
            $this->startDateInputs[$enquiry->id] = $this->startDateInputs[$enquiry->id] ?? $currentAssignment?->start_date?->format('Y-m-d');
        }

        return view('livewire.admin.programme-enquiries-manager', [
            'enquiries' => $enquiries,
            'tutors' => $tutors,
        ]);
    }
}
