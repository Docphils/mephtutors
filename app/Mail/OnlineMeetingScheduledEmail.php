<?php

namespace App\Mail;

use App\Models\OnlineMeeting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OnlineMeetingScheduledEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OnlineMeeting $meeting,
        public User $recipient
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Online Class Session Scheduled'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.online-meeting-scheduled',
            with: [
                'meeting' => $this->meeting,
                'recipient' => $this->recipient,
                'joinUrl' => route('meetings.join', $this->meeting),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
