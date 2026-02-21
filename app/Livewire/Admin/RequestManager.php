<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TutorRequest;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Tutor Requests | MephEd')] 
class RequestManager extends Component
{
    use WithPagination;

    public $status = 'all';
    public $selectedRequest = null;
    public $showModal = false;
    public $deleteModal = false;
    public $editModal = false;
    public $search = '';
    public $newStatus = '';

    protected $listeners = [
        'closeModal' => 'closeModal',
        'openDeleteModal' => 'openDeleteModal',
        'deleteRequest' => 'deleteRequest'
    ];

    public function mount()
    {
        Gate::authorize('Admin');
    }

    public function showRequest($id)
    {
        $this->selectedRequest = TutorRequest::with(['user', 'serviceItem', 'level', 'examType'])->findOrFail($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->deleteModal = false;
        $this->editModal = false;
        $this->selectedRequest = null;
    }

    public function openDeleteModal($id)
    {
        $this->selectedRequest = TutorRequest::findOrFail($id);
        $this->deleteModal = true;
    }

    public function deleteRequest()
    {
        if ($this->selectedRequest) {
            TutorRequest::findOrFail($this->selectedRequest->id)->delete();
            session()->flash('success', 'Request deleted successfully.');
        }

        $this->closeModal();
        $this->resetPage(); 
    }

    public function openEditModal($id)
    {
        $this->selectedRequest = TutorRequest::findOrFail($id);
        $this->newStatus = $this->selectedRequest->status; 
        $this->editModal = true;
    }

    public function updateRequest()
    {
        $validStatuses = ['pending', 'reviewing', 'matched', 'in_progress', 'completed', 'cancelled'];
        
        $this->validate([
            'newStatus' => 'required|in:' . implode(',', $validStatuses),
        ]);

        $tutorRequest = TutorRequest::findOrFail($this->selectedRequest->id);
        $tutorRequest->update([
            'status' => $this->newStatus,
        ]);

        session()->flash('success', 'Status updated successfully.');
        $this->closeModal();
        $this->resetPage();
    }

    public function render()
    {
        $query = TutorRequest::with(['user', 'serviceItem']);

        // Search Logic
        if ($this->search) {
            $query->where(function($q) {
                $q->where('lesson_address', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($u) {
                      $u->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('serviceItem', function($s) {
                      $s->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Status Filter Logic
        if ($this->status !== 'all' && $this->status !== 'search') {
            $query->where('status', $this->status);
        }

        return view('livewire.admin.request-manager', [
            'requests' => $query->latest()->paginate(10)
        ]);
    }
}