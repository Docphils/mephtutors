<?php

namespace App\Livewire\Admin;

use App\Models\TutorProfile;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tutor Profile Management - MephEd Admin')]
class TutorprofileManager extends Component
{
    use WithPagination;

    public string $activeTab = 'All';

    public $search = '';

    public ?int $tutorProfileId = null;

    public string $status = '';

    public string $approvalRemark = '';

    public bool $showModal = false;

    public function mount()
    {
        $this->getTutorProfiles();
    }

    // Filter tabs for qualification and discipline
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Show modal to edit status and approvalRemark only
    public function editTutorProfile(int $id): void
    {
        Gate::authorize('Admin');
        $profile = TutorProfile::findOrFail($id);

        $this->tutorProfileId = $profile->id;
        $this->status = $profile->status;
        $this->approvalRemark = $profile->approvalRemark;

        $this->showModal = true;
    }

    // Retrieve tutor profiles based on active tab (qualification or discipline)
    public function getTutorProfiles()
    {
        $query = TutorProfile::query();

        if ($this->search) {
            // Filter only by fullName if search is set
            $query->where('fullName', 'like', '%'.$this->search.'%')
                ->orWhere('address', 'like', '%'.$this->search.'%');
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
