<?php

namespace App\Livewire\Admin;

use App\Mail\OnlineMeetingScheduledEmail;
use App\Models\Booking;
use App\Models\OnlineMeeting;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Online Sessions - MephEd Admin')]
class OnlineMeetingManager extends Component
{
    use WithPagination;

    protected $listeners = [
        'create-online-meeting' => 'openCreate',
    ];

    public string $search = '';
    public string $status = 'all';
    public int $perPage = 12;

    public bool $showForm = false;
    public bool $showDetails = false;
    public ?OnlineMeeting $selectedMeeting = null;
    public ?int $editingId = null;

    public $booking_id = null;
    public $service_item_id = null;
    public $client_id = null;
    public $tutor_id = null;
    public $title = '';
    public $description = '';
    public $jitsi_domain = '';
    public $jitsi_room = '';
    public $jitsi_password = '';
    public $starts_at = '';
    public $ends_at = '';
    public $meeting_status = 'scheduled';
    public $recording_enabled = true;
    public string $tutor_privilege = 'participant';

    public function mount(): void
    {
        Gate::authorize('Admin');
        $this->jitsi_domain = config('services.jitsi.domain', 'meet.jit.si');
        $this->recording_enabled = (bool) config('services.jitsi.recording_enabled', true);
        $this->markAutoMissed();
    }

    public function rules(): array
    {
        $meetingId = $this->editingId ?: 'NULL';

        return [
            'booking_id' => 'nullable|exists:bookings,id',
            'client_id' => 'nullable|exists:users,id',
            'tutor_id' => 'nullable|exists:users,id',
            'title' => 'required|string|min:4|max:180',
            'description' => 'nullable|string|max:5000',
            'jitsi_domain' => 'required|string|max:120',
            'jitsi_room' => 'nullable|string|max:180|unique:online_meetings,jitsi_room,' . $meetingId,
            'jitsi_password' => 'nullable|string|max:120',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'meeting_status' => 'required|in:scheduled,live,completed,cancelled',
            'recording_enabled' => 'boolean',
            'tutor_privilege' => 'required|in:participant,moderator',
        ];
    }

    public function updatedBookingId($value): void
    {
        if (!$value) {
            return;
        }

        $booking = Booking::with(['client', 'tutor', 'serviceItem'])->find($value);
        if (!$booking) {
            return;
        }
        $sessionCount = OnlineMeeting::where('booking_id', $booking->id)->count() + 1;

        $this->service_item_id = $booking->service_item_id;
        $this->client_id = $booking->client_id;
        $this->tutor_id = $booking->tutor_id;
        $this->title = 'MephEd Online - L' . $booking->id . '-S' . $sessionCount;
        if (!$this->editingId) {
            $this->jitsi_room = $this->generateRoomId();
        }
        if (blank($this->description)) {
            $this->description = 'Session for ' . ($booking->serviceItem->name ?? 'booked lesson');
        }
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editMeeting(int $id): void
    {
        $meeting = OnlineMeeting::findOrFail($id);
        $this->editingId = $meeting->id;
        $this->booking_id = $meeting->booking_id;
        $this->service_item_id = $meeting->service_item_id;
        $this->client_id = $meeting->client_id;
        $this->tutor_id = $meeting->tutor_id;
        $this->title = $meeting->title;
        $this->description = $meeting->description ?? '';
        $this->jitsi_domain = $meeting->jitsi_domain;
        $this->jitsi_room = $meeting->jitsi_room;
        $this->jitsi_password = $meeting->jitsi_password ?? '';
        $this->starts_at = optional($meeting->starts_at)->format('Y-m-d\TH:i');
        $this->ends_at = optional($meeting->ends_at)->format('Y-m-d\TH:i');
        $this->meeting_status = $meeting->status;
        $this->recording_enabled = (bool) $meeting->recording_enabled;
        $this->tutor_privilege = data_get($meeting->jitsi_options, 'privileges.tutor', 'participant');
        $this->showForm = true;
    }

    public function saveMeeting(): void
    {
        $this->validate();

        if (!$this->editingId) {
            $this->jitsi_room = $this->generateRoomId();
        } elseif (!$this->jitsi_room) {
            $this->jitsi_room = $this->generateRoomId();
        }

        $payload = [
            'booking_id' => $this->booking_id,
            'service_item_id' => $this->service_item_id,
            'client_id' => $this->client_id,
            'tutor_id' => $this->tutor_id,
            'title' => $this->title,
            'description' => $this->description,
            'jitsi_domain' => $this->jitsi_domain,
            'jitsi_room' => $this->jitsi_room,
            'jitsi_password' => $this->jitsi_password ?: null,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at ?: null,
            'status' => $this->meeting_status,
            'recording_enabled' => (bool) $this->recording_enabled,
        ];

        if ($this->editingId) {
            $meeting = OnlineMeeting::findOrFail($this->editingId);
            $meeting->update($payload);
        } else {
            $payload['scheduled_by'] = auth()->id();
            $meeting = OnlineMeeting::create($payload);
        }

        $meeting->update([
            'jitsi_options' => [
                'configOverwrite' => [
                    'subject' => $this->title,
                    'enableWelcomePage' => false,
                ],
                'interfaceConfigOverwrite' => [
                    'DEFAULT_REMOTE_DISPLAY_NAME' => 'MephEd Learner',
                ],
                'privileges' => [
                    'admin' => 'moderator',
                    'tutor' => $this->tutor_privilege,
                    'client' => 'participant',
                ],
            ],
        ]);

        $this->syncAttendanceRecords($meeting);
        $this->dispatchMeetingMails($meeting);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Online session saved and notifications sent.');
    }

    public function openDetails(int $id): void
    {
        $this->selectedMeeting = OnlineMeeting::with(['client', 'tutor', 'booking', 'attendances.user'])->findOrFail($id);
        $this->showDetails = true;
    }

    public function markAttendance(int $attendanceId, string $status): void
    {
        abort_unless(in_array($status, ['scheduled', 'attended', 'missed'], true), 422);
        $attendance = \App\Models\OnlineMeetingAttendance::findOrFail($attendanceId);
        $attendance->update([
            'attendance_status' => $status,
            'joined_at' => $status === 'attended' ? ($attendance->joined_at ?: now()) : $attendance->joined_at,
        ]);
        if ($this->selectedMeeting) {
            $this->selectedMeeting = $this->selectedMeeting->fresh(['client', 'tutor', 'booking', 'attendances.user']);
        }
    }

    public function deleteMeeting(int $id): void
    {
        OnlineMeeting::findOrFail($id)->delete();
        $this->showDetails = false;
        $this->selectedMeeting = null;
        session()->flash('success', 'Session deleted.');
    }

    public function closeDetails(): void
    {
        $this->showDetails = false;
        $this->selectedMeeting = null;
    }

    private function dispatchMeetingMails(OnlineMeeting $meeting): void
    {
        $recipients = collect([$meeting->client, $meeting->tutor])
            ->filter(fn ($user) => $user && filled($user->email))
            ->unique('id');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)
                    ->later(
                        now()->addSeconds(random_int(2, 8)),
                        new OnlineMeetingScheduledEmail($meeting, $recipient)
                    );
            } catch (\Throwable $th) {
                report($th);
            }
        }
    }

    private function syncAttendanceRecords(OnlineMeeting $meeting): void
    {
        $users = User::whereIn('id', array_filter([
            $meeting->scheduled_by,
            $meeting->client_id,
            $meeting->tutor_id,
        ]))->get();

        $userIds = $users->pluck('id')->all();

        foreach ($users as $user) {
            $meeting->attendances()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'role' => $user->role,
                    'attendance_status' => 'scheduled',
                ]
            );
        }

        $meeting->attendances()->whereNotIn('user_id', $userIds)->delete();
    }

    private function markAutoMissed(): void
    {
        OnlineMeeting::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now()->subMinutes(10))
            ->update([
                'status' => 'completed',
                'ended_at' => now(),
            ]);

        \App\Models\OnlineMeetingAttendance::query()
            ->where('attendance_status', 'scheduled')
            ->whereHas('meeting', function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('ends_at')->where('ends_at', '<', now()->subMinutes(10))
                        ->orWhere(function ($nq) {
                            $nq->whereNull('ends_at')->where('starts_at', '<', now()->subHours(3));
                        });
                });
            })
            ->update(['attendance_status' => 'missed']);
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'booking_id',
            'service_item_id',
            'client_id',
            'tutor_id',
            'title',
            'description',
            'jitsi_room',
            'jitsi_password',
            'starts_at',
            'ends_at',
        ]);

        $this->jitsi_domain = config('services.jitsi.domain', 'meet.jit.si');
        $this->meeting_status = 'scheduled';
        $this->recording_enabled = (bool) config('services.jitsi.recording_enabled', true);
        $this->tutor_privilege = 'participant';
        $this->jitsi_room = '';
    }

    private function generateRoomId(): string
    {
        if (!$this->booking_id) {
            return 'MephEd-0/' . now()->format('YmdHis');
        }

        $prefix = 'MephEd-' . (int) $this->booking_id . '/';
        $next = OnlineMeeting::where('booking_id', $this->booking_id)->count() + 1;
        $room = $prefix . $next;

        while (OnlineMeeting::where('jitsi_room', $room)->exists()) {
            $next++;
            $room = $prefix . $next;
        }

        return $room;
    }

    public function render()
    {
        $this->markAutoMissed();

        $query = OnlineMeeting::with(['client', 'tutor', 'booking', 'attendances'])
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->search, function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhere('jitsi_room', 'like', $term)
                        ->orWhereHas('client', fn ($uq) => $uq->where('name', 'like', $term))
                        ->orWhereHas('tutor', fn ($uq) => $uq->where('name', 'like', $term));
                });
            })
            ->latest();

        return view('livewire.admin.online-meeting-manager', [
            'meetings' => $query->paginate($this->perPage),
            'bookings' => Booking::with(['client', 'tutor', 'serviceItem'])->latest()->limit(120)->get(),
            'clients' => User::where('role', 'client')->orderBy('name')->get(),
            'tutors' => User::where('role', 'tutor')->orderBy('name')->get(),
        ]);
    }
}
