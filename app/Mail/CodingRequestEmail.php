<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CodingRequestEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $codingRequest;
    /**
     * Create a new message instance.
     */
    public function __construct($codingRequest)
    {
        //
        $this->codingRequest = $codingRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Coding Tutor Request Received',
            from: 'support@mephed.ng',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.coding-request',
            with: ['codingRequest' => $this->codingRequest]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
