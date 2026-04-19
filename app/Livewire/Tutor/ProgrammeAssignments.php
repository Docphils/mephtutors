<?php

namespace App\Livewire\Tutor;

use App\Models\ProgrammeEnquiryAssignment;
use App\Support\InterventionStatusNotifier;
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
    public bool $showCompletedModal = false;
    public ?int $completionAssignmentId = null;
    public string $completionRemark = '';
    public array $visibleStatuses = ProgrammeEnquiryAssignment::VISIBLE_TO_TUTOR;
    public array $canBeMarkedComplete = ProgrammeEnquiryAssignment::CAN_BE_MARKED_COMPLETE;

    public function complete(int $id): void
    {
        $assignment = $this->findTutorAssignment($id);
        abort_unless(in_array($assignment->status, ProgrammeEnquiryAssignment::canBeMarkedCompleteStatuses(), true), 403);
        $this->completionAssignmentId = $assignment->id;
        $this->completionRemark = '';
        $this->showCompletedModal = true;
    }

    public function submitCompletion(): void
    {
        $this->validate([
            'completionRemark' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        abort_unless($this->completionAssignmentId, 404);
        $assignment = $this->findTutorAssignment($this->completionAssignmentId);
        abort_unless(in_array($assignment->status, ProgrammeEnquiryAssignment::canBeMarkedCompleteStatuses(), true), 403);

        $assignment->update([
            'status' => ProgrammeEnquiryAssignment::STATUS_COMPLETED,
            'tutor_notes' => trim($this->completionRemark),
            'completed_at' => now(),
        ]);

        $assignment->programmeEnquiry()->update(['status' => 'pending_client_review']);
        $assignment->loadMissing([
            'programmeEnquiry.programme',
            'programmeEnquiry.user.userProfile',
            'tutor.tutorProfile',
            'tutor.userProfile',
        ]);

        InterventionStatusNotifier::notifyClient(
            $assignment->programmeEnquiry,
            InterventionStatusNotifier::CLIENT_COMPLETED_REVIEW_REQUIRED,
            [
                'tutor_name' => $assignment->tutor?->tutorProfile?->fullName ?? $assignment->tutor?->name ?? 'Assigned tutor',
                'review_deadline' => optional($assignment->completed_at?->copy()->addDay())->format('M d, Y h:i A'),
            ]
        );
        $this->refreshSelectedAssignment($assignment->id);
        $this->closeCompleteModal();

        session()->flash('success', 'Assignment marked complete - awaiting client approval.');
    }

    public function closeCompleteModal(): void
    {
        $this->showCompletedModal = false;
        $this->completionAssignmentId = null;
        $this->completionRemark = '';
    }

    public function openDetails(int $id): void
    {
        $this->selectedAssignment = ProgrammeEnquiryAssignment::query()
            ->where('tutor_id', Auth::id())
            ->with([
                'payment',
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
                'payment',
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
                'payment',
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
            ->whereIn('status', ProgrammeEnquiryAssignment::visibleToTutorStatuses())
            ->with([
                'payment',
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
