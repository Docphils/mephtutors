<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\{Booking, Crm, Payment, TutorRequest, ProgrammeEnquiry, User, TutorProfile, Enrollee, Contact, Newsletter};
use App\Models\OnlineMeeting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\{Layout, Title};
use Carbon\Carbon;

#[Layout('layouts.app')]
#[Title('MephEd - Admin Dashboard')]
class AdminDashboardController extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // Comprehensive Statistics
        $stats = [
            'total_users'        => User::count(),
            'tutor_requests'     => TutorRequest::count(),
            'pending_requests'   => TutorRequest::where('status', 'Pending')->count(),
            'intervention_requests' => ProgrammeEnquiry::count(),
            'pending_interventions' => ProgrammeEnquiry::whereIn('status', ['new', 'pending', 'reviewing'])->count(),
            'active_interventions' => ProgrammeEnquiry::whereIn('status', ['matched', 'in_progress'])->count(),
            'completed_interventions' => ProgrammeEnquiry::where('status', 'completed')->count(),
            'active_bookings'    => Booking::where('status', 'Active')->count(),
            'completed_bookings' => Booking::where('status', 'Completed')->count(),
            'earned_payments'    => Payment::where('status', 'Earned')->sum('amount'),
            'new_crm'            => Crm::where('status', 'new')->count(),
            'pending_tutors'     => TutorProfile::where('status', 'Pending')->count(),
            'bootcamp_count'     => Enrollee::count(), 
            'unread_messages'    => Contact::where('is_read', false)->count(), 
            'campaigns_sent'     => Newsletter::where('status', 'Sent')->count(), 
            'scheduled_meetings' => OnlineMeeting::where('status', 'scheduled')->count(),
            'completed_meetings' => OnlineMeeting::where('status', 'completed')->count(),
        ];

        // Advanced Chart Data: 7-Day Multi-Metric Growth
        $chartData = [
            'labels'   => [],
            'users'    => [],
            'bookings' => [],
            'revenue'  => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartData['labels'][] = $date->format('D');
            
            // Daily User Growth
            $chartData['users'][] = User::whereDate('created_at', $date->toDateString())->count();
            
            // Daily Booking Activity
            $chartData['bookings'][] = Booking::whereDate('created_at', $date->toDateString())->count();
            
            // Daily Revenue (Converted to thousands for chart scaling)
            $chartData['revenue'][] = Payment::where('status', 'Earned')
                ->whereDate('created_at', $date->toDateString())
                ->sum('amount') / 1000;
        }

        return view('livewire.admin.admin-dashboard-controller', [
            'user'          => $user,
            'stats'         => $stats,
            'chartData'     => $chartData,
            'upcomingMeetings' => OnlineMeeting::with(['client', 'tutor'])
                ->where('starts_at', '>=', now())
                ->latest('starts_at')
                ->take(4)
                ->get(),
        ]);
    }
}
