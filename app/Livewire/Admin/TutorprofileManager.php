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
    public $search = '';

    public function mount(){
        $this->getTutorProfiles();
    }

    // Filter tabs for qualification and discipline
    public function setTab($tab)
    {
        $this->activeTab = $tab;
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
