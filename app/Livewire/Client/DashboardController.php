<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Client Dashboard')]
class DashboardController extends Component
{
    public $mainPage = '';
    public function render()
    {
        $userProfile = Auth::user()->userProfile;
        $bookings = Auth::user()->bookings;
        $user = Auth::user();
        $tutorRequests = $user->tutorRequests;
        $ongoingBookings = Booking::where('client_id', $user->id)->where('status', 'Active')->get();
        $completedBookings = Booking::where('client_id', $user->id)->where('status', 'Completed')->get();
        $closedBookings = Booking::where('client_id', $user->id)->where('status', 'Closed')->get();
        $newCRM = $user->crms->where('status', 'Pending');
        $ongoingCRM = $user->crms->where('status', 'Ongoing');
        $closedCRM = $user->crms->where('status', 'Closed');

        return view('livewire.client.dashboard-controller', compact('newCRM', 'ongoingCRM', 'closedCRM','userProfile', 'user', 'tutorRequests', 'ongoingBookings', 'completedBookings', 'closedBookings'));
    }
}
