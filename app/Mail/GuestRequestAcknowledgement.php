<?php

namespace App\Mail;

use App\Models\Crm;
use App\Models\TutorRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
     * @param TutorRequest|Crm|null $requestModel
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
            $subject = 'Thanks for your request - set your password';
            $this->resetUrl = url(route('password.reset', ['token' => $this->token, 'email' => $this->user->email], false));
        } elseif ($this->requestModel instanceof Crm) {
            $subject = 'Thanks - we received your institution request';
        }

        return $this->from('support@mephed.ng', 'MephEd Support')
            ->bcc('support@mephed.ng')
            ->subject($subject)
            ->view('emails.guest-request-ack');
    }
}
