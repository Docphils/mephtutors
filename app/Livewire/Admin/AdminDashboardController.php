<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\Crm;
use App\Models\EbookOrder;
use App\Models\Enrollee;
use App\Models\IncomeTransaction;
use App\Models\Newsletter;
use App\Models\OnlineMeeting;
use App\Models\Payment;
use App\Models\ProgrammeEnquiry;
use App\Models\TutorProfile;
use App\Models\TutorRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('MephEd - Admin Dashboard')]
class AdminDashboardController extends Component
{
    public function render()
    {
        $user = Auth::user();

        $today = CarbonImmutable::today();
        $weekStart = $today->subDays(6)->startOfDay();
        $monthStart = $today->startOfMonth();

        $userCountsByDay = User::query()
            ->where('created_at', '>=', $weekStart)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');
        $bookingCountsByDay = Booking::query()
            ->where('created_at', '>=', $weekStart)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartData = ['labels' => [], 'dates' => [], 'users' => [], 'bookings' => []];
        for ($offset = 6; $offset >= 0; $offset--) {
            $date = $today->subDays($offset);
            $key = $date->toDateString();
            $chartData['labels'][] = $date->format('D');
            $chartData['dates'][] = $date->format('M j');
            $chartData['users'][] = (int) ($userCountsByDay[$key] ?? 0);
            $chartData['bookings'][] = (int) ($bookingCountsByDay[$key] ?? 0);
        }
        $chart = $this->buildChartLayout($chartData);

        $stats = [
            'total_users' => User::count(),
            'total_clients' => User::where('role', 'client')->count(),
            'total_tutors' => User::where('role', 'tutor')->count(),
            'new_users_today' => User::whereDate('created_at', $today)->count(),
            'new_users_week' => array_sum($chartData['users']),
            'tutor_requests' => TutorRequest::count(),
            'pending_requests' => TutorRequest::where('status', 'Pending')->count(),
            'intervention_requests' => ProgrammeEnquiry::count(),
            'pending_interventions' => ProgrammeEnquiry::whereIn('status', ['new', 'pending', 'reviewing'])->count(),
            'active_interventions' => ProgrammeEnquiry::whereIn('status', ['matched', 'in_progress'])->count(),
            'completed_interventions' => ProgrammeEnquiry::where('status', 'completed')->count(),
            'active_bookings' => Booking::where('status', 'Active')->count(),
            'completed_bookings' => Booking::where('status', 'Completed')->count(),
            'new_bookings_week' => array_sum($chartData['bookings']),
            'tutor_earned' => Payment::where('status', 'Earned')->sum('amount'),
            'tutor_earned_month' => Payment::where('status', 'Earned')->where('created_at', '>=', $monthStart)->sum('amount'),
            'income_total' => IncomeTransaction::query()->where('currency', 'NGN')->sum('amount'),
            'income_month' => IncomeTransaction::query()->where('currency', 'NGN')->whereBetween('received_at', [$monthStart, $today->endOfDay()])->sum('amount'),
            'ebook_orders' => EbookOrder::count(),
            'paid_ebook_orders' => EbookOrder::where('status', 'paid')->count(),
            'pending_payments' => Payment::where('status', 'Pending')->count(),
            'new_crm' => Crm::where('status', 'new')->count(),
            'pending_tutors' => TutorProfile::where('status', 'Pending')->count(),
            'bootcamp_count' => Enrollee::count(),
            'pending_enrollees' => Enrollee::where('status', 'pending')->count(),
            'unread_messages' => Contact::where('is_read', false)->count(),
            'new_messages_today' => Contact::where('is_read', false)->whereDate('created_at', $today)->count(),
            'campaigns_sent' => Newsletter::where('status', 'Sent')->count(),
            'scheduled_meetings' => OnlineMeeting::where('status', 'scheduled')->count(),
            'completed_meetings' => OnlineMeeting::where('status', 'completed')->count(),
        ];
        $pendingRequestCount = $stats['pending_requests'] + $stats['pending_interventions'];
        $attentionCount = $stats['pending_tutors'] + $pendingRequestCount + $stats['unread_messages'];

        $recentUsers = User::query()->latest()->limit(5)->get(['id', 'name', 'role', 'created_at'])
            ->map(static fn (User $registeredUser): array => [
                'name' => $registeredUser->name,
                'role' => $registeredUser->role,
                'initial' => mb_strtoupper(mb_substr($registeredUser->name, 0, 1)),
                'created_label' => $registeredUser->created_at->diffForHumans(),
            ]);
        $recentMessages = Contact::query()->latest()->limit(4)->get(['id', 'name', 'email', 'message', 'is_read', 'created_at'])
            ->map(static fn (Contact $message): array => [
                'name' => $message->name,
                'email' => $message->email,
                'message' => $message->message,
                'is_read' => (bool) $message->is_read,
                'created_label' => $message->created_at->diffForHumans(),
            ]);
        $upcomingMeetings = OnlineMeeting::with(['client', 'tutor'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take(4)
            ->get()
            ->map(static function (OnlineMeeting $meeting): array {
                $client = $meeting->getRelation('client');
                $tutor = $meeting->getRelation('tutor');

                return [
                    'title' => $meeting->title,
                    'month' => $meeting->starts_at->format('M'),
                    'day' => $meeting->starts_at->format('j'),
                    'starts_label' => $meeting->starts_at->format('D, M j · g:i A'),
                    'client_name' => $client instanceof User ? $client->name : 'No client',
                    'tutor_name' => $tutor instanceof User ? $tutor->name : 'No tutor',
                ];
            });
        $recentEbookOrders = EbookOrder::query()->with('ebook')->latest()->limit(5)->get();

        return view('livewire.admin.admin-dashboard-controller', [
            'stats' => $stats,
            'chartData' => $chartData,
            'chart' => $chart,
            'greetingName' => explode(' ', trim((string) $user?->name))[0],
            'attentionCount' => $attentionCount,
            'pendingRequestCount' => $pendingRequestCount,
            'dashboardDate' => $today->format('l, F j, Y'),
            'recentUsers' => $recentUsers,
            'recentMessages' => $recentMessages,
            'upcomingMeetings' => $upcomingMeetings,
            'recentEbookOrders' => $recentEbookOrders,
        ]);
    }

    /**
     * @param  array{labels: list<string>, dates: list<string>, users: list<int>, bookings: list<int>}  $chartData
     * @return array{
     *     width: int,
     *     height: int,
     *     padding: int,
     *     gridLines: list<float>,
     *     userPoints: list<array{x: float, y: float, date: string, value: int}>,
     *     bookingPoints: list<array{x: float, y: float, date: string, value: int}>,
     *     userPolyline: string,
     *     bookingPolyline: string
     * }
     */
    private function buildChartLayout(array $chartData): array
    {
        $width = 720;
        $height = 220;
        $padding = 24;
        $maxValue = max(1, ...$chartData['users'], ...$chartData['bookings']);
        $step = ($width - ($padding * 2)) / max(1, count($chartData['labels']) - 1);
        $userPoints = [];
        $bookingPoints = [];

        foreach ($chartData['labels'] as $index => $label) {
            $x = (float) ($padding + ($index * $step));
            $userY = (float) ($height - $padding - (($chartData['users'][$index] / $maxValue) * ($height - ($padding * 2))));
            $bookingY = (float) ($height - $padding - (($chartData['bookings'][$index] / $maxValue) * ($height - ($padding * 2))));

            $userPoints[] = [
                'x' => $x,
                'y' => $userY,
                'date' => $chartData['dates'][$index],
                'value' => $chartData['users'][$index],
            ];
            $bookingPoints[] = [
                'x' => $x,
                'y' => $bookingY,
                'date' => $chartData['dates'][$index],
                'value' => $chartData['bookings'][$index],
            ];
        }

        return [
            'width' => $width,
            'height' => $height,
            'padding' => $padding,
            'gridLines' => [
                (float) $padding,
                (float) ($padding + (($height - ($padding * 2)) / 3)),
                (float) ($padding + ((($height - ($padding * 2)) / 3) * 2)),
                (float) ($height - $padding),
            ],
            'userPoints' => $userPoints,
            'bookingPoints' => $bookingPoints,
            'userPolyline' => implode(' ', array_map(
                static fn (array $point): string => round($point['x'], 2).','.round($point['y'], 2),
                $userPoints,
            )),
            'bookingPolyline' => implode(' ', array_map(
                static fn (array $point): string => round($point['x'], 2).','.round($point['y'], 2),
                $bookingPoints,
            )),
        ];
    }
}
