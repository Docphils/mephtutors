<?php

namespace App\Livewire\Tutor;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On; 
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('MephEd - Tutor Dashboard')]
class DashboardController extends Component
{
    public $createProfile = false;

    #[On('close')]
    public function close(){
        $this->createProfile = false;
    }

    public function render()
    {
        $user = Auth::user();
        $tutorProfile = $user->tutorProfile;
        
        
        $pendingPayments = Payment::where('tutor_id', $user->id)->where('status', 'Pending')->count();
        $earnedPayments = Payment::where('tutor_id', $user->id)->where('status', 'Earned')->count();
        $completedPayments = Payment::where('tutor_id', $user->id)->where('status', 'Paid')->count();
        
        return view('livewire.tutor.dashboard-controller', [
            'pendingPayments' => $pendingPayments,
            'earnedPayments' => $earnedPayments,
            'completedPayments' => $completedPayments,
            'user' => $user
        ]);
    }
}