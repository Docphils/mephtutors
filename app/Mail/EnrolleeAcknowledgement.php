<?php

namespace App\Mail;

use App\Models\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrolleeAcknowledgement extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enrollee $enrollee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'We received your registration - MephEd');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollee-acknowledgement',
            with: [
                'enrollee' => $this->enrollee,
                'cohort' => $this->enrollee->cohort,
            ],
        );
    }
}
