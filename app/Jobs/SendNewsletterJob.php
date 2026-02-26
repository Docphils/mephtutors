<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Newsletter;
use App\Mail\NewsletterMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Newsletter $newsletter;

    public $tries = 3;
    public $timeout = 1200;

    public function __construct(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    public function handle(): void
    {
        $query = User::query();

        switch ($this->newsletter->recipients) {
            case 'All':
                $query->where('is_subscribed', true);
                break;
            case 'Clients':
                $query->where('role', 'client')->where('is_subscribed', true);
                break;
            case 'Admins':
                $query->where('role', 'admin')->where('is_subscribed', true);
                break;
            case 'Tutors':
                $query->where('role', 'tutor')->where('is_subscribed', true);
                break;
            case 'TutorsWithProfile':
                $query->where('role', 'tutor')
                    ->whereHas('tutorProfile')
                    ->where('is_subscribed', true);
                break;
            case 'TutorsWithoutProfile':
                $query->where('role', 'tutor')
                    ->whereDoesntHave('tutorProfile')
                    ->where('is_subscribed', true);
                break;
            case 'TestTutor':
                $query->where('id', 56);
                break;
        }

        $sent = [];

        $query->chunk(200, function ($users) use (&$sent) {
            foreach ($users as $user) {
                try {
                    $personalizedContent = $this->newsletter->toArray();

                    // Replace {name} with actual user's name
                    $personalizedContent['body'] = str_replace(
                        '{name}',
                        $user->name, // or $user->first_name if you store that
                        $personalizedContent['body']
                    );

                    Mail::to($user->email)
                        ->queue(new NewsletterMail($personalizedContent, $user));

                    $sent[] = $user->email;
                } catch (\Exception $e) {
                    Log::error("Newsletter send failed: {$user->email} — {$e->getMessage()}");
                }
            }
        });

        $this->newsletter->update([
            'status' => 'Sent',
            'sent_to' => $sent,
            'sent_at' => now(),
        ]);
    }
}