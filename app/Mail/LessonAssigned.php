<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LessonAssigned extends Mailable
{
    use Queueable, SerializesModels;

    private $newBooking;
    private $tutor;
    private $client;
    /**
     * Create a new message instance.
     */
    public function __construct(Booking $newBooking)
    {
        //
        $this->newBooking = $newBooking;
        $this->tutor = $newBooking->tutor;
        $this->client = $newBooking->client;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $cc = [];
        if (!empty($this->tutor?->email)) {
            $cc[] = new Address($this->tutor->email, $this->tutor->name ?? 'Assigned Tutor');
        }

        return new Envelope(
            subject: 'Lesson Assigned To Your Request',
            from: 'admin@mephed.ng',
            cc: $cc
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.lesson-assigned',
            with: ['newBooking' => $this->newBooking,
                    'tutor' => $this->tutor,
                    'client' => $this->client]
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
