<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use App\Models\TutorRequest;
use App\Models\Crm;

class GuestRequestAcknowledgement extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;
    public $requestModel;
    public $resetUrl;

    /**
     * @param User $user
     * @param string|null $token
     * @param TutorRequest|Crm $requestModel
     */
    public function __construct(User $user, ?string $token = null, $requestModel = null)
    {
        $this->user = $user;
        $this->token = $token;
        $this->requestModel = $requestModel;
    }

    public function build()
    {
        $subject = 'Thanks for your request';

        if ($this->token) {
            $subject = 'Thanks for your request — set your password';
            // Precompute absolute reset URL for the view
            $this->resetUrl = url(route('password.reset', ['token' => $this->token, 'email' => $this->user->email], false));
        } elseif ($this->requestModel instanceof Crm) {
            $subject = 'Thanks — we received your institution request';
        }

        return $this->subject($subject)
                    ->view('emails.guest-request-ack');
    }
}
