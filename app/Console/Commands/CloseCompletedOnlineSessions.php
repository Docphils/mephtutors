<?php

namespace App\Console\Commands;

use App\Models\OnlineMeeting;
use Illuminate\Console\Command;

class CloseCompletedOnlineSessions extends Command
{
    protected $signature = 'sessions:close-completed';
    protected $description = 'Close online sessions that are past end time plus grace period';

    public function handle(): int
    {
        $count = OnlineMeeting::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now()->subMinutes(10))
            ->update(['status' => 'completed']);

        OnlineMeeting::query()
            ->where('status', 'completed')
            ->whereHas('attendances', fn ($q) => $q->where('attendance_status', 'scheduled'))
            ->each(function (OnlineMeeting $meeting): void {
                $meeting->attendances()
                    ->where('attendance_status', 'scheduled')
                    ->update(['attendance_status' => 'missed']);
            });

        $this->info("Closed {$count} online sessions.");

        return self::SUCCESS;
    }
}
