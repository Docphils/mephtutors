<?php

namespace App\Mail;

use App\Models\ProgrammeEnquiry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProgrammeGuestAcknowledgement extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public ?string $token;
    public ProgrammeEnquiry $programmeRequest;
    public ?string $resetUrl = null;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?string $token, ProgrammeEnquiry $programmeRequest)
    {
        $this->user = $user;
        $this->token = $token;
        $this->programmeRequest = $programmeRequest;

        if ($token) {
            $this->resetUrl = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));
        }
    }

    public function build()
    {
        $subject = $this->token
            ? 'Programme request received - set your password'
            : 'Programme request received';

        return $this->from('support@mephed.ng', 'MephEd Support')
            ->bcc('support@mephed.ng')
            ->subject($subject)
            ->view('emails.programme-guest-ack');
    }
}

