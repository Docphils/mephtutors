<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Booking;
use App\Models\TutorRequest;
use App\Models\OnlineMeetingAttendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Client Insights Dashboard - MephEd')]
class DashboardController extends Component
{
    public function render()
    {
        $user = Auth::user();
        $userProfile = $user->userProfile;
        
        // Tutor Request Metrics
        $tutorRequests = $user->tutorRequests;
        $pendingRequests = $tutorRequests->where('status', 'pending')->count();
        $matchRate = $tutorRequests->count() > 0 
            ? round(($tutorRequests->whereIn('status', ['matched', 'in_progress', 'completed'])->count() / $tutorRequests->count()) * 100) 
            : 0;

        // Lesson & Financial Metrics
        $bookings = Booking::where('client_id', $user->id)->get();
        $activeBookings = $bookings->where('status', 'Active');
        $completedBookings = $bookings->where('status', 'Completed');
        $totalInvestment = $bookings->where('client_payment_status', 'Paid')->sum('amount');
        
        // CRM / Institution Metrics
        $crms = $user->crms;
        $activeInstitutions = $crms->whereIn('status', ['approved', 'deployed'])->count();

        // Recent Activity
        $recentBookings = $bookings->sortByDesc('created_at')->take(5);
        $meetingBase = OnlineMeetingAttendance::with('meeting')
            ->where('user_id', $user->id)
            ->where('role', 'client');

        $upcomingMeetings = (clone $meetingBase)
            ->where('attendance_status', 'scheduled')
            ->whereHas('meeting', fn ($query) => $query->where('starts_at', '>=', now()))
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        return view('livewire.client.dashboard-controller', [
            'user' => $user,
            'userProfile' => $userProfile,
            'stats' => [
                'totalRequests' => $tutorRequests->count(),
                'pendingRequests' => $pendingRequests,
                'matchRate' => $matchRate,
                'activeLessons' => $activeBookings->count(),
                'completedLessons' => $completedBookings->count(),
                'totalInvestment' => $totalInvestment,
                'activeInstitutions' => $activeInstitutions,
                'scheduledMeetings' => (clone $meetingBase)->where('attendance_status', 'scheduled')->count(),
                'attendedMeetings' => (clone $meetingBase)->where('attendance_status', 'attended')->count(),
                'missedMeetings' => (clone $meetingBase)->where('attendance_status', 'missed')->count(),
            ],
            'recentBookings' => $recentBookings,
            'upcomingMeetings' => $upcomingMeetings,
            'incompleteProfile' => !$userProfile || empty($userProfile->phone) || empty($userProfile->address)
        ]);
    }
}
