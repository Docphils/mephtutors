<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpdatedCodingOrClubEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $updatedRequest;
    /**
     * Create a new message instance.
     */
    public function __construct($updatedRequest)
    {
        //
        $this->updatedRequest = $updatedRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Institution Request Status Updated',
            from: 'admin@mephed.ng',
            bcc: [new Address('admin@mephed.ng', 'MephEd Admin')]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.updated-coding-club',
            with: ['updatedRequest' => $this->updatedRequest]
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
