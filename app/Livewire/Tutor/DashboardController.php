<?php

namespace App\Livewire\Tutor;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\CrmAssignment;
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
        $incompleteTutorProfile = !$tutorProfile || empty($tutorProfile->phone) || empty($tutorProfile->address) || empty($tutorProfile->qualification);
        
        
        $pendingPayments = Payment::where('tutor_id', $user->id)->where('status', 'Pending')->count();
        $earnedPayments = Payment::where('tutor_id', $user->id)->where('status', 'Earned')->count();
        $completedPayments = Payment::where('tutor_id', $user->id)->where('status', 'Paid')->count();

        $institutionAssignments = CrmAssignment::with(['crm.serviceItem.service'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $activeInstitutionDeployments = CrmAssignment::where('user_id', $user->id)
            ->whereIn('status', ['assigned', 'active'])
            ->count();
        
        return view('livewire.tutor.dashboard-controller', [
            'pendingPayments' => $pendingPayments,
            'earnedPayments' => $earnedPayments,
            'completedPayments' => $completedPayments,
            'activeInstitutionDeployments' => $activeInstitutionDeployments,
            'institutionAssignments' => $institutionAssignments,
            'tutorProfile' => $tutorProfile,
            'incompleteTutorProfile' => $incompleteTutorProfile,
            'user' => $user
        ]);
    }
}
