<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TutorProfileCreatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $tutorProfile;
    
    public function __construct($tutorProfile)
    {
        $this->tutorProfile = $tutorProfile;
    }

    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tutor Profile Submitted For Review',
            from: 'admin@mephed.ng'
        );
    }

    
    public function content(): Content
    {
        return new Content(
            view: 'emails.tutor-profile-created',
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
