<?php

namespace App\Livewire\Tutor;

use App\Models\CrmAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Institution Assignments - MephEd Tutor')]
class InstitutionAssignments extends Component
{
    use WithPagination;

    public $status = 'all';
    public $search = '';
    public $showModal = false;
    public $selectedAssignment = null;

    public function mount()
    {
        Gate::authorize('Tutor');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function viewAssignment($id)
    {
        $this->selectedAssignment = CrmAssignment::with([
            'crm.serviceItem.service',
            'crm.user.userProfile',
            'assignedBy',
        ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedAssignment = null;
    }

    public function render()
    {
        $query = CrmAssignment::with(['crm.serviceItem.service', 'crm.user'])
            ->where('user_id', Auth::id());

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $search = '%' . $this->search . '%';
            $query->whereHas('crm', function ($q) use ($search) {
                $q->where('institution_name', 'like', $search)
                    ->orWhere('institution_address', 'like', $search)
                    ->orWhereHas('serviceItem', fn($si) => $si->where('name', 'like', $search));
            });
        }

        return view('livewire.tutor.institution-assignments', [
            'assignments' => $query->latest()->paginate(10),
        ]);
    }
}
