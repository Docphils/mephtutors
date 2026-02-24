<?php

namespace App\Livewire\Requests;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Level;
use App\Models\ExamType;
use App\Models\TutorRequest;
use App\Mail\GuestRequestAcknowledgement;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.visitor')]
#[Title('Request a Tutor - MephEd')]
class TutorRequestWizard extends Component
{
    public $step = 1;

    // Step 1: User Identity
    public $fullname;
    public $email;
    public $phone;
    public $gender;
    public $existingUser = false;
    public bool $is_for_self = true;
    public $learners = [];

    // Step 2: Service & Academics
    public $service_id;
    public $service_item_id;
    public $level_id;
    public $exam_type_id;
    public $curriculum = 'N/A';
    public $courses = []; 
    public $serviceItems = [];
    public $requires_level = false;
    public $requires_exam_type = false;
    public $requires_curriculum = false;
    public $has_subjects = false;

    // Step 3: Logistics & Budget
    public $delivery_mode = 'online';
    public $duration_per_session = 60;
    public $budget_min;
    public $budget_max;
    public $state;
    public $city;
    public $address;
    public $additional_notes;
    public $session_type = 'individual';
    public $preferred_days = []; 
    public $preferred_tutor_gender = 'any';

    public $states = [
        'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara','FCT'
    ];

    public $dayOptions = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
    ];

    public function mount($serviceItem)
    {
        $item = ServiceItem::where('slug', $serviceItem)->first();

        if (!$item) {
            return redirect()->route('contact');
        }

        $this->service_id = $item->service_id;
        $this->service_item_id = $item->id;
        $this->requires_level = $item->requires_level;
        $this->requires_exam_type = $item->requires_exam_type;
        $this->requires_curriculum = $item->requires_curriculum;
        $this->has_subjects = $item->has_subjects;

        if (empty($this->learners)) {
            $this->addLearner();
        }
        if (empty($this->courses)) {
            $this->addCourse();
        }

        $this->updatedServiceId();
    }

    public function updatedEmail()
    {
        $this->validateOnly('email', ['email' => 'required|email']);
        $this->existingUser = User::where('email', trim($this->email))->exists();
        if ($this->existingUser) {
            $this->addError('email', 'This email is already registered. Please login to continue.');
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
        if (empty($this->learners)) $this->addLearner();
    }

    public function addCourse()
    {
        $this->courses[] = ['name' => ''];
    }

    public function removeCourse($index)
    {
        unset($this->courses[$index]);
        $this->courses = array_values($this->courses);
        if (empty($this->courses)) $this->addCourse();
    }

    public function updatedServiceItemId()
    {
        $item = ServiceItem::find($this->service_item_id);
        if ($item) {
            $this->requires_level = $item->requires_level;
            $this->requires_exam_type = $item->requires_exam_type;
            $this->requires_curriculum = $item->requires_curriculum;
            $this->has_subjects = $item->has_subjects;
        }
    }

    public function updatedServiceId()
    {
        $this->serviceItems = ServiceItem::where('service_id', $this->service_id)->get();
    }

    public function next()
    {
        if ($this->step === 1) {
            $this->validate([
                'fullname' => 'required|min:3',
                'email' => 'required|email',
                'phone' => 'required',
                'gender' => 'required',
                'learners.*.name' => $this->is_for_self ? 'nullable' : 'required|min:2',
            ]);
            if ($this->existingUser) return;
        }

        if ($this->step === 2) {
            $rules = ['service_item_id' => 'required'];
            if ($this->requires_level) $rules['level_id'] = 'required';
            if ($this->has_subjects) $rules['courses.*.name'] = 'required|min:2';
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
        $this->validate([
            'preferred_days' => 'required|array|min:1',
            'delivery_mode' => 'required',
            'preferred_tutor_gender' => 'required',
            'budget_min' => 'required|numeric',
            'budget_max' => 'required|numeric|gte:budget_min',
            'duration_per_session' => 'required|numeric',
        ]);

        // 1. Create or Get User
        $user = User::firstOrCreate(
            ['email' => $this->email],
            [
                'name' => $this->fullname,
                'password' => Hash::make(Str::random(12)),
                'role' => 'client'
            ]
        );

        // 2. Update Profile
        UserProfile::updateOrCreate(['user_id' => $user->id], [
            'phone' => $this->phone,
            'gender' => $this->gender,
            'state' => $this->state,
            'city' => $this->city,
            'address' => $this->address
        ]);

        $finalSchedule = collect($this->preferred_days)
            ->filter(fn($day) => isset($day['selected']) && $day['selected'])
            ->map(fn($day) => $day['time'] ?? 'Anytime')
            ->toArray();

        // 3. Create Request
        $request = TutorRequest::create([
            'user_id' => $user->id,
            'is_for_self' => $this->is_for_self,
            'learners' => $this->is_for_self ? null : $this->learners,
            'service_item_id' => $this->service_item_id,
            'level_id' => $this->level_id,
            'exam_type_id' => $this->exam_type_id,
            'curriculum' => $this->curriculum,
            'subjects' => $this->has_subjects ? array_column($this->courses, 'name') : null,
            'delivery_mode' => $this->delivery_mode,
            'session_type' => $this->session_type,
            'preferred_days' => $finalSchedule, // JSON mapped array
            'duration_per_session' => $this->duration_per_session,
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'lesson_address' => $this->address,
            'preferred_tutor_gender' => $this->preferred_tutor_gender,
            'additional_notes' => $this->additional_notes,
            'status' => 'pending',
        ]);

        // 4. Email Setup
        try {
            $token = Password::broker()->createToken($user);
            Mail::to($user->email)->send(new GuestRequestAcknowledgement($user, $token, $request));
        } catch (\Exception $e) {
            logger()->error('Tutor Request Acknowledgement email failed: ' . $e->getMessage());
        }

        $this->step = 4;
    }

    public function render()
    {
        return view('livewire.requests.tutor-request-wizard', [
            'services' => Service::where('is_active', true)->where('target', 'tutor_request')->get(),
            'levels' => Level::all(),
            'examTypes' => ExamType::all(),
        ]);
    }
}