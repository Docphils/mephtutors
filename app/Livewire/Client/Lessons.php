<?php

namespace App\Livewire\Client;

use App\Mail\DeclinedLessonEmail;
use App\Mail\EarnedPaymentEmail;
use App\Mail\LessonReviewedEmail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Booking;
use App\Models\Payment;
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

    public $activeTab = '';
    public $showModal = false; 
    public $selectedLesson;
    public $clientAcceptanceRemarks, $clientApprovalRemarks, $status, $paymentEvidence;

    public $showAcceptanceModal = false;
    public $showApprovalModal = false;
   
    public function mount(){
        Gate::authorize('Client');
        $this->activeTab = 'Active Lessons';
    }
    // Set the active tab
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Show the selected lesson in the modal
    public function showLesson($id)
    {
        $this->selectedLesson = Booking::with('tutor')->find($id); 
        $this->showModal = true;
    } 

    // Open Acceptance Modal
    public function editAcceptance($id)
    {
        $this->selectedLesson = Booking::findOrFail($id);
        Gate::authorize('Client');

        // Ensure the booking status is 'Pending' before showing the acceptance modal
        if ($this->selectedLesson->status !== 'Pending') {
            session()->flash('error', 'You can only edit acceptance remarks for pending lessons.');
            return;
        }

        $this->resetFields();
        $this->showAcceptanceModal = true;
    }

    // Open Approval Modal
    public function editApproval($id)
    {
        $this->selectedLesson = Booking::findOrFail($id);
        

        // Ensure the booking status is 'Completed' before showing the approval modal
        if ($this->selectedLesson->status !== 'Completed') {
            session()->flash('error', 'You can only edit approval remarks for completed lessons.');
            return;
        }

        $this->resetFields();
        $this->showApprovalModal = true;
    }

    // Client Acceptance Remarks
    public function submitAcceptance()
    {
        Gate::authorize('Client');

        // Ensure the status of the booking is still 'Pending' before updating
        if ($this->selectedLesson->status !== 'Pending') {
            session()->flash('error', 'You cannot submit acceptance remarks for lessons that are not pending.');
            return;
        }
        
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
            $filePath = $this->paymentEvidence->store('payment_evidences', 'public');
            $lessonData['paymentEvidence'] = $filePath;
        }

        $this->selectedLesson->update($lessonData);
        $reviewedLesson = $this->selectedLesson->refresh()->load('client.userProfile');

        try {
            Mail::to('admin@mephed.ng')->send(new LessonReviewedEmail($reviewedLesson));
            session()->flash('success', 'Status updated successfully');
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());

            session()->flash('success', 'Status updated successfully (but email was not sent).');
        }
        $this->resetFields();
        $this->showAcceptanceModal = false;
        
       
    }

    // Client Approval Remarks
    public function submitApproval()
    {

        // Ensure the status of the booking is still 'Completed' before updating
        if ($this->selectedLesson->status !== 'Completed') {
            session()->flash('error', 'You cannot submit approval remarks for lessons that are not completed.');
            return;
        }

        $this->validate([
            'clientApprovalRemarks' => 'required|string',
            'status' => 'required|in:Declined,Closed',
        ]);

        $this->selectedLesson->update([
            'clientApprovalRemarks' => $this->clientApprovalRemarks,
            'status' => $this->status
        ]);

       
        $declinedLesson = $this->selectedLesson->refresh()->load('client.userProfile');

        if($declinedLesson->status == 'Closed'){
            $payment = Payment::where('booking_id', $this->selectedLesson->id);
            $payment->update([
                'status' => 'Earned'
            ]);
        }

        if($declinedLesson->status == 'Declined'){
            try {
                Mail::to($declinedLesson->tutor->email)->cc('admin@mephed.ng')->send(new DeclinedLessonEmail($declinedLesson));
                session()->flash('success', 'Status updated successfully');
            } catch (\Exception $e) {
                Log::error('Mail sending failed: ' . $e->getMessage());
    
                session()->flash('success', 'Status updated successfully (email not sent). Please contact support');
            }
        }

        if($declinedLesson->status == 'Closed'){
            try {
                Mail::to('admin@mephed.ng')->send(new EarnedPaymentEmail($declinedLesson));
                session()->flash('success', 'Status updated successfully');
            } catch (\Exception $e) {
                Log::error('Mail sending failed: ' . $e->getMessage());
    
                session()->flash('success', 'Status updated successfully (email not sent). Please contact support');
            }
        }
      

        $this->resetFields();
        $this->showApprovalModal = false;

        session()->flash('success', 'Remarks added successfully');
    }

    // Reset fields
    public function resetFields()
    {
        $this->clientAcceptanceRemarks = '';
        $this->clientApprovalRemarks = '';
        $this->status = '';
        $this->paymentEvidence = null;
    }

    
    public function getLessons()
    {
        $user = Auth::user();

        // Retrieve bookings for the authenticated user based on the active tab
        switch ($this->activeTab) {
            case 'Closed Lessons':
                return Booking::where('client_id', $user->id)->where('status', 'Closed')->with('tutor')->paginate(10);
            case 'Completed Lessons':
                return Booking::where('client_id', $user->id)->where('status', 'Completed')->with('tutor')->paginate(10);
            case 'Active Lessons':
                return Booking::where('client_id', $user->id)->where('status', 'Active')->with('tutor')->paginate(10);
            case 'Accepted Lessons':
                return Booking::where('client_id', $user->id)->where('status', 'Accepted')->with('tutor')->paginate(10);
            case 'Pending Lessons':
                return Booking::where('client_id', $user->id)->where('status', 'Pending')->with('tutor')->paginate(10);
            default:
                return Booking::where('client_id', $user->id)->paginate(10); 
        }
    }

    public function render()
    {
        return view('livewire.client.lessons', [
            'lessons' => $this->getLessons(),
        ]);
    }}
