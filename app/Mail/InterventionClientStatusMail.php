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

class InterventionClientStatusMail extends Mailable implements ShouldQueue
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
            view: 'emails.interventions.client-status',
            with: [
                'enquiry' => $this->enquiry,
                'event' => $this->event,
                'context' => $this->context,
                'headline' => $this->headline(),
                'messageLine' => $this->messageLine(),
                'ctaUrl' => route('client.programmeRequests.manager'),
                'ctaText' => 'Open My Interventions',
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
            'assigned_or_matched' => 'Tutor Matched To Your Intervention ' . $reference,
            'activated' => 'Intervention Activated ' . $reference,
            'completed_review_required' => 'Review Required: Intervention Marked Completed ' . $reference,
            'reassigned' => 'Tutor Reassigned For Your Intervention ' . $reference,
            'closed' => 'Intervention Closed ' . $reference,
            default => 'Intervention Update ' . $reference,
        };
    }

    protected function headline(): string
    {
        return match ($this->event) {
            'assigned_or_matched' => 'Your intervention has been matched',
            'activated' => 'Your intervention is now active',
            'completed_review_required' => 'Tutor marked this intervention as completed',
            'reassigned' => 'Your intervention tutor has been reassigned',
            'closed' => 'Your intervention has been closed',
            default => 'Intervention update',
        };
    }

    protected function messageLine(): string
    {
        return match ($this->event) {
            'assigned_or_matched' => 'A tutor has been assigned to your intervention request.',
            'activated' => 'Payment is confirmed and your intervention has moved to in-progress.',
            'completed_review_required' => 'Please approve or decline completion within 24 hours from your dashboard.',
            'reassigned' => 'Your intervention has been reassigned to ensure continuity and fit.',
            'closed' => 'This intervention is now closed. Thank you for learning with MephEd.',
            default => 'There is a new update on your intervention request.',
        };
    }
}
