<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterMail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

class Newsletter extends Component
{
    public $title;
    public $body = '';
    public $body2;
    public $subject;
    public $recipients;


    public function sendNewsletter()
    {
         

        $content = $this->validate([
            'subject' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'body2' => 'nullable|string',
            'recipients' => 'required|in:All,Admins,Clients,Tutors,TestTutor,TutorsWithProfile,TutorsWithoutProfile',
        ]);

        $query = User::query();

        if ($this->recipients == 'All') {
            $query->where('is_subscribed', true);
        } else {
            switch ($this->recipients) {
                case 'Clients':
                    $query->where('role', 'client');
                    break;
                case 'Admins':
                    $query->where('role', 'admin');
                    break;
                case 'Tutors':
                    $query->where('role', 'tutor');
                    break;
                case 'TutorsWithProfile':
                    $query->where('role', 'tutor')->whereHas('tutorProfile');
                    break;
                case 'TestTutor':
                    $query->where('role', 'tutor')->where('id', '56');
                    break;
                case 'TutorsWithoutProfile':
                    $query->where('role', 'tutor')->whereDoesntHave('tutorProfile');
                    break;
                default:
                    session()->flash('error', 'Invalid recipient selection.');
                    return;
            }
        }

        // Fetch emails
        $subscribedUsers = $query->pluck('email');


        if ($subscribedUsers->isEmpty()) {
            session()->flash('error', 'No subscribed users to send the newsletter to.');
            return;
        }else{
            // Send the newsletter to each subscribed user
            foreach ($subscribedUsers as $email) {
                Mail::to($email)->send(new NewsletterMail($content));
            }
        }
        // Clear fields and show success message
        $this->reset(['title', 'body', 'subject', 'body2', 'recipients']);
        session()->flash('success', 'Newsletter sent successfully!');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.newsletter');
    }
}
