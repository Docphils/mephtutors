<?php

namespace App\Mail;

use App\Models\ProgrammeEnquiry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterventionTutorStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProgrammeEnquiry $enquiry,
        public User $tutor,
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
            view: 'emails.interventions.tutor-status',
            with: [
                'enquiry' => $this->enquiry,
                'tutor' => $this->tutor,
                'event' => $this->event,
                'context' => $this->context,
                'headline' => $this->headline(),
                'messageLine' => $this->messageLine(),
                'ctaUrl' => route('tutor.programme-assignments'),
                'ctaText' => 'Open My Intervention Assignments',
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
            'assigned' => 'New Intervention Assignment ' . $reference,
            'reassigned_or_cancelled' => 'Intervention Reassignment/Cancellation Update ' . $reference,
            'declined' => 'Intervention Completion Declined ' . $reference,
            'closed_earned' => 'Intervention Closed - Payout Earned ' . $reference,
            default => 'Intervention Assignment Update ' . $reference,
        };
    }

    protected function headline(): string
    {
        return match ($this->event) {
            'assigned' => 'You have a new intervention assignment',
            'reassigned_or_cancelled' => 'Assignment was reassigned or cancelled',
            'declined' => 'Completion approval was declined by client',
            'closed_earned' => 'Intervention closed and payout marked as earned',
            default => 'Intervention assignment update',
        };
    }

    protected function messageLine(): string
    {
        return match ($this->event) {
            'assigned' => 'Please review learner details and start date in your tutor dashboard.',
            'reassigned_or_cancelled' => 'An assignment change was made by admin. Review your current assignment list.',
            'declined' => 'The client requested a follow-up review after completion.',
            'closed_earned' => 'The intervention has been closed and your payout moved to earned status.',
            default => 'A new update is available on your intervention assignment.',
        };
    }
}
