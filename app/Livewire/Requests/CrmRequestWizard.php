<?php

namespace App\Livewire\Requests;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Crm;
use App\Mail\GuestRequestAcknowledgement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.visitor')]
#[Title('Request an Institutional Service - MephEd')]
class CrmRequestWizard extends Component
{
    public $step = 1;

    // Step 1 – User Info
    public $fullname;
    public $email;
    public $phone;
    public $gender;
    public $existingUser = false;

    // Step 2 – CRM Info
    public $institution_name;
    public $institution_address;
    public $number_of_tutors = 1;
    public $engagement_type;
    public $requirements;

    public $service_id;
    public $service_item_id;
    public $serviceItems = [];

    public $delivery_mode = 'online';
    public $sessions_per_week = 1;

    public $allowedEngagementTypes = [
        'short_term',
        'long_term',
        'contract',
        'club_management',
    ];

    public function mount()
    {
        // Initialize default service items if service_id exists
        if ($this->service_id) $this->updatedServiceId();
    }

    public function updatedServiceId()
    {
        $this->serviceItems = ServiceItem::where('service_id', $this->service_id)
            ->where('is_active', true)
            ->whereIn('target', ['institutions', 'both'])
            ->get();

        if ($this->service_item_id && !$this->serviceItems->contains('id', $this->service_item_id)) {
            $this->service_item_id = null;
        }
    }

    public function updatedEmail()
    {
        $email = trim($this->email ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->existingUser = User::where('email', $email)->exists();
            if (! $this->existingUser) $this->resetErrorBag('email');
        } else {
            $this->existingUser = false;
            $this->resetErrorBag('email');
        }
    }

    public function next()
    {
        $rules = [];

        if ($this->step === 1) {
            $rules = [
                'fullname' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:30',
                'gender' => 'required|in:Male,Female',
            ];
            $this->validate($rules);

            if ($this->existingUser) {
                $this->addError('email', 'An account already exists for this email. Please sign in.');
                return;
            }
        }

        if ($this->step === 2) {
            $rules = [
                'institution_name' => 'required|string|max:255',
                'number_of_tutors' => 'required|integer|min:1',
                'institution_address' => 'required|string|max:500',
                'engagement_type' => 'required|in:' . implode(',', $this->allowedEngagementTypes),
                'service_id' => 'required|exists:services,id',
                'service_item_id' => 'required|exists:service_items,id',
            ];
            $this->validate($rules);
        }

        $this->step++;
    }

    public function back()
    {
        $this->step--;
    }

    public function submit()
    {
        $rules = [
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'gender' => 'required|in:Male,Female',
            'institution_name' => 'required|string|max:255',
            'number_of_tutors' => 'required|integer|min:1',
            'engagement_type' => 'required|in:' . implode(',', $this->allowedEngagementTypes),
            'service_id' => 'required|exists:services,id',
            'service_item_id' => 'required|exists:service_items,id',
            'institution_address' => 'required|string|max:500',
            'sessions_per_week' => 'required|integer|min:1',
        ];

        $this->validate($rules);

        // Create or update user
        $user = User::firstOrCreate(
            ['email' => $this->email],
            [
                'name' => $this->fullname,
                'password' => Hash::make(Str::random(10)),
                'role' => 'client'
            ]
        );

        // Update or create profile
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $this->phone,
                'gender' => $this->gender,
            ]
        );

        $crmDelivery = $this->delivery_mode === 'offline' ? 'onsite' : $this->delivery_mode;

        $crm = Crm::create([
            'user_id' => $user->id,
            'institution_name' => $this->institution_name,
            'institution_address' => $this->institution_address,
            'service_item_id' => $this->service_item_id,
            'number_of_tutors_required' => $this->number_of_tutors,
            'delivery_mode' => $crmDelivery,
            'requirements' => $this->requirements,
            'engagement_type' => $this->engagement_type,
            'sessions_per_week' => $this->sessions_per_week,
            'status' => 'new',
        ]);

        // Send acknowledgement email
        try {
            $token = Password::broker()->createToken($user);
            Mail::to($user->email)->send(new GuestRequestAcknowledgement($user, $token, $crm));
        } catch (\Exception $e) {
            logger()->error('CRM Acknowledgement email failed: ' . $e->getMessage());
        }

        $this->step = 4;
    }

    public function render()
    {
        $services = Service::where('is_active', true)
            ->whereHas('serviceItems', fn($q) => $q->where('is_active', true)->whereIn('target', ['institutions', 'both']))
            ->get();

        return view('livewire.requests.crm-request-wizard', [
            'services' => $services,
        ]);
    }
}