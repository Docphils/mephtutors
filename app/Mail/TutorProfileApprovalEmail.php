<?php

namespace App\Mail;

use App\Models\TutorProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorProfileApprovalEmail extends Mailable
{
    use Queueable, SerializesModels;
    
    private $tutorProfile;
    public function __construct($tutorProfile)
    {
        //
        $this->tutorProfile = $tutorProfile;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tutor Profile Review Status',
            from: 'support@mephed.ng',
            bcc: [new Address('support@mephed.ng', 'MephEd Support')]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tutorProfile-approval',
            with: ['tutorProfile' => $this->tutorProfile]
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
