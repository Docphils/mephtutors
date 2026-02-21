<?php

namespace App\Livewire\Admin\Lesson;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\TutorRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\LessonAssigned;
use App\Mail\ActiveLessonEmail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Lesson Management - Admin Panel')]
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
    public $tutor_request_id, $start_date, $end_date, $location, $days_times, $subjects, $learners, $sessions, $duration;
    public $tutorGender = 'Any', $curriculum = 'British', $status_field = 'Pending', $paymentStatus = 'Pending';
    public $classes, $amount, $client_id, $tutor_id, $paymentEvidence;

    // Dropdown Data
    public $tutorRequests = [];
    public $clients = [];
    public $tutors = [];

    protected $listeners = ['refreshList' => '$refresh'];

    public function mount()
    {
        Gate::authorize('Admin');
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->clients = User::where('role', 'client')->orderBy('name')->get();
        $this->tutors = User::where('role', 'tutor')->orderBy('name')->get();
    }

    public function rules()
    {
        return [
            'tutor_request_id' => 'nullable|exists:tutor_requests,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string',
            'days_times' => 'required|string',
            'subjects' => 'required|string',
            'learners' => 'required|string',
            'sessions' => 'required|numeric',
            'duration' => 'required|string',
            'tutorGender' => 'required|in:Male,Female,Any',
            'curriculum' => 'required|in:British,French,Nigerian,Blended',
            'status_field' => 'required|string',
            'paymentStatus' => 'required|string',
            'classes' => 'required|string',
            'amount' => 'required|numeric',
            'client_id' => 'required|exists:users,id',
            'tutor_id' => 'nullable|exists:users,id',
            'paymentEvidence' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ];
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function showBooking($id)
    {
        $this->selectedBooking = Booking::with(['client', 'tutor', 'tutorRequest'])->findOrFail($id);
        $this->showDetail = true;
    }

    public function openCreate($tutorRequestId = null)
    {
        $this->resetForm();
        if ($tutorRequestId) {
            $tr = TutorRequest::find($tutorRequestId);
            if ($tr) {
                $this->tutor_request_id = $tr->id;
                $this->location = $tr->lesson_address ?? $tr->address ?? trim(($tr->user->userProfile->address . ', ' ?? '') .($tr->user->userProfile->city .', ' ?? ''). ($tr->user->userProfile->state ?? '') );
                $this->subjects = $tr->additional_notes;
                $this->amount = $tr->budget_max ?? $tr->budget_min ?? 0;
                $this->client_id = $tr->user_id;
                $this->tutorGender = ucfirst($tr->preferred_tutor_gender ?? 'Any');
                $this->sessions = $tr->sessions_per_week;
                $this->duration = $tr->duration_per_session ? $tr->duration_per_session . ' mins' : null;
                $this->days_times = $tr->preferred_days . ' @ ' . $tr->preferred_time;
                $this->classes = $tr->level_id ? $tr->level->name : 'Adult';
            }
        }
        $this->showForm = true;
    }

    public function editBooking($id)
    {
        $booking = Booking::findOrFail($id);
        $this->editingId = $booking->id;
        $this->tutor_request_id = $booking->tutor_request_id;
        $this->start_date = $booking->start_date;
        $this->end_date = $booking->end_date;
        $this->location = $booking->location;
        $this->days_times = $booking->days_times;
        $this->subjects = $booking->subjects;
        $this->learners = $booking->learners;
        $this->sessions = $booking->sessions;
        $this->duration = $booking->duration;
        $this->tutorGender = $booking->tutorGender;
        $this->curriculum = $booking->curriculum;
        $this->status_field = $booking->status;
        $this->paymentStatus = $booking->paymentStatus;
        $this->classes = $booking->classes;
        $this->amount = $booking->amount;
        $this->client_id = $booking->client_id;
        $this->tutor_id = $booking->tutor_id;

        $this->showForm = true;
    }

    public function saveBooking()
    {
        $this->validate();

        $data = [
            'tutor_request_id' => $this->tutor_request_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'location' => $this->location,
            'days_times' => $this->days_times,
            'subjects' => $this->subjects,
            'learners' => $this->learners,
            'sessions' => $this->sessions,
            'duration' => $this->duration,
            'tutorGender' => $this->tutorGender,
            'curriculum' => $this->curriculum,
            'status' => $this->status_field,
            'paymentStatus' => $this->paymentStatus,
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
                'amount' => $booking->amount * 0.7, // Commission logic
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

        // Sync tutor request status
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
        $this->tutorRequests = TutorRequest::where('status', 'pending')->latest()->limit(50)->get();
        $this->showAssign = true;
    }

    public function selectTutorRequest($id)
    {
        $tr = TutorRequest::findOrFail($id);
        $this->openCreate($tr->id);
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
        $this->showDelete = true;
    }

    public function deleteBooking()
    {
        if (!$this->selectedBooking) return;
        $booking = Booking::findOrFail($this->selectedBooking->id);
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
        $this->start_date = null;
        $this->end_date = null;
        $this->location = null;
        $this->days_times = null;
        $this->subjects = null;
        $this->learners = null;
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
    }

    public function render()
    {
        $query = Booking::with(['client', 'tutor', 'tutorRequest']);

        if ($this->search) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('learners', 'like', $search)
                  ->orWhere('location', 'like', $search)
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', $search))
                  ->orWhereHas('tutor', fn($q3) => $q3->where('name', 'like', $search));
            });
        }

        if ($this->status && $this->status !== 'all') {
            $query->where('status', ucfirst($this->status));
        }

        return view('livewire.admin.lesson.booking-manager', [
            'bookings' => $query->latest()->paginate($this->perPage)
        ]);
    }
}