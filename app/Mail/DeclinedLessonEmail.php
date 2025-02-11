<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeclinedLessonEmail extends Mailable
{
    use Queueable, SerializesModels;
    private $declinedLesson;
    /**
     * Create a new message instance.
     */
    public function __construct($declinedLesson)
    {
        //
        $this->declinedLesson = $declinedLesson;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Declined Lesson Notification',
            from: 'admin@mephed.ng'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.declined-lesson',
            with: ['declinedLesson' => $this->declinedLesson]
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
