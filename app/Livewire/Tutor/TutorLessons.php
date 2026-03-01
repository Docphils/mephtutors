<?php

namespace App\Livewire\Tutor;

use App\Mail\LessonCompleteEmail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Manage Lessons - MephEd Tutor')]
class TutorLessons extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $activeTab = 'All'; // Default tab
    public $showModal = false; 
    public $selectedLesson = null;
    public $tutorRemarks, $status;
    public $showCompletedModal = false;

    protected $listeners = ['refreshList' => '$refresh'];

    /**
     * Set the active tab and reset pagination
     */
    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * Show the selected lesson details in the modal
     */
    public function showLesson($id)
    {
        $this->selectedLesson = Booking::with(['client', 'serviceItem', 'tutor'])->findOrFail($id); 
        $this->showModal = true;
    } 

    /**
     * Open the Completion Modal with validation checks
     */
    public function editCompleted($id)
    {
        $this->selectedLesson = Booking::findOrFail($id);
        Gate::authorize('Tutor');

        // Only Active or Declined lessons can be marked as Completed
        if (!in_array($this->selectedLesson->status, ['Active', 'Declined'])) {
            session()->flash('error', 'You can only mark active or declined lessons as completed.');
            return;
        }

        $this->resetFields();
        $this->showCompletedModal = true;
    }

    /**
     * Process the completion submission and notify the client
     */
    public function submitCompleted()
    {
        Gate::authorize('Tutor');

        if (!in_array($this->selectedLesson->status, ['Active', 'Declined'])) {
            session()->flash('error', 'Invalid lesson status for completion.');
            return;
        }

        $this->validate([
            'tutorRemarks' => 'required|string|min:10',
            'status' => 'required|in:Completed',
        ]);

        $this->selectedLesson->update([
            'tutorRemarks' => $this->tutorRemarks,
            'status' => $this->status,
            'completed_at' => now(),
        ]);

        $completedLesson = $this->selectedLesson->refresh()->load('client');

        try {
            Mail::to($completedLesson->client->email)->send(new LessonCompleteEmail($completedLesson));
            session()->flash('success', 'Lesson marked as completed and client notified.');
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
            session()->flash('success', 'Lesson updated, but notification email failed. Please contact support.');
        }

        $this->resetFields();
        $this->showCompletedModal = false;
    }

    /**
     * Clear form fields
     */
    public function resetFields()
    {
        $this->tutorRemarks = '';
        $this->status = '';
    }

    /**
     * Helper to retrieve filtered lessons
     */
    public function getLessons()
    {
        $user = Auth::user();
        Gate::authorize('Tutor');

        $query = Booking::where('tutor_id', $user->id)
            ->with(['client', 'serviceItem', 'tutor']);

        switch ($this->activeTab) {
            case 'Closed Lessons':
                $query->where('status', 'Closed');
                break;
            case 'Completed Lessons':
                $query->where('status', 'Completed');
                break;
            case 'Active Lessons':
                $query->where('status', 'Active');
                break;
            case 'Declined Lessons':
                $query->where('status', 'Declined');
                break;
            case 'Accepted Lessons':
                $query->where('status', 'Accepted');
                break;
            // 'All Lessons' or default case shows everything
        }

        return $query->latest()->paginate(10);
    }

    public function render()
    {
        return view('livewire.tutor.tutor-lessons', [
            'lessons' => $this->getLessons(),
        ]);
    }
}