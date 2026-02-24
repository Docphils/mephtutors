<?php

namespace App\Livewire\Client;

use App\Mail\LessonReviewedEmail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('My Lessons - MEPHED')]
class Lessons extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $activeTab = 'Active Lessons';
    public $showModal = false; 
    public $selectedLesson;
    public $clientAcceptanceRemarks, $clientApprovalRemarks, $status, $paymentEvidence;

    public $showAcceptanceModal = false;
    public $showApprovalModal = false;

    public $tabs = [
        ['name' => 'Pending Lessons', 'icon' => 'fa-clock-rotate-left'],
        ['name' => 'In Review', 'icon' => 'fa-magnifying-glass-chart'],
        ['name' => 'Accepted Lessons', 'icon' => 'fa-circle-check'],
        ['name' => 'Active Lessons', 'icon' => 'fa-play-circle'],
        ['name' => 'Completed Lessons', 'icon' => 'fa-flag-checkered'],
        ['name' => 'Closed Lessons', 'icon' => 'fa-box-archive'],
    ];
   
    public function mount()
    {
        Gate::authorize('Client');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function getStatusClasses($status)
    {
        return match($status) {
            'Pending'   => 'bg-amber-100 text-amber-700',
            'Accepted'  => 'bg-blue-100 text-blue-700',
            'Active'    => 'bg-emerald-100 text-emerald-700',
            'Completed' => 'bg-cyan-100 text-cyan-700',
            'Closed'    => 'bg-slate-100 text-slate-700',
            default     => 'bg-slate-100 text-slate-600',
        };
    }

    public function showLesson($id)
    {
        $booking = Booking::with(['tutor', 'serviceItem', 'client'])->find($id);
        if ($booking) {
            $this->selectedLesson = $booking;
            $this->showModal = true;
        }
    } 

    public function editAcceptance($id)
    {
        $this->selectedLesson = Booking::findOrFail($id);
        if ($this->selectedLesson->status !== 'Pending') {
            session()->flash('error', 'Only pending lessons can be updated.');
            return;
        }
        $this->resetFields();
        $this->showAcceptanceModal = true;
    }

    public function editApproval($id)
    {
        $this->selectedLesson = Booking::findOrFail($id);
        if ($this->selectedLesson->status !== 'Completed') {
            session()->flash('error', 'Only completed lessons can be approved.');
            return;
        }
        $this->resetFields();
        $this->showApprovalModal = true;
    }

    public function submitAcceptance(PaystackService $paystack)
    {
        $this->validate([
            'clientAcceptanceRemarks' => 'required|string',
            'status' => 'required|in:Adjust,Accepted',
            'paymentEvidence' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $lessonData = [
            'clientAcceptanceRemarks' => $this->clientAcceptanceRemarks,
            'status' => $this->status
        ];

        if ($this->paymentEvidence) {
            $lessonData['paymentEvidence'] = $this->paymentEvidence
                ->store('payment_evidences', 'public');
        }

        $this->selectedLesson->update($lessonData);

        // 🔔 Notify admin (unchanged)
        try {
            Mail::to('admin@mephed.ng')
                ->send(new LessonReviewedEmail($this->selectedLesson->refresh()));
        } catch (\Exception $e) {
            Log::error('Mail failed: ' . $e->getMessage());
        }

        $this->showAcceptanceModal = false;
        $this->showModal = false;

        /*
        |--------------------------------------------------------------------------
        | 🔥 ONLY TRIGGER PAYMENT IF ACCEPTED
        |--------------------------------------------------------------------------
        */
        if ($this->status === 'Accepted') {

            // Safety check
            if ($this->selectedLesson->client_payment_status === 'Paid') {
                session()->flash('success', 'Lesson already paid.');
                return;
            }

            try {

                $authorizationUrl = $paystack
                    ->initializePayment($this->selectedLesson->refresh());

                return redirect()->away($authorizationUrl);

            } catch (\Exception $e) {
                Log::error('Paystack init failed: ' . $e->getMessage());
                session()->flash('error', 'Unable to initialize payment.');
                return;
            }
        }

        // If Adjust was selected
        session()->flash('success', 'Adjustment request submitted.');
    }

    public function submitApproval()
    {
        $this->validate([
            'clientApprovalRemarks' => 'required|string',
            'status' => 'required|in:Declined,Closed',
        ]);

        $this->selectedLesson->update([
            'clientApprovalRemarks' => $this->clientApprovalRemarks,
            'status' => $this->status
        ]);

        if($this->status == 'Closed'){
            Payment::where('booking_id', $this->selectedLesson->id)->update(['status' => 'Earned']);
        }

        $this->showApprovalModal = false;
        session()->flash('success', 'Lesson status finalized.');
    }

    public function pay($bookingId, PaystackService $paystack)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('client_id', auth()->id())
            ->firstOrFail();

        if ($booking->client_payment_status === 'Paid') {
            session()->flash('error', 'This booking has already been paid.');
            return;
        }

        if ($booking->status !== 'Pending') {
            session()->flash('error', 'Only pending bookings can be paid for.');
            return;
        }

        try {
            $authorizationUrl = $paystack->initializePayment($booking);

            return redirect()->away($authorizationUrl);

        } catch (\Exception $e) {
            session()->flash('error', 'Unable to initialize payment.');
        }
    }

    public function retryPayment($bookingId, PaystackService $paystack)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('client_id', auth()->id())
            ->firstOrFail();

        if ($booking->client_payment_status === 'Paid') {
            session()->flash('success', 'Lesson already paid.');
            return;
        }

        if ($booking->status !== 'Accepted') {
            session()->flash('error', 'Lesson must be accepted before payment.');
            return;
        }

        try {
            $authorizationUrl = $paystack
                ->initializePayment($booking);

            return redirect()->away($authorizationUrl);

        } catch (\Exception $e) {
            Log::error('Retry payment failed: ' . $e->getMessage());
            session()->flash('error', 'Unable to initialize payment.');
        }
    }

    public function resetFields()
    {
        $this->clientAcceptanceRemarks = '';
        $this->clientApprovalRemarks = '';
        $this->status = '';
        $this->paymentEvidence = null;
    }

    public function render()
    {
        $user = Auth::user();
        $statusMap = [
            'Closed Lessons'    => 'Closed',
            'Completed Lessons' => 'Completed',
            'Active Lessons'    => 'Active',
            'Accepted Lessons'  => 'Accepted',
            'In Review'         => 'Adjust',
            'Pending Lessons'   => 'Pending',
        ];

        $query = Booking::where('client_id', $user->id)->with(['tutor', 'serviceItem']);
        
        if (isset($statusMap[$this->activeTab])) {
            $query->where('status', $statusMap[$this->activeTab]);
        }

        return view('livewire.client.lessons', [
            'lessons' => $query->latest()->paginate(9),
        ]);
    }
}