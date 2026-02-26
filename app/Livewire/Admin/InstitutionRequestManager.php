<?php

namespace App\Livewire\Admin;

use App\Models\Crm;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Mail\UpdatedCodingOrClubEmail;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function mount()
    {
        Gate::authorize('Admin');
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatus() { $this->resetPage(); }

    public function viewRequest($id)
    {
        $this->selectedRequest = Crm::with(['user', 'serviceItem.service'])->findOrFail($id);
        $this->showDetail = true;
    }

    public function openEdit($id)
    {
        $this->selectedRequest = Crm::findOrFail($id);
        $this->newStatus = $this->selectedRequest->status;
        $this->showEditModal = true;
    }

    public function updateStatus()
    {
        $this->validate([
            'newStatus' => 'required'
        ]);

        $this->selectedRequest->update(['status' => $this->newStatus]);

        try {
            Mail::to($this->selectedRequest->user->email)->send(new UpdatedCodingOrClubEmail($this->selectedRequest));
            session()->flash('success', 'Status updated and client notified.');
        } catch (\Exception $e) {
            Log::error('Mail failed: ' . $e->getMessage());
            session()->flash('success', 'Status updated (Notification failed).');
        }

        $this->showEditModal = false;
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
        $this->showDetailModal = false;
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
            'requests' => $query->latest()->paginate($this->perPage)
        ]);
    }
}