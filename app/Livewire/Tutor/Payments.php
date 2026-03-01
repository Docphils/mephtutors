<?php

namespace App\Livewire\Tutor;

use Livewire\Component;
use Illuminate\Support\Facades\Gate;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Manage Payments - MephEd Tutor')]
class Payments extends Component
{
    use WithPagination;

    public $activeTab = 'All Payments';
    public $showModal = false; 
    public $selectedPayment = null;

    // Dispute Form Fields
    public $showDisputeModal = false;
    public $disputeReason = '';

    protected $listeners = ['refreshList' => '$refresh'];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function showPayment($id)
    {
        $this->selectedPayment = Payment::with(['booking.client', 'booking.serviceItem'])->findOrFail($id); 
        $this->showModal = true;
    }

    public function openDispute($id)
    {
        $this->selectedPayment = Payment::findOrFail($id);
        $this->disputeReason = '';
        $this->showDisputeModal = true;
    }

    public function submitDispute()
    {
        $this->validate([
            'disputeReason' => 'required|string|min:10|max:500',
        ]);

        // The dispute field is cast as an array in the Payment model
        $disputeData = [
            'reason' => $this->disputeReason,
            'status' => 'Pending', // Pending, Resolved, Rejected
            'submitted_at' => now()->toDateTimeString(),
            'admin_response' => null,
            'resolved_at' => null
        ];

        $this->selectedPayment->update([
            'dispute' => $disputeData
        ]);

        session()->flash('success', 'Dispute submitted successfully. Admin will review your request.');
        $this->showDisputeModal = false;
        $this->reset(['disputeReason', 'selectedPayment']);
    }

    public function getPayments()
    {
        $user = Auth::user();
        Gate::authorize('Tutor');

        $query = Payment::where('tutor_id', $user->id)
            ->with(['booking.client', 'booking.serviceItem']);

        switch ($this->activeTab) {
            case 'Pending Payments':
                $query->where('status', 'Pending');
                break;
            case 'Earned Payments':
                $query->where('status', 'Earned');
                break;
            case 'Completed Payments':
                $query->where('status', 'Paid');
                break;
            case 'Disputed':
                $query->whereNotNull('dispute');
                break;
        }

        return $query->latest()->paginate(10);
    }

    public function render()
    {
        return view('livewire.tutor.payments', [
            'payments' => $this->getPayments(),
        ]);
    }
}