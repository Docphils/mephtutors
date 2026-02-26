<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $user;

    /**
     * @param $content Array containing subject, title, body, body2, and attachments path
     */
    public function __construct($content, $user)
    {
        $this->content = $content;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->content['subject'],
            from: 'support@mephed.ng'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: ['content' => $this->content, 'user' => $this->user, 'unsubscribeUrl' => URL::signedRoute('newsletter.unsubscribe', ['user' => $this->user->id])]
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        // Attach the file if it exists in the content array
        if (!empty($this->content['attachments'])) {
            $path = storage_path('app/public/' . $this->content['attachments']);
            
            if (file_exists($path)) {
                $attachments[] = Attachment::fromPath($path);
            }
        }

        return $attachments;
    }
}