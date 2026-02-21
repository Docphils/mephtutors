<?php

namespace App\Livewire\Requests;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Level;
use App\Models\ExamType;
use App\Models\TutorRequest;
use App\Models\Crm;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuestRequestAcknowledgement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.visitor')]
#[Title('Request a Service - MephEd')]
class RequestWizard extends Component
{
    public $step = 1;

    // Step 1 – User Info & Learner Details
    public $fullname;
    public $email;
    public $phone;
    public $gender;
    public $existingUser = false;
    
    // New Learner Fields
    public bool $is_for_self = true;
    public $learners = []; // Format: [['name' => 'Name 1'], ['name' => 'Name 2']]

    // Step 2 – Service
    public $service_id;
    public $service_item_id;
    public $level_id;
    public $exam_type_id;
    public $curriculum = 'N/A'; // New Field

    public $serviceItems = [];
    public $requires_level = false;
    public $requires_exam_type = false;

    // CRM support
    public $is_crm = false;
    public $institution_name;
    public $contact_person;
    public $engagement_type;
    public $requirements;
    public $number_of_tutors = 1;

    public $allowedEngagementTypes = [
        'short_term',
        'long_term',
        'contract',
        'club_management',
    ];

    public $states = [
        'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara','FCT'
    ];

    // Step 3 – Logistics
    public $delivery_mode = 'online';
    public $sessions_per_week;
    public $duration_per_session;
    public $budget_min;
    public $budget_max;
    public $state;
    public $city;
    public $address;
    public $additional_notes;
    public $session_type = 'individual'; 
    public $preferred_days;
    public $preferred_time;
    public $preferred_tutor_gender = 'any';

    public function mount($serviceItem)
    {
        $item = ServiceItem::where('slug', $serviceItem)->first();

        if (!$item) {
            return redirect()->route('contact')
                ->with('info', 'Please contact us directly for the ' . strToUpper($serviceItem) . ' service.');
        }
        $this->serviceItem = $item;
        $this->service_id = $item->service_id;
        $this->service_item_id = $item->id;
        $this->requires_level = $item->requires_level;
        $this->requires_exam_type = $item->requires_exam_type;
        $this->is_crm = in_array($item->target, ['institutions', 'both']);

        // Initialize with one empty learner if not for self
        if (empty($this->learners)) {
            $this->addLearner();
        }

        $this->updatedServiceId();
    }

    /**
     * Learner Management
     */
    public function updatedIsForSelf()
    {
        if ($this->is_for_self) {
            $this->learners = [];
        } elseif (empty($this->learners)) {
            $this->addLearner();
        }
    }

    public function addLearner()
    {
        $this->learners[] = ['name' => ''];
    }

    public function removeLearner($index)
    {
        unset($this->learners[$index]);
        $this->learners = array_values($this->learners);
        
        if (empty($this->learners)) {
            $this->addLearner();
        }
    }

    public function updatedServiceId()
    {
        $targetValues = $this->is_crm ? ['institutions', 'both'] : ['tutor_request', 'both'];
        $this->serviceItems = ServiceItem::where('service_id', $this->service_id)
            ->where('is_active', true)
            ->whereIn('target', $targetValues)
            ->get();

        if ($this->service_item_id) {
            $found = $this->serviceItems->contains('id', $this->service_item_id);
            if (! $found) {
                $this->service_item_id = null;
            }
        }
    }

    public function updatedServiceItemId()
    {
        $item = ServiceItem::find($this->service_item_id);
        if ($item) {
            $this->requires_level = $item->requires_level;
            $this->requires_exam_type = $item->requires_exam_type;
            $this->is_crm = in_array($item->target, ['institutions', 'both']);
            $this->service_id = $item->service_id;
        }
    }

    public function updatedEmail()
    {
        $email = trim($this->email ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->existingUser = \App\Models\User::where('email', $email)->exists();
            if (! $this->existingUser) {
                $this->resetErrorBag('email');
            }
        } else {
            $this->existingUser = false;
            $this->resetErrorBag('email');
        }
    }

    public function next()
    {
        if ($this->step === 1 && $this->existingUser) {
            $this->addError('email', 'An account already exists for this email. Please sign in.');
            return;
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
            'fullname' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'service_item_id' => 'required',
        ];

        if ($this->is_crm) {
            $rules = array_merge($rules, [
                'institution_name' => 'required|string',
                'number_of_tutors' => 'nullable|integer|min:1',
                'engagement_type' => 'required|string',
                'address' => 'required|string',
            ]);
        } else {
            // Tutor Request specific validation
            $rules = array_merge($rules, [
                'session_type' => 'required|in:individual,group',
                'sessions_per_week' => 'required|integer|min:1',
                'duration_per_session' => 'required|integer|min:30',
                'is_for_self' => 'required|boolean',
                'curriculum' => 'required|in:British,French,Nigerian,Blended,N/A',
            ]);

            if (!$this->is_for_self) {
                $rules['learners'] = 'required|array|min:1';
                $rules['learners.*.name'] = 'required|string|min:2';
            }

            if (in_array($this->delivery_mode, ['offline', 'hybrid'])) {
                $rules['state'] = 'required|string';
                $rules['city'] = 'required|string';
                $rules['address'] = 'required|string';
            }
        }

        if ($this->requires_level) {
            $rules['level_id'] = 'required|exists:levels,id';
        }

        if ($this->requires_exam_type) {
            $rules['exam_type_id'] = 'required|exists:exam_types,id';
        }

        $rules['service_id'] = 'required|exists:services,id';

        $this->validate($rules);

        $user = User::firstOrCreate(
            ['email' => $this->email],
            [
                'name' => $this->fullname,
                'password' => Hash::make(Str::random(10)),
                'role' => 'client'
            ]
        );

        $profileData = [
            'user_id' => $user->id,
            'phone' => $this->phone,
            'state' => $this->state ?? '',
            'city' => $this->city ?? '',
            'address' => $this->address ?? '',
            'DOB' => now(),
            'gender' => $this->gender ?? 'Male'
        ];

        if ($user->userProfile) {
            $user->userProfile->update($profileData);
        } else {
            UserProfile::create($profileData);
        }

        if ($this->is_crm) {
            $crmDelivery = $this->delivery_mode === 'offline' ? 'onsite' : $this->delivery_mode;

            $crm = Crm::create([
                'user_id' => $user->id,
                'institution_name' => $this->institution_name,
                'institution_address' => trim(($this->address ?? '') . ' ' . ($this->city ?? '') . ' ' . ($this->state ?? '')),
                'service_item_id' => $this->service_item_id,
                'number_of_tutors_required' => $this->number_of_tutors ?? 1,
                'delivery_mode' => in_array($crmDelivery, ['onsite','online','hybrid']) ? $crmDelivery : 'onsite',
                'requirements' => $this->requirements ?? $this->additional_notes,
                'engagement_type' => $this->engagement_type,
                'status' => 'new',
            ]);

            try {
                $token = Password::broker()->createToken($user);
                Mail::to($user->email)->send(new GuestRequestAcknowledgement($user, $token, $crm));
            } catch (\Exception $e) {
                logger()->error('Failed sending CRM acknowledgement: '.$e->getMessage());
            }
        } else {
            // Create tutor request with NEW fields
            $lessonAddress = $this->address ?: null;
            $tutorRequest = TutorRequest::create([
                'user_id' => $user->id,
                'is_for_self' => $this->is_for_self,
                'learners' => $this->is_for_self ? null : $this->learners,
                'service_item_id' => $this->service_item_id,
                'level_id' => $this->requires_level ? $this->level_id : null,
                'exam_type_id' => $this->requires_exam_type ? $this->exam_type_id : null,
                'curriculum' => $this->curriculum,
                'delivery_mode' => $this->delivery_mode,
                'session_type' => $this->session_type,
                'preferred_days' => $this->preferred_days,
                'preferred_time' => $this->preferred_time,
                'sessions_per_week' => $this->sessions_per_week,
                'duration_per_session' => $this->duration_per_session,
                'budget_min' => $this->budget_min,
                'budget_max' => $this->budget_max,
                'lesson_address' => $lessonAddress,
                'preferred_tutor_gender' => $this->preferred_tutor_gender ?? 'any',
                'additional_notes' => $this->additional_notes,
                'status' => 'pending',
            ]);

            try {
                $token = Password::broker()->createToken($user);
                Mail::to($user->email)->send(new GuestRequestAcknowledgement($user, $token, $tutorRequest));
            } catch (\Exception $e) {
                logger()->error('Failed sending guest acknowledgement: '.$e->getMessage());
            }
        }

        $this->step = 4;
    }

    public function render()
    {
        $target = $this->is_crm ? ['institutions', 'both'] : ['tutor_request', 'both'];

        $services = Service::where('is_active', true)
            ->whereHas('serviceItems', function ($q) use ($target) {
                $q->where('is_active', true)->whereIn('target', $target);
            })->get();

        return view('livewire.requests.request-wizard', [
            'services' => $services,
            'levels' => Level::all(),
            'examTypes' => ExamType::all(),
        ]);
    }
}