<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\ProgrammeEnquiry;
use App\Support\InterventionStatusNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CloseCompletedProgrammeEnquiries extends Command
{
    protected $signature = 'interventions:close-completed';
    protected $description = 'Auto-close intervention requests pending client review for over 24 hours';

    public function handle(): int
    {
        $enquiries = ProgrammeEnquiry::query()
            ->with([
                'programme',
                'user.userProfile',
                'assignments.tutor.tutorProfile',
                'assignments.tutor.userProfile',
            ])
            ->where('status', 'pending_client_review')
            ->get();

        $closedCount = 0;

        foreach ($enquiries as $enquiry) {
            $assignment = $enquiry->assignments
                ->sortByDesc('id')
                ->first(function ($item) {
                    return $item->status === 'completed' && $item->completed_at;
                });

            if (! $assignment || ! $assignment->completed_at) {
                continue;
            }

            $deadline = $assignment->completed_at->copy()->addDay();
            if (now()->lessThan($deadline)) {
                continue;
            }

            try {
                $enquiry->update(['status' => 'completed']);

                Payment::query()->updateOrCreate(
                    ['programme_enquiry_assignment_id' => $assignment->id],
                    [
                        'tutor_id' => $assignment->tutor_id,
                        'booking_id' => null,
                        'amount' => round(max((float) ($enquiry->price_quote ?? 0), 0) * 0.7, 2),
                        'status' => 'Earned',
                    ]
                );

                InterventionStatusNotifier::notifyClient($enquiry, InterventionStatusNotifier::CLIENT_CLOSED);
                InterventionStatusNotifier::notifyTutor(
                    $enquiry,
                    $assignment->tutor,
                    InterventionStatusNotifier::TUTOR_CLOSED_EARNED,
                    ['payment_status' => 'earned']
                );
                InterventionStatusNotifier::notifyAdmins(
                    $enquiry,
                    InterventionStatusNotifier::ADMIN_CLOSED_APPROVED,
                    ['note' => 'Auto-approved after 24 hours without client action.']
                );

                $closedCount++;
            } catch (\Throwable $e) {
                Log::warning('Intervention auto-close failed', [
                    'enquiry_id' => $enquiry->id,
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Auto-closed {$closedCount} intervention request(s).");

        return self::SUCCESS;
    }
}
