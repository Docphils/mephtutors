<?php

namespace App\Mail;

use App\Models\ProgrammeEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProgrammeEnquiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public ProgrammeEnquiry $enquiry;

    /**
     * Create a new message instance.
     */
    public function __construct(ProgrammeEnquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Programme Enquiry: ' . ($this->enquiry->programme?->name ?? 'General'),
            from: 'no-reply@mephed.ng'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.programme-enquiry-notification'
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

