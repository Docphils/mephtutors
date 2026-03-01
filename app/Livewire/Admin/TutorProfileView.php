<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\TutorProfile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\TutorProfileApprovalEmail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Tutor Profile Details - MephEd Admin')]
class TutorProfileView extends Component
{
    public TutorProfile $profile;

    public $activeSection = 'view_all';
    public $editor = false;
    public $showModal = false;
    public $tutorProfileId;
    public $status, $approvalRemark;

    public function mount(TutorProfile $profile)
    {
        Gate::authorize('Admin');

        $this->profile = $profile->load('user');
    }

    public function setSection($section)
    {
        $this->activeSection = $section;
    }

    // Show modal to edit status and approvalRemark only
    public function editTutorProfile($id)
    {
        $profile = TutorProfile::findOrFail($id);
        Gate::authorize('Admin');

        $this->tutorProfileId = $profile->id;
        $this->status = $profile->status;
        $this->approvalRemark = $profile->approvalRemark;

        $this->showModal = true;
    }

    // Save changes for approval status and remark
    public function saveTutorProfile()
    {
        Gate::authorize('Admin');

        $this->validate([
            'status' => 'required|in:Approved,Review',
            'approvalRemark' => 'nullable|string|max:255',
        ]);

        $profile = TutorProfile::findOrFail($this->tutorProfileId);
        
        $profile->update([
            'status' => $this->status,
            'approvalRemark' => $this->approvalRemark,
        ]);

        $tutorProfile = $profile->refresh()->load('user');

        try{
            Mail::to($tutorProfile->user->email)->queue(new TutorProfileApprovalEmail($tutorProfile));
            session()->flash('success', 'Tutor profile updated successfully');
        } catch (\Exception $e) {;
            Log::error('Mail sending failed: ' . $e->getMessage());

            session()->flash('success', 'Tutor profile updated successfully (but notification was not sent). Please contact support team');
        }

        $this->showModal = false;
        session()->flash('success', 'Tutor profile updated successfully');
    }

    public function render()
    {
        return view('livewire.admin.tutor-profile-view');
    }
}