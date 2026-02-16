<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BootcampSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $registrant;

    /**
     * Create a new message instance.
     *
     * @param array $registrant
     */
    public function __construct($registrant)
    {
        $this->registrant = $registrant;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Bootcamp Registration',
            from: 'info@mephed.ng'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bootcamp-reg', 
            with: ['registrant' => $this->registrant]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
