<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\TutorRequest;
use App\Models\User;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\LessonAssigned;
use App\Mail\ActiveLessonEmail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Lesson Management - MephEd Admin')]
class BookingManager extends Component
{
    use WithPagination, WithFileUploads;

    public $status = 'all';
    public $search = '';
    public $perPage = 15;

    public $selectedBooking = null;
    public $showDetail = false;
    public $showForm = false;
    public $showAssign = false;
    public $showDelete = false;
    public $showActivationModal = false;

    // Form fields for create/edit
    public $editingId = null;
    public $tutor_request_id, $start_date, $end_date, $location;
    public $sessions, $duration, $classes, $amount, $client_id, $tutor_id, $paymentEvidence, $service_item_id;
    public $level_id;
    public $exam_type_id;
    
    // Arrays for dynamic form inputs
    public $days_times = [];
    public $subjects = [];
    public $learners = [];

    public $tutorGender = 'Any', $curriculum = 'British', $status_field = 'Pending', $paymentStatus = 'Pending';

    // Dropdown Data
    public $tutorRequests = [];
    public $clients = [];
    public $tutors = [];

    protected $listeners = ['refreshList' => '$refresh', 'open-new-booking' => 'openAssign'];

    public function mount()
    {
        Gate::authorize('Admin');
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->clients = User::where('role', 'client')->orderBy('name')->get();
        $this->tutors = User::where('role', 'tutor')->whereHas('tutorProfile')->orderBy('name')->get();

    }

    public function getServiceItemProperty()
    {
        if (!$this->service_item_id) {
            return null;
        }

        return ServiceItem::find($this->service_item_id);
    }

    private function serviceUses(string $feature): bool
    {
        if (!$this->serviceItem) {
            return false;
        }

        return match ($feature) {
            'subjects'      => (bool) $this->serviceItem->has_subjects,
            'curriculum'    => (bool) $this->serviceItem->requires_curriculum,
            'level'         => (bool) $this->serviceItem->requires_level,
            'exam_type'     => (bool) $this->serviceItem->requires_exam_type,

            // always required in bookings table
            'learners'      => true,
            'schedule'      => true,

            default => false,
        };
    }

    public function updatedServiceItemId()
    {
        if (!$this->serviceUses('subjects')) {
            $this->subjects = [['name' => '']];
        }

        if (!$this->serviceUses('learners')) {
            $this->learners = [['name' => '', 'age' => '']];
        }

        if (!$this->serviceUses('schedule')) {
            $this->days_times = [['day' => '', 'time' => '']];
        }

        if (!$this->serviceUses('level')) {
            $this->level_id = null;
        }

        if (!$this->serviceUses('exam_type')) {
            $this->exam_type_id = null;
        }
    }

    public function rules()
    {
        $rules = [
            'tutor_request_id' => 'nullable|exists:tutor_requests,id',
            'service_item_id' => 'nullable|exists:service_items,id',

            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string',

            'sessions' => 'required|numeric',
            'duration' => 'required|string',

            'tutorGender' => 'required|in:Male,Female,Any',
            'curriculum' => 'required|in:British,French,Nigerian,Blended,N/A',

            'status_field' => 'required|string',
            'paymentStatus' => 'required|in:Pending,Paid,Failed',

            'classes' => 'required|string',
            'amount' => 'required|numeric',

            'client_id' => 'required|exists:users,id',
            'tutor_id' => 'nullable|exists:users,id',

            'paymentEvidence' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ];

        /*
        |--------------------------------------------------------------------------
        | CONDITIONAL SERVICE FIELDS
        |--------------------------------------------------------------------------
        */

        if ($this->serviceUses('schedule')) {
            $rules['days_times'] = 'required|array|min:1';
            $rules['days_times.*.day'] = 'required|string';
            $rules['days_times.*.time'] = 'required|string';
        }

        if ($this->serviceUses('subjects')) {
            $rules['subjects'] = 'required|array|min:1';
            $rules['subjects.*.name'] = 'required|string';
        }

        if ($this->serviceUses('learners')) {
            $rules['learners'] = 'required|array|min:1';
            $rules['learners.*.name'] = 'required|string';
            $rules['learners.*.age'] = 'nullable|string';
        }

        if ($this->serviceUses('level')) {
            $rules['level_id'] = 'required|exists:levels,id';
        } else {
            $rules['level_id'] = 'nullable';
        }

        if ($this->serviceUses('exam_type')) {
            $rules['exam_type_id'] = 'required|exists:exam_types,id';
        } else {
            $rules['exam_type_id'] = 'nullable';
        }

        return $rules;
    }

    public function updated($field)
    {
        $this->validateOnly($field, $this->rules());
    }

    // Dynamic Input Methods
    public function addSubject() { $this->subjects[] = ['name' => '']; }
    public function removeSubject($index) {
        unset($this->subjects[$index]);
        $this->subjects = array_values($this->subjects);
        if (empty($this->subjects)) $this->addSubject();
    }

    public function addDayTime() { $this->days_times[] = ['day' => '', 'time' => '']; }
    public function removeDayTime($index) {
        unset($this->days_times[$index]);
        $this->days_times = array_values($this->days_times);
        if (empty($this->days_times)) $this->addDayTime();
    }

    public function addLearner() { $this->learners[] = ['name' => '', 'age' => '']; }
    public function removeLearner($index) {
        unset($this->learners[$index]);
        $this->learners = array_values($this->learners);
        if (empty($this->learners)) $this->addLearner();
    }

    public function showBooking($id)
    {
        $booking = Booking::with(['client', 'tutor', 'tutorRequest', 'serviceItem'])->findOrFail($id);
        $this->selectedBooking = $this->formatBookingData($booking);
        $this->showDetail = true;
    }

    public function openCreate($tutorRequestId = null)
    {
        $this->resetForm();
        if ($tutorRequestId) {
            $this->populateFromRequest($tutorRequestId);
        }
        $this->showForm = true;
    }

    private function populateFromRequest($id)
    {
        $tr = TutorRequest::with('serviceItem', 'level', 'user.userProfile')->find($id);
        if (!$tr) return;

        $this->tutor_request_id = $tr->id;
        $this->service_item_id = $tr->service_item_id;
        
        if($tr->delivery_mode !== 'online') {
            $this->location = $tr->lesson_address ?? $tr->address .', '. ($tr->user->userProfile->city ?? '') .', '. ($tr->user->userProfile->state ?? '') ?? trim(
            ($tr->user->userProfile->address ?? '') . ', ' .
            ($tr->user->userProfile->city ?? '') . ', ' .
            ($tr->user->userProfile->state ?? '')
        );
        }else{
            $this->location = 'Online';
        }

        $this->amount = $tr->budget_max ?? $tr->budget_min ?? 0;
        $this->client_id = $tr->user_id;
        $this->tutorGender = ucfirst($tr->preferred_tutor_gender ?? 'Any');
        $this->sessions = $tr->sessions_per_week ?? 1;
        $this->duration = $tr->duration_per_session ? $tr->duration_per_session . ' mins' : null;
        $this->classes = $tr->level_id ? $tr->level->name : 'Adult';
        $this->level_id = $tr->level_id;
        $this->exam_type_id = $tr->exam_type_id;
        $this->curriculum = $tr->curriculum ?? 'British';

        // Map subjects
        $this->subjects = [];
        $trSubjects = is_string($tr->subjects) ? json_decode($tr->subjects, true) : (array)($tr->subjects ?? []);
        foreach ($trSubjects as $sub) {
            $this->subjects[] = ['name' => is_array($sub) ? ($sub['name'] ?? '') : $sub];
        }
        if (empty($this->subjects) && !empty($tr->additional_notes)) {
            $this->subjects[] = ['name' => $tr->additional_notes];
        }
        if (empty($this->subjects)) $this->subjects = [['name' => '']];

        // Map days_times
        $this->days_times = [];
        $prefDays = is_string($tr->preferred_days) ? json_decode($tr->preferred_days, true) : (array)($tr->preferred_days ?? []);
        foreach ($prefDays as $key => $val) {
            if (is_array($val)) {
                $this->days_times[] = [
                    'day' => $val['day'] ?? '',
                    'time' => $val['time'] ?? ''
                ];
            } elseif (is_numeric($key)) {
                $this->days_times[] = ['day' => $val, 'time' => ''];
            } else {
                $this->days_times[] = ['day' => $key, 'time' => $val];
            }
        }
        if (empty($this->days_times)) $this->days_times = [['day' => '', 'time' => '']];

        // Map learners
        $this->learners = [];
        // If it's for self, just set one learner
        if ($tr->is_for_self) {
            $this->learners = [['name' => $tr->user->name . ' (Self)', 'age' => '']];
        } else {
            // Decode learners safely
            $trLearners = is_string($tr->learners)
                ? json_decode($tr->learners, true)
                : (array)($tr->learners ?? []);

            foreach ($trLearners as $learner) {
                if (is_array($learner)) {
                    $this->learners[] = [
                        'name' => $learner['name'] ?? '',
                        'age'  => $learner['age'] ?? ''
                    ];
                } else {
                    $this->learners[] = ['name' => $learner, 'age' => ''];
                }
            }

            // Ensure at least one learner exists
            if (empty($this->learners)) {
                $this->learners = [['name' => '', 'age' => '']];
            }
        }

    }

    public function editBooking($id)
    {
        $booking = Booking::findOrFail($id);
        $this->editingId = $booking->id;
        $this->tutor_request_id = $booking->tutor_request_id;
        $this->service_item_id = $booking->service_item_id;
        $this->start_date = $booking->start_date;
        $this->end_date = $booking->end_date;
        $this->location = $booking->location;
        $this->sessions = $booking->sessions;
        $this->duration = $booking->duration;
        $this->tutorGender = $booking->tutorGender;
        $this->curriculum = $booking->curriculum;
        $this->status_field = $booking->status;
        $this->paymentStatus = $booking->client_payment_status;
        $this->classes = $booking->classes;
        $this->amount = $booking->amount;
        $this->level_id = $booking->level_id;
        $this->exam_type_id = $booking->exam_type_id;
        $this->client_id = $booking->client_id;
        $this->tutor_id = $booking->tutor_id;
        $this->paymentEvidence = null;

        // Populate Arrays for dynamic forms
        $formattedBooking = $this->formatBookingData($booking);

        $this->days_times = empty($formattedBooking->formatted_days_times) 
            ? [['day' => '', 'time' => '']] 
            : $formattedBooking->formatted_days_times;

        $this->learners = empty($formattedBooking->formatted_learners) 
            ? [['name' => '', 'age' => '']] 
            : $formattedBooking->formatted_learners;

        $this->subjects = empty($formattedBooking->formatted_subjects) 
            ? [['name' => '']] 
            : collect($formattedBooking->formatted_subjects)->map(fn($s) => ['name' => $s])->toArray();

        $this->showForm = true;
    }

    public function saveBooking()
    {
        $this->validate();

        // Convert component arrays back to model format 
        $flatSubjects = array_values(array_filter(array_column($this->subjects, 'name')));

        $data = [
            'tutor_request_id' => $this->tutor_request_id,
            'service_item_id' => $this->service_item_id,
            'level_id' => $this->level_id,
            'exam_type_id' => $this->exam_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'location' => $this->location,
            'days_times' => json_encode($this->days_times),
            'subjects' => json_encode($flatSubjects),
            'learners' => json_encode($this->learners),
            'sessions' => $this->sessions,
            'duration' => $this->duration,
            'tutorGender' => $this->tutorGender,
            'curriculum' => $this->curriculum,
            'status' => $this->status_field,
            'client_payment_status' => $this->paymentStatus,
            'classes' => $this->classes,
            'amount' => $this->amount,
            'client_id' => $this->client_id,
            'tutor_id' => $this->tutor_id,
            'user_id' => auth()->id(),
        ];

        if ($this->paymentEvidence) {
            $data['paymentEvidence'] = $this->paymentEvidence->store('payment_evidences', 'public');
        }

        if ($this->editingId) {
            $booking = Booking::findOrFail($this->editingId);
            $booking->update($data);
            session()->flash('success', 'Booking updated successfully');
        } else {
            $booking = Booking::create($data);
            Payment::create([
                'tutor_id' => $booking->tutor_id,
                'booking_id' => $booking->id,
                'amount' => $booking->amount * 0.7,
                'status' => 'Pending',
            ]);

            try {
                if ($booking->client) {
                    Mail::to($booking->client->email)->send(new LessonAssigned($booking));
                }
            } catch (\Exception $e) {
                Log::error('Mail sending failed: ' . $e->getMessage());
            }

            session()->flash('success', 'Booking created successfully');
        }

        if ($booking->tutor_request_id) {
            $tr = TutorRequest::find($booking->tutor_request_id);
            if ($tr && strtolower($tr->status) === 'pending') {
                $tr->status = 'matched';
                $tr->matched_at = now();
                $tr->save();
            }
        }

        $this->resetForm();
        $this->showForm = false;
        $this->resetPage();
    }

    public function openAssign()
    {
        $this->tutorRequests = TutorRequest::with('serviceItem')->where('status', 'pending')->latest()->limit(50)->get();
        $this->showAssign = true;
    }

    public function selectTutorRequest($id)
    {
        $this->openCreate($id);
        $this->showAssign = false;
    }

    public function editActivation($id)
    {
        $this->selectedBooking = Booking::findOrFail($id);
        if ($this->selectedBooking->status !== 'Accepted') {
            session()->flash('error', 'You can only activate accepted lessons.');
            return;
        }
        $this->showActivationModal = true;
    }

    public function submitActivation()
    {
        if (!$this->selectedBooking) return;
        
        $this->selectedBooking->update(['status' => 'Active']);
        $activeLesson = $this->selectedBooking->refresh()->load('tutor');

        try {
            if ($activeLesson->tutor) {
                Mail::to($activeLesson->tutor->email)->send(new ActiveLessonEmail($activeLesson));
            }
            session()->flash('success', 'Lesson activated successfully');
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
            session()->flash('success', 'Lesson activated (email failed).');
        }

        $this->selectedBooking = null;
        $this->showActivationModal = false;
        $this->resetPage();
    }

    public function openDelete($id)
    {
        $this->selectedBooking = Booking::findOrFail($id);
        if($this->selectedBooking->status !== 'Pending') {
            session()->flash('error', 'Only pending lessons can be deleted. Please update the status first.');
            $this->selectedBooking = null;
            return;
        }
        $this->showDelete = true;
    }

    public function deleteBooking()
    {
        if (!$this->selectedBooking) return;
        $booking = Booking::findOrFail($this->selectedBooking->id);
        if ($booking->status !== 'Pending') {
            $this->showDelete = false;
            $this->selectedBooking = null;
            $this->resetPage();
            session()->flash('error', 'Cannot delete a lesson that is no longer pending. Please update its status first.');
            return;
        }
        $booking->payments()->delete();
        $booking->delete();
        $this->showDelete = false;
        $this->selectedBooking = null;
        $this->resetPage();
        session()->flash('success', 'Booking deleted');
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->tutor_request_id = null;
        $this->service_item_id = null;
        $this->start_date = null;
        $this->end_date = null;
        $this->location = null;
        $this->days_times = [['day' => '', 'time' => '']];
        $this->subjects = [['name' => '']];
        $this->learners = [['name' => '', 'age' => '']];
        $this->sessions = null;
        $this->duration = null;
        $this->tutorGender = 'Any';
        $this->curriculum = 'British';
        $this->status_field = 'Pending';
        $this->paymentStatus = 'Pending';
        $this->classes = null;
        $this->amount = 0;
        $this->client_id = null;
        $this->tutor_id = null;
        $this->paymentEvidence = null;
        $this->level_id = null;
        $this->exam_type_id = null;
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->selectedBooking = null;
    }

    private function formatBookingData($booking)
    {
        $learners = is_string($booking->learners) ? json_decode($booking->learners, true) : (array)($booking->learners ?? []);
        $subjects = is_string($booking->subjects) ? json_decode($booking->subjects, true) : (array)($booking->subjects ?? []);
        $daysTimes = is_string($booking->days_times) ? json_decode($booking->days_times, true) : (array)($booking->days_times ?? []);

        // Enforce consistent array shapes
        $booking->formatted_learners = collect($learners)->map(function($l) {
            return is_array($l) ? $l : ['name' => $l, 'age' => ''];
        })->toArray();

        $booking->formatted_subjects = collect($subjects)->map(function($s) {
            return is_array($s) ? ($s['name'] ?? '') : $s;
        })->toArray();

        $booking->formatted_days_times = collect($daysTimes)->map(function($dt, $key) {
            if (is_array($dt)) {
                return ['day' => $dt['day'] ?? '', 'time' => $dt['time'] ?? ''];
            }
            return is_numeric($key) ? ['day' => $dt, 'time' => ''] : ['day' => $key, 'time' => $dt];
        })->toArray();

        // Convenience strings for listing
        $booking->learners_string = collect($booking->formatted_learners)->pluck('name')->filter()->implode(', ') ?: 'N/A';
        $booking->subjects_string = implode(', ', array_filter($booking->formatted_subjects)) ?: 'No Subjects';

        return $booking;
    }

    public function render()
    {
        $query = Booking::with(['client', 'tutor', 'tutorRequest', 'serviceItem']);

        if ($this->search) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('learners', 'like', $search)
                  ->orWhere('location', 'like', $search)
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', $search))
                  ->orWhereHas('tutor', fn($q3) => $q3->where('name', 'like', $search))
                  ->orWhereHas('serviceItem', fn($q4) => $q4->where('name', 'like', $search));
            });
        }

        if ($this->status && $this->status !== 'all') {
            $query->where('status', ucfirst($this->status));
        }

        $paginator = $query->latest()->paginate($this->perPage);

        // Format JSON fields via our helper before sending to the blade
        $paginator->getCollection()->transform(function($booking) {
            return $this->formatBookingData($booking);
        });

        return view('livewire.admin.booking-manager', [
            'bookings' => $paginator
        ]);
    }
}