<?php

namespace App\Livewire\Admin;

use App\Mail\TutorProfileApprovalEmail;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TutorProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Tutor Profile Management - MephEd Admin')]
class TutorprofileManager extends Component
{
    use WithPagination;

    public $activeTab = 'All'; 
    public $showModal = false;
    public $tutorProfileId;
    public $status, $approvalRemark;
    public $search = '';
    public $showDetailModal = false;
    public $selectedProfile;

    public function mount(){
        $this->getTutorProfiles();
    }

    // Filter tabs for qualification and discipline
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Show the selected profile in the detail modal
    public function showTutorProfile($id)
    {
        $this->selectedProfile = TutorProfile::findOrFail($id);
        $this->showDetailModal = true;
    }

    // Show modal to edit status and approvalRemark only
    public function editTutorProfile($id)
    {
        $profile = TutorProfile::findOrFail($id);
        Gate::authorize('Admin');

        $this->tutorProfileId = $profile->id;
        $this->status = $profile->Approved;
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
        if($this->status == 'Approved'){
            $approvalStatus = true;
        }else{
            $approvalStatus = false;
        }
        $profile->update([
            'Approved' => $approvalStatus,
            'approvalRemark' => $this->approvalRemark,
        ]);

        $tutorProfile = $profile->refresh()->load('user');

        try{
            Mail::to($tutorProfile->user->email)->send(new TutorProfileApprovalEmail($tutorProfile));
            session()->flash('success', 'Tutor profile updated successfully');
        } catch (\Exception $e) {;
            Log::error('Mail sending failed: ' . $e->getMessage());

            session()->flash('success', 'Tutor profile updated successfully (but notification was not sent). Please contact support team');
        }

        $this->showModal = false;
        session()->flash('success', 'Tutor profile updated successfully');
    }

    // Retrieve tutor profiles based on active tab (qualification or discipline)
    public function getTutorProfiles()
    {
        $query = TutorProfile::query();

        if ($this->search) {
            // Filter only by fullName if search is set
            $query->where('fullName', 'like', '%' . $this->search . '%')
                ->orWhere('address', 'like', '%' . $this->search . '%');
        } elseif ($this->activeTab !== 'All') {
            // Filter by qualification or discipline if activeTab is set and not 'All'
           $query->where(function ($q) {
                if ($this->activeTab === 'Approved') {
                    $q->where('status', 'Approved'); // Fetch only approved records
                } elseif ($this->activeTab === 'Pending') {
                    $q->where('status', 'Pending'); // Fetch only pending records
                } else {
                    $q->where('qualification', $this->activeTab)
                    ->orWhere('discipline', $this->activeTab);
                }
            });
        }

        return $query->latest()->paginate(10);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.tutorprofile-manager', [
            'tutorProfiles' => $this->getTutorProfiles(),
        ]);
    }
}
