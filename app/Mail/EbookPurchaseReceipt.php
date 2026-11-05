<?php

namespace App\Mail;

use App\Models\EbookOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EbookPurchaseReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EbookOrder $order, public string $downloadUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your MephEd eBook is ready');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.ebooks.purchase-receipt');
    }
}
