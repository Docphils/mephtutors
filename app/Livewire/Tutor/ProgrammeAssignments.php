<?php

namespace App\Livewire\Tutor;

use App\Models\ProgrammeEnquiryAssignment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Intervention Assignments')]
class ProgrammeAssignments extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $search = '';
    public ?ProgrammeEnquiryAssignment $selectedAssignment = null;
    public bool $showDetailsModal = false;

    public function accept(int $id): void
    {
        // Intentionally left blank: tutors no longer accept assignments.
    }

    public function decline(int $id): void
    {
        // Tutors no longer decline assignments via this UI.
    }

    public function start(int $id): void
    {
        // Starting is handled automatically when payment activates the assignment.
    }

    public function complete(int $id): void
    {
        $assignment = $this->findTutorAssignment($id);
        abort_unless(in_array($assignment->status, ['active'], true), 403);

        // Mark as completed by tutor but await client approval. Store completed_at for deadline.
        $assignment->update([
            'status' => 'pending_client_review',
            'completed_at' => now(),
        ]);

        $assignment->programmeEnquiry()->update(['status' => 'pending_client_review']);
        $this->refreshSelectedAssignment($assignment->id);

        session()->flash('success', 'Assignment marked complete - awaiting client approval.');
    }

    public function openDetails(int $id): void
    {
        $this->selectedAssignment = ProgrammeEnquiryAssignment::query()
            ->where('tutor_id', Auth::id())
            ->with([
                'programmeEnquiry.programme',
                'programmeEnquiry.user.userProfile',
                'programmeEnquiry.assignments.tutor.tutorProfile',
                'programmeEnquiry.assignments.tutor.userProfile',
            ])
            ->findOrFail($id);

        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->selectedAssignment = null;
    }

    protected function findTutorAssignment(int $id): ProgrammeEnquiryAssignment
    {
        return ProgrammeEnquiryAssignment::query()
            ->where('tutor_id', Auth::id())
            ->with([
                'programmeEnquiry.programme',
                'programmeEnquiry.user.userProfile',
                'programmeEnquiry.assignments.tutor.tutorProfile',
                'programmeEnquiry.assignments.tutor.userProfile',
            ])
            ->findOrFail($id);
    }

    protected function refreshSelectedAssignment(int $id): void
    {
        if (! $this->showDetailsModal || ! $this->selectedAssignment || $this->selectedAssignment->id !== $id) {
            return;
        }

        $this->selectedAssignment = ProgrammeEnquiryAssignment::query()
            ->where('tutor_id', Auth::id())
            ->with([
                'programmeEnquiry.programme',
                'programmeEnquiry.user.userProfile',
                'programmeEnquiry.assignments.tutor.tutorProfile',
                'programmeEnquiry.assignments.tutor.userProfile',
            ])
            ->find($id);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $assignments = ProgrammeEnquiryAssignment::query()
            ->where('tutor_id', Auth::id())
            ->whereHas('programmeEnquiry', fn ($q) => $q->whereIn('status', ['in_progress', 'pending_client_review']))
            ->with([
                'programmeEnquiry.programme',
                'programmeEnquiry.user.userProfile',
                'programmeEnquiry.assignments.tutor.tutorProfile',
                'programmeEnquiry.assignments.tutor.userProfile',
            ])
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->search, function ($query) {
                $query->whereHas('programmeEnquiry', function ($sub) {
                    $sub->where('learner_name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('programme', fn ($p) => $p->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.tutor.programme-assignments', [
            'assignments' => $assignments,
        ]);
    }
}
