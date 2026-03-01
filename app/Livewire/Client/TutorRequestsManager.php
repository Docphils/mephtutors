<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TutorRequest;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Level;
use App\Models\ExamType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
#[Title('My Tutor Requests')]
class TutorRequestsManager extends Component
{
    use WithPagination;

    // Search & Filters
    #[Url(history: true)] public $search = '';
    #[Url(history: true)] public $statusFilter = '';
    #[Url(history: true)] public $sortField = 'created_at';
    #[Url(history: true)] public $sortDirection = 'desc';

    // UI States
    public $step = 1;
    public $showModal = false;
    public $showDetails = false;
    public $isEditing = false;
    public $selectedRequest = null;

    // Form Fields
    public $request_id;
    public $is_for_self = true;
    public $learners = [];
    public $service_id;
    public $service_item_id;
    public $level_id;
    public $exam_type_id;
    public $curriculum = 'N/A';
    public $courses = []; 
    public $delivery_mode = 'online';
    public $session_type = 'individual';
    
    // Scheduling: Array of ['day' => 'Monday', 'time' => '10:00 AM']
    public $preferred_days = [];
    
    public $duration_per_session = 60;
    public $budget_min;
    public $budget_max;
    
    // Address Logic
    public $use_profile_address = true;
    public $state;
    public $city;
    public $street_address;
    public $lesson_address; // Final concatenated value
    
    public $preferred_tutor_gender = 'any';
    public $additional_notes;

    // Field Visibility Toggles
    public $requires_level = false;
    public $requires_exam_type = false;
    public $requires_curriculum = false;
    public $has_subjects = false;

    public $states = [
        'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara','FCT'
    ];

    public $dayOptions = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    protected $listeners = ['openRequestCreate' => 'openCreate'];

    public function mount() 
    {
        if (empty($this->learners)) $this->addLearner();
        if (empty($this->courses)) $this->addCourse();
    }

    // --- Navigation ---
    public function next()
    {
        if ($this->step === 1) {
            $this->validate([
                'is_for_self' => 'required|boolean',
                'learners.*.name' => 'required_if:is_for_self,false|min:3'
            ]);
        } elseif ($this->step === 2) {
            $rules = ['service_item_id' => 'required'];
            if ($this->requires_level) $rules['level_id'] = 'required';
            if ($this->has_subjects) $rules['courses.*.name'] = 'required|min:2';
            if ($this->requires_exam_type) $rules['exam_type_id'] = 'required';
            $this->validate($rules);
        }
        $this->step++;
    }

    public function back() { $this->step--; }

    // --- Dynamic List Management ---
    public function addLearner() { $this->learners[] = ['name' => '']; }
    public function removeLearner($index) { 
        unset($this->learners[$index]); 
        $this->learners = array_values($this->learners);
    }

    public function addCourse() { $this->courses[] = ['name' => '']; }
    public function removeCourse($index) {
        unset($this->courses[$index]);
        $this->courses = array_values($this->courses);
    }

    public function toggleDay($day)
    {
        $exists = collect($this->preferred_days)->search(fn($item) => $item['day'] === $day);
        if ($exists !== false) {
            unset($this->preferred_days[$exists]);
            $this->preferred_days = array_values($this->preferred_days);
        } else {
            $this->preferred_days[] = ['day' => $day, 'time' => '09:00'];
        }
    }

     public function openEdit($id) {
        $request = TutorRequest::where('user_id', Auth::id())->findOrFail($id);
        
        if (!in_array($request->status, ['pending', 'reviewing'])) {
            session()->flash('error', 'Cannot edit a request that is already matched or in progress.');
            return;
        }

        $this->resetForm();
        $this->step = 1;
        $this->request_id = $id;
        $this->is_for_self = $request->is_for_self;
        $this->learners = $request->learners ?? [['name' => '']];
        $this->service_id = $request->serviceItem->service_id ?? null;
        $this->service_item_id = $request->service_item_id;
        $this->level_id = $request->level_id;
        $this->exam_type_id = $request->exam_type_id;
        $this->curriculum = $request->curriculum;
        
        $this->courses = collect($request->subjects ?? [])->map(fn($s) => ['name' => $s])->toArray();
        if (empty($this->courses)) $this->addCourse();
        
        $this->delivery_mode = $request->delivery_mode;
        $this->session_type = $request->session_type;
        $this->preferred_days = $request->preferred_days ?? [];
        $this->preferred_time = $request->preferred_time;
        $this->sessions_per_week = $request->sessions_per_week;
        $this->duration_per_session = $request->duration_per_session;
        $this->budget_min = $request->budget_min;
        $this->budget_max = $request->budget_max;
        $this->state = $request->state;
        $this->city = $request->city;
        $this->lesson_address = $request->lesson_address;
        $this->preferred_tutor_gender = $request->preferred_tutor_gender;
        $this->additional_notes = $request->additional_notes;

        // If lesson address matches profile logic or manual
        $this->use_profile_address = false; 

        $this->updatedServiceItemId();
        $this->isEditing = true;
        $this->showModal = true;
    }

    // --- Conditional UI Logic ---
    public function updatedServiceId() { $this->service_item_id = null; }
    
    public function updatedServiceItemId() {
        $item = ServiceItem::find($this->service_item_id);
        if ($item) {
            $this->requires_level = $item->requires_level;
            $this->requires_exam_type = $item->requires_exam_type;
            $this->requires_curriculum = $item->requires_curriculum;
            $this->has_subjects = $item->has_subjects;
        }
    }

    public function openCreate() {
        $this->resetForm();
        $this->step = 1;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function save() {
        $this->validate([
            'preferred_days' => 'required|array|min:1',
            'delivery_mode' => 'required',
            'session_type' => 'required',
            'preferred_tutor_gender' => 'required',
            'duration_per_session' => 'required|numeric',
            'budget_min' => 'required|numeric',
            'budget_max' => 'required|numeric|gte:budget_min',
        ]);

        $finalAddress = null;
        $profile = Auth::user()->userProfile;
        if ($this->delivery_mode !== 'online') {
            if ($this->use_profile_address) {
                if ($profile && $profile->address && $profile->city && $profile->state) {
                    $finalAddress = "{$profile->address}, {$profile->city}, {$profile->state}";
                } else {
                    $this->addError('use_profile_address', 'Profile address is incomplete. Please enter manually or update your profile.');
                    return;
                }
            } else {
                $this->validate([
                    'state' => 'required',
                    'city' => 'required',
                    'street_address' => 'required'
                ]);
                $finalAddress = "{$this->street_address}, {$this->city}, {$this->state}";
                Auth::user()->userProfile()->updateOrCreate(
                    ['user_id' => Auth::id()], // Search criteria
                    [
                        'address' => $this->street_address,
                        'city' => $this->city,
                        'state' => $this->state
                    ]
                );
            }
        }

        $payload = [
            'user_id' => Auth::id(),
            'is_for_self' => $this->is_for_self,
            'learners' => $this->is_for_self ? null : $this->learners,
            'service_item_id' => $this->service_item_id,
            'level_id' => $this->requires_level ? $this->level_id : null,
            'exam_type_id' => $this->requires_exam_type ? $this->exam_type_id : null,
            'curriculum' => $this->requires_curriculum ? $this->curriculum : 'N/A',
            'subjects' => $this->has_subjects ? array_column($this->courses, 'name') : null,
            'delivery_mode' => $this->delivery_mode,
            'session_type' => $this->session_type,
            'preferred_days' => $this->preferred_days,
            'duration_per_session' => $this->duration_per_session,
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'lesson_address' => $finalAddress,
            'preferred_tutor_gender' => $this->preferred_tutor_gender,
            'additional_notes' => $this->additional_notes,
            'status' => 'pending'
        ];

        if ($this->isEditing) {
            TutorRequest::where('user_id', Auth::id())->findOrFail($this->request_id)->update($payload);
            session()->flash('success', 'Request updated successfully.');
        } else {
            TutorRequest::create($payload);
            session()->flash('success', 'Request submitted successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function openDetails($id) {
        $this->selectedRequest = TutorRequest::where('user_id', Auth::id())
            ->with(['serviceItem', 'level', 'examType'])
            ->findOrFail($id);
        $this->showDetails = true;
    }

    public function resetForm() {
        $this->reset([
            'step', 'request_id', 'is_for_self', 'service_id', 'service_item_id', 'level_id', 
            'exam_type_id', 'additional_notes', 'state', 'city', 'street_address', 'lesson_address',
            'budget_min', 'budget_max', 'preferred_days', 'use_profile_address'
        ]);
        $this->learners = [['name' => '']];
        $this->courses = [['name' => '']];
        $this->delivery_mode = 'online';
        $this->session_type = 'individual';
        $this->preferred_tutor_gender = 'any';
        $this->duration_per_session = 60;
    }

    public function render() {

        $query = TutorRequest::where('user_id', Auth::id())
        ->with(['serviceItem', 'level'])
        // Apply Search (checks Service Item name or Subjects)
        ->when($this->search, function($q) {
            $q->where(function($sub) {
                $sub->whereHas('serviceItem', fn($si) => $si->where('name', 'like', '%' . $this->search . '%'))
                   ->orWhere('subjects', 'like', '%' . $this->search . '%');
            });
        })
        // Apply Status Filter
        ->when($this->statusFilter, function($q) {
            $q->where('status', $this->statusFilter);
        })
        // Apply Dynamic Sorting
        ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.client.tutor-requests-manager', [
            'requests' => $query->paginate(6),
            'services' => Service::where('target', 'tutor_request')->get(),
            'serviceItems' => ServiceItem::where('service_id', $this->service_id)->get(),
            'levels' => Level::all(),
            'examTypes' => ExamType::all(),
        ]);
    }
}