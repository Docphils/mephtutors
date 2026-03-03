<?php

namespace App\Livewire\Admin;

use App\Models\Crm;
use App\Models\User;
use App\Mail\UpdatedCodingOrClubEmail;
use App\Services\PaystackService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Institution Management - MephEd Admin')]
class InstitutionRequestManager extends Component
{
    use WithPagination;

    public $status = 'all';
    public $search = '';
    public $perPage = 15;

    public $selectedRequest = null;
    public $showDetail = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    // Form fields for status update
    public $newStatus;
    public $quoteAmount;
    public $quoteNotes;
    public $contractTerms;
    public $paymentStatus;

    public $selectedTutors = [];
    public $assignmentNotes;

    public function mount()
    {
        Gate::authorize('Admin');
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatus() { $this->resetPage(); }

    public function viewRequest($id)
    {
        $this->selectedRequest = Crm::with(['user', 'serviceItem.service', 'assignments.assignee'])->findOrFail($id);
        $this->showDetail = true;
    }

    public function openEdit($id)
    {
        $this->resetErrorBag();
        $this->selectedRequest = Crm::with(['user', 'serviceItem.service', 'assignments.assignee'])->findOrFail($id);
        $this->newStatus = $this->selectedRequest->status;
        $this->quoteAmount = $this->selectedRequest->quote_amount;
        $this->quoteNotes = $this->selectedRequest->quote_notes;
        $this->contractTerms = $this->selectedRequest->contract_terms;
        $this->paymentStatus = $this->selectedRequest->payment_status;
        $this->selectedTutors = $this->selectedRequest->assignments
            ->where('role', 'tutor')
            ->pluck('user_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
        $this->assignmentNotes = null;
        $this->showEditModal = true;
    }

    public function updateStatus(PaystackService $paystack)
    {
        $this->validate([
            'newStatus' => 'required|in:new,contacted,proposal_sent,negotiating,approved,rejected,deployed,closed',
            'quoteAmount' => 'nullable|numeric|min:0',
            'quoteNotes' => 'nullable|string',
            'contractTerms' => 'nullable|string',
            'paymentStatus' => 'required|in:pending,part_paid,paid,waived',
        ]);

        $shouldInitializePayment = false;
        $crmForPayment = null;

        DB::transaction(function () use (&$shouldInitializePayment, &$crmForPayment) {
            $crm = Crm::with(['assignments'])->findOrFail($this->selectedRequest->id);
            $amountChanged = (float) $crm->quote_amount !== (float) $this->quoteAmount;

            if ($this->newStatus === 'deployed' && $this->paymentStatus !== 'paid') {
                $this->addError('paymentStatus', 'CRM must be marked as paid before deployment.');
                return;
            }

            $payload = [
                'status' => $this->newStatus,
                'quote_amount' => $this->quoteAmount,
                'quote_notes' => $this->quoteNotes,
                'contract_terms' => $this->contractTerms,
                'payment_status' => $this->paymentStatus,
            ];

            if ((float) $this->quoteAmount > 0 && ($amountChanged || !$crm->payment_reference)) {
                $payload['payment_reference'] = (string) Str::uuid();
            }

            if ($this->newStatus === 'contacted' && !$crm->contacted_at) {
                $payload['contacted_at'] = now();
            }
            if ($this->newStatus === 'proposal_sent' && !$crm->proposal_sent_at) {
                $payload['proposal_sent_at'] = now();
            }
            if ($this->newStatus === 'approved' && !$crm->approved_at) {
                $payload['approved_at'] = now();
            }
            if ($this->paymentStatus === 'paid' && !$crm->paid_at) {
                $payload['paid_at'] = now();
            }
            if ($this->newStatus === 'deployed' && !$crm->deployed_at) {
                $payload['deployed_at'] = now();
            }
            if ($this->newStatus === 'closed' && !$crm->closed_at) {
                $payload['closed_at'] = now();
            }

            $crm->update($payload);

            if ((float) $crm->quote_amount > 0 && in_array($crm->payment_status, ['pending', 'part_paid'], true)) {
                $shouldInitializePayment = $amountChanged || !$crm->payment_link || !$crm->payment_reference;
                $crmForPayment = $crm->fresh(['user']);
            }

            $this->selectedRequest = $crm->fresh(['user', 'serviceItem.service', 'assignments.assignee']);
        });

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        if ($shouldInitializePayment && $crmForPayment) {
            try {
                $paystack->initializeCrmPayment($crmForPayment);
                $this->selectedRequest = $this->selectedRequest->fresh(['user', 'serviceItem.service', 'assignments.assignee']);
            } catch (\Exception $e) {
                Log::error('CRM payment initialization failed: ' . $e->getMessage());
                session()->flash('success', 'Request saved but payment link initialization failed.');
            }
        }

        try {
            Mail::to($this->selectedRequest->user->email)->send(new UpdatedCodingOrClubEmail($this->selectedRequest));
            session()->flash('success', 'Status updated and client notified.');
        } catch (\Exception $e) {
            Log::error('Mail failed: ' . $e->getMessage());
            session()->flash('success', 'Status updated (Notification failed).');
        }

        $this->showEditModal = false;
    }

    public function saveTutorAssignments()
    {
        if (!$this->selectedRequest?->id) {
            session()->flash('error', 'No active request selected for assignment.');
            return;
        }

        $this->validate([
            'selectedTutors.*' => 'exists:users,id',
            'assignmentNotes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () {
                $crm = Crm::with('assignments')->findOrFail($this->selectedRequest->id);
                $selectedIds = collect($this->selectedTutors)->map(fn($id) => (int) $id)->unique()->values();

                if ($selectedIds->isEmpty()) {
                    $crm->assignments()->where('role', 'tutor')->delete();
                } else {
                    $crm->assignments()
                        ->where('role', 'tutor')
                        ->whereNotIn('user_id', $selectedIds)
                        ->delete();

                    foreach ($selectedIds as $tutorId) {
                        $assignment = $crm->assignments()->where('user_id', $tutorId)->first();

                        if (!$assignment) {
                            $crm->assignments()->create([
                                'user_id' => $tutorId,
                                'assigned_by' => auth()->id(),
                                'role' => 'tutor',
                                'notes' => $this->assignmentNotes,
                                'status' => $crm->status === 'deployed' ? 'active' : 'assigned',
                                'started_at' => $crm->status === 'deployed' ? now() : null,
                            ]);
                            continue;
                        }

                        $assignment->update([
                            'assigned_by' => auth()->id(),
                            'role' => 'tutor',
                            'notes' => $this->assignmentNotes,
                            'status' => $crm->status === 'deployed' ? 'active' : $assignment->status,
                            'started_at' => $crm->status === 'deployed' ? ($assignment->started_at ?? now()) : $assignment->started_at,
                        ]);
                    }
                }

                $this->selectedRequest = $crm->fresh(['user', 'serviceItem.service', 'assignments.assignee']);
            });

            session()->flash('success', 'Tutor assignments updated successfully.');
            $this->closeModal();
        } catch (\Throwable $e) {
            Log::error('Failed to save tutor assignments: ' . $e->getMessage(), ['crm_id' => $this->selectedRequest?->id]);
            session()->flash('error', 'Failed to save tutor assignments. Please retry.');
            $this->closeModal();
        }
    }

    public function confirmDelete($id)
    {
        $this->selectedRequest = Crm::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->selectedRequest->delete();
        $this->showDeleteModal = false;
        $this->selectedRequest = null;
        session()->flash('success', 'Request deleted successfully.');
    }

    
    public function closeModal()
    {
        $this->showDetail = false;
        $this->showDeleteModal = false;
        $this->showEditModal = false;
        $this->selectedRequest = null;
    }

    public function render()
    {
        $query = Crm::with(['user', 'serviceItem.service']);

        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(function($q) use ($s) {
                $q->where('institution_name', 'like', $s)
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', $s))
                  ->orWhereHas('serviceItem', fn($si) => $si->where('name', 'like', $s));
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return view('livewire.admin.institution-request-manager', [
            'requests' => $query->latest()->paginate($this->perPage),
            'availableTutors' => User::query()
                ->where('role', 'tutor')
                ->whereHas('tutorProfile', fn($q) => $q->where('status', 'Approved'))
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }
}
