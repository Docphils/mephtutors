<?php

namespace App\Livewire\Tutor;

use App\Models\OnlineMeetingAttendance;
use App\Models\OnlineMeeting;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tutor Online Sessions - MephEd')]
class OnlineMeetings extends Component
{
    use WithPagination;

    public string $tab = 'scheduled';
    public int $perPage = 10;

    public function mount(): void
    {
        Gate::authorize('Tutor');
        $this->markAutoMissed();
    }

    public function setTab(string $tab): void
    {
        $allowed = ['scheduled', 'attended', 'missed'];
        if (in_array($tab, $allowed, true)) {
            $this->tab = $tab;
            $this->resetPage();
        }
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

        OnlineMeetingAttendance::query()
            ->where('user_id', auth()->id())
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

    public function render()
    {
        $this->markAutoMissed();

        $query = OnlineMeetingAttendance::with(['meeting.client', 'meeting.serviceItem'])
            ->where('user_id', auth()->id())
            ->where('role', 'tutor')
            ->where('attendance_status', $this->tab)
            ->latest();

        $base = OnlineMeetingAttendance::where('user_id', auth()->id())->where('role', 'tutor');

        return view('livewire.tutor.online-meetings', [
            'records' => $query->paginate($this->perPage),
            'counts' => [
                'scheduled' => (clone $base)->where('attendance_status', 'scheduled')->count(),
                'attended' => (clone $base)->where('attendance_status', 'attended')->count(),
                'missed' => (clone $base)->where('attendance_status', 'missed')->count(),
            ],
        ]);
    }
}
