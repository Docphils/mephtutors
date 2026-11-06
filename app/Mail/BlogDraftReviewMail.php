<?php

namespace App\Mail;

use App\Models\BlogPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BlogDraftReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly BlogPost $post,
        public readonly string $reviewUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MephEd blog draft ready for review: '.$this->post->title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.blog-draft-review',
            with: ['post' => $this->post, 'reviewUrl' => $this->reviewUrl],
        );
    }
}
