<?php

namespace App\Mail;

use App\Models\EbookOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EbookNewOrderNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EbookOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New MephEd eBook order');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.ebooks.admin-order-notice');
    }
}
