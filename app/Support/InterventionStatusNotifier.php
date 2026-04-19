<?php

namespace App\Support;

use App\Mail\InterventionAdminStatusMail;
use App\Mail\InterventionClientStatusMail;
use App\Mail\InterventionTutorStatusMail;
use App\Models\ProgrammeEnquiry;
use App\Models\ProgrammeEnquiryAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InterventionStatusNotifier
{
    public const ADMIN_SUBMISSION_NOTIFICATION = 'submission_notification';
    public const ADMIN_CANCELLATION_OR_ADJUSTMENT_REQUESTED = 'cancellation_or_adjustment_requested';
    public const ADMIN_DECLINED_APPROVAL = 'declined_approval';
    public const ADMIN_CLOSED_APPROVED = 'closed_approved';
    public const ADMIN_TUTOR_PAYMENT_DISPUTED = 'tutor_payment_disputed';

    public const CLIENT_ASSIGNED_OR_MATCHED = 'assigned_or_matched';
    public const CLIENT_ACTIVATED = 'activated';
    public const CLIENT_COMPLETED_REVIEW_REQUIRED = 'completed_review_required';
    public const CLIENT_REASSIGNED = 'reassigned';
    public const CLIENT_CLOSED = 'closed';

    public const TUTOR_ASSIGNED = 'assigned';
    public const TUTOR_REASSIGNED_OR_CANCELLED = 'reassigned_or_cancelled';
    public const TUTOR_DECLINED = 'declined';
    public const TUTOR_CLOSED_EARNED = 'closed_earned';

    public static function notifyAdmins(ProgrammeEnquiry $enquiry, string $event, array $context = []): void
    {
        $enquiry->loadMissing(['programme', 'user.userProfile']);

        $adminEmails = User::query()
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter(fn ($email) => is_string($email) && trim($email) !== '')
            ->values()
            ->all();

        $recipients = collect(array_merge($adminEmails, ['support@mephed.ng']))
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => $email !== '')
            ->unique()
            ->values();

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->queue(new InterventionAdminStatusMail($enquiry, $event, $context));
            } catch (\Throwable $e) {
                Log::warning('Intervention admin notification failed', [
                    'email' => $email,
                    'event' => $event,
                    'enquiry_id' => $enquiry->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public static function notifyClient(ProgrammeEnquiry $enquiry, string $event, array $context = []): void
    {
        $enquiry->loadMissing(['programme', 'user.userProfile']);
        $email = trim((string) ($enquiry->user?->email ?? ''));
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->queue(new InterventionClientStatusMail($enquiry, $event, $context));
        } catch (\Throwable $e) {
            Log::warning('Intervention client notification failed', [
                'email' => $email,
                'event' => $event,
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function notifyTutor(
        ProgrammeEnquiry $enquiry,
        User|ProgrammeEnquiryAssignment|null $tutor,
        string $event,
        array $context = []
    ): void {
        if ($tutor instanceof ProgrammeEnquiryAssignment) {
            $tutor = $tutor->tutor;
        }

        if (! $tutor instanceof User) {
            return;
        }

        $email = trim((string) ($tutor->email ?? ''));
        if ($email === '') {
            return;
        }

        $enquiry->loadMissing(['programme', 'user.userProfile']);
        $tutor->loadMissing(['tutorProfile', 'userProfile']);

        try {
            Mail::to($email)->queue(new InterventionTutorStatusMail($enquiry, $tutor, $event, $context));
        } catch (\Throwable $e) {
            Log::warning('Intervention tutor notification failed', [
                'email' => $email,
                'event' => $event,
                'enquiry_id' => $enquiry->id,
                'tutor_id' => $tutor->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
