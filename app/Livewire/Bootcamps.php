<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Bootcamp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\BootcampSubmissionNotification;
use Illuminate\Support\Facades\Log;

class Bootcamps extends Component
{
    public $name, $email, $phone, $address;

    protected $rules = [
        'name' => 'required|string|min:3',
        'email' => 'required|email',
        'phone' => 'required|string|min:6|max:20',
        'address' => 'required|string|max:255',  
    ];

    public function save(){
        $this->validate();

        $registrant = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
        ];

        Bootcamp::create($registrant);

        // Get all users with the 'admin' role
        $admins = User::where('role', 'admin')->get();

        // Send email to each admin
        try {
        Mail::to('admin@mephed.ng')->send(new BootcampSubmissionNotification($registrant));
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
        }

        session()->flash('success', 'Registration received. Join our WhatsApp Community to stay updated');

        $this->reset(['name', 'email', 'phone', 'address']);
    }

    public function render()
    {
        return view('livewire.bootcamps');
    }
}
