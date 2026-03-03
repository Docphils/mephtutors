<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LessonCompleteEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $completedLesson;
    /**
     * Create a new message instance.
     */
    public function __construct($completedLesson)
    {
        //
        $this->completedLesson = $completedLesson;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tutor Marked Your Lesson As Completed',
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
            view: 'emails.lesson-complete',
            with: ['completedLesson' => $this->completedLesson]
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
