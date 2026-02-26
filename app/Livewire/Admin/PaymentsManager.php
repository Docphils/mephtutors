<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Payment;
use App\Models\User;
use App\Models\Booking;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Tutor Payments Management - MephEd Admin')]
class PaymentsManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $status = null;
    public $selectedPayment = null;
    public $deleteModal = false;
    public $editModal = false;
    public $showModal = false;
    public $createModal = false;
    public $amount, $evidence, $tutor_id, $booking_id;
    public $newEvidence;

    protected $listeners = [
        "confirmDelete" => "confirmDelete",
        "deletePayment" => "deletePayment",
        "createPayment" => "create",
    ];

    public function showPayment($id)
    {
        $this->selectedPayment = Payment::findOrFail($id);
        $this->showModal = true;
    }

    public function render()
    {
        $payments = Payment::when($this->status, function ($query) {
            return $query->where('status', $this->status);
        })->where(function ($query) {
            return $query->where('amount', 'like', '%'.$this->search.'%')
                         ->orWhereHas('tutor', function ($q) {
                             $q->where('name', 'like', '%'.$this->search.'%');
                         })
                         ->orWhereHas('booking', function ($q) {
                             $q->where('location', 'like', '%'.$this->search.'%');
                         });
        })->paginate(10);


        $tutors = User::where('role','tutor')->get();
        $bookings = Booking::all();

        return view('livewire.admin.payments-manager', compact('payments', 'tutors', 'bookings'));
    }

    public function create()
    {
        $this->resetFields();
        $this->createModal = true;
    }

    public function store()
    {
        $this->validate([
            'amount' => 'required|string|max:255',
            'evidence' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'tutor_id' => 'required|exists:users,id',
            'booking_id' => 'required|exists:bookings,id',
            'status' => 'required|in:Pending,Earned,Paid',
        ]);

        $path = $this->evidence ? $this->evidence->store('payments', 'public') : null;

        Payment::create([
            'amount' => $this->amount,
            'evidence' => $path,
            'status' => $this->status,
            'tutor_id' => $this->tutor_id,
            'booking_id' => $this->booking_id,
        ]);

        $this->createModal = false;
        $this->resetFields();
        session()->flash('message', 'Payment created successfully.');
    }

    public function edit($id)
    {
        $this->selectedPayment = Payment::findOrFail($id);
        $this->amount = $this->selectedPayment->amount;
        $this->evidence = $this->selectedPayment->evidence;
        $this->status = $this->selectedPayment->status;
        $this->tutor_id = $this->selectedPayment->tutor_id;
        $this->booking_id = $this->selectedPayment->booking_id;
        $this->editModal = true;
    }

    public function update()
    {
        $this->validate([
            'amount' => 'required|string|max:255',
            'newEvidence' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'tutor_id' => 'required|exists:users,id',
            'booking_id' => 'required|exists:bookings,id',
            'status' => 'required|in:Pending,Earned,Paid',
        ]);

        if ($this->newEvidence) {
            // Delete old file
            if ($this->selectedPayment->evidence) {
                Storage::disk('public')->delete($this->selectedPayment->evidence);
            }
            $this->evidence = $this->newEvidence->store('payments', 'public');
        }

        $this->selectedPayment->update([
            'amount' => $this->amount,
            'evidence' => $this->evidence,
            'status' => $this->status,
            'tutor_id' => $this->tutor_id,
            'booking_id' => $this->booking_id,
        ]);

        $this->editModal = false;
        $this->resetFields();
        session()->flash('message', 'Payment updated successfully.');
    }

    public function openDelete($id)
    {
        $this->selectedPayment = Payment::findOrFail($id);
        $this->deleteModal = true;
    }

    public function deletePayment()
    {
        if ($this->selectedPayment->evidence) {
            Storage::disk('public')->delete($this->selectedPayment->evidence);
        }

        $this->selectedPayment->delete();

        $this->deleteModal = false;
        $this->resetFields();
        session()->flash('message', 'Payment deleted successfully.');
    }

    private function resetFields()
    {
        $this->amount = '';
        $this->evidence = null;
        $this->status = '';
        $this->newEvidence = null;
        $this->tutor_id = '';
        $this->booking_id = '';
        $this->selectedPayment = null;
    }
    public function closeModals()
    {
        $this->editModal = false;
        $this->deleteModal = false;
        $this->showModal = false;
        $this->createModal = false;
        $this->resetFields();
    }
}
