<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\{Booking, Crm, Payment, TutorRequest, User};
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\{Layout, Title};

#[Layout('layouts.app')]
#[Title('MephEd - Admin Dashboard')]
class AdminDashboardController extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // Using counts directly from the DB for better performance
        $stats = [
            'total_users'        => User::count(),
            'tutor_requests'     => TutorRequest::count(),
            'active_bookings'    => Booking::where('status', 'Active')->count(),
            'completed_bookings' => Booking::where('status', 'Completed')->count(),
            'earned_payments'    => Payment::where('status', 'Earned')->count(),
            'new_crm'            => Crm::where('status', 'Pending')->count(),
        ];

        return view('livewire.admin.admin-dashboard-controller', [
            'user'         => $user,
            'userProfile'  => $user->userProfile,
            'stats'        => $stats
        ]);
    }
}