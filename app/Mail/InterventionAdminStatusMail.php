<?php

namespace App\Mail;

use App\Models\ProgrammeEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterventionAdminStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProgrammeEnquiry $enquiry,
        public string $event,
        public array $context = []
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine(),
            from: new Address('support@mephed.ng', 'MephEd Support')
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.interventions.admin-status',
            with: [
                'enquiry' => $this->enquiry,
                'event' => $this->event,
                'context' => $this->context,
                'headline' => $this->headline(),
                'messageLine' => $this->messageLine(),
                'ctaUrl' => route('admin.programmeEnquiries'),
                'ctaText' => 'Open Intervention Enquiries',
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function subjectLine(): string
    {
        $reference = '#' . $this->enquiry->id;

        return match ($this->event) {
            'submission_notification' => 'New Intervention Submission ' . $reference,
            'cancellation_or_adjustment_requested' => 'Intervention Cancellation/Adjustment Request ' . $reference,
            'declined_approval' => 'Intervention Approval Declined ' . $reference,
            'closed_approved' => 'Intervention Closed (Tutor Earning Due) ' . $reference,
            'tutor_payment_disputed' => 'Tutor Disputed Intervention Payment ' . $reference,
            default => 'Intervention Status Update ' . $reference,
        };
    }

    protected function headline(): string
    {
        return match ($this->event) {
            'submission_notification' => 'New intervention request submitted',
            'cancellation_or_adjustment_requested' => 'Cancellation or adjustment requested',
            'declined_approval' => 'Client declined completion approval',
            'closed_approved' => 'Intervention closed and tutor earning is due',
            'tutor_payment_disputed' => 'Tutor submitted a payment dispute',
            default => 'Intervention status update',
        };
    }

    protected function messageLine(): string
    {
        return match ($this->event) {
            'submission_notification' => 'A new intervention onboarding request requires review and matching.',
            'cancellation_or_adjustment_requested' => 'A cancellation or adjustment request was raised and needs admin follow-up.',
            'declined_approval' => 'The client declined completion approval and requested further intervention review.',
            'closed_approved' => 'The intervention has been closed. Tutor earning is now due for payout processing.',
            'tutor_payment_disputed' => 'A tutor has disputed payment linked to this intervention. Please review dispute notes.',
            default => 'A new intervention lifecycle event has been recorded.',
        };
    }
}
