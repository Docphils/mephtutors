<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TutorRequest;
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
    #[Url(history: true)]
    public $search = '';
    #[Url(history: true)]
    public $statusFilter = '';
    #[Url(history: true)]
    public $sortField = 'created_at';
    #[Url(history: true)]
    public $sortDirection = 'desc';

    // UI States
    public $showModal = false;
    public $showDetails = false;
    public $isEditing = false;
    public $selectedRequest = null;

    // Form fields
    public $request_id;
    public $service_item_id;
    public $level_id;
    public $exam_type_id;
    public $delivery_mode = 'online';
    public $session_type = 'individual';
    public $preferred_days = [];
    public $preferred_time;
    public $sessions_per_week;
    public $duration_per_session;
    public $budget_min;
    public $budget_max;
    public $lesson_address;
    public $preferred_tutor_gender = 'any';
    public $additional_notes;

    public function updatedSearch() { $this->resetPage(); }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    protected $listeners = ['openRequestCreate' => 'openCreate'];

    public function openCreate()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $request = TutorRequest::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($request->status, ['pending', 'reviewing'])) {
            session()->flash('error', 'Only requests in "pending" or "reviewing" status can be edited.');
            return;
        }

        $this->request_id = $id;
        $this->service_item_id = $request->service_item_id;
        $this->level_id = $request->level_id;
        $this->exam_type_id = $request->exam_type_id;
        $this->delivery_mode = $request->delivery_mode;
        $this->session_type = $request->session_type;
        $this->preferred_days = explode(',', $request->preferred_days ?? '');
        $this->preferred_time = $request->preferred_time;
        $this->sessions_per_week = $request->sessions_per_week;
        $this->duration_per_session = $request->duration_per_session;
        $this->budget_min = $request->budget_min;
        $this->budget_max = $request->budget_max;
        $this->lesson_address = $request->lesson_address;
        $this->preferred_tutor_gender = $request->preferred_tutor_gender;
        $this->additional_notes = $request->additional_notes;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function openDetails($id)
    {
        $this->selectedRequest = TutorRequest::with(['serviceItem', 'level', 'examType'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        $this->showDetails = true;
    }

    public function resetForm()
    {
        $this->reset([
            'request_id', 'service_item_id', 'level_id', 'exam_type_id', 
            'preferred_time', 'sessions_per_week', 'duration_per_session', 
            'budget_min', 'budget_max', 'lesson_address', 'additional_notes'
        ]);
        $this->preferred_days = [];
        $this->delivery_mode = 'online';
    }

    public function save()
    {
        $rules = [
            'service_item_id' => 'required|exists:service_items,id',
            'level_id' => 'nullable|exists:levels,id',
            'delivery_mode' => 'required|in:online,offline,hybrid',
            'sessions_per_week' => 'required|integer|min:1',
            'duration_per_session' => 'required|integer',
            'lesson_address' => 'required_if:delivery_mode,offline,hybrid',
            'preferred_tutor_gender' => 'required|in:male,female,any',
        ];

        $validated = $this->validate($rules);
        $data = array_merge($validated, [
            'user_id' => Auth::id(),
            'exam_type_id' => $this->exam_type_id,
            'session_type' => $this->session_type,
            'preferred_days' => implode(',', $this->preferred_days),
            'preferred_time' => $this->preferred_time,
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'additional_notes' => $this->additional_notes,
        ]);

        if ($this->isEditing) {
            $req = TutorRequest::where('user_id', Auth::id())->findOrFail($this->request_id);
            if (!in_array($req->status, ['pending', 'reviewing'])) return;
            $req->update($data);
            session()->flash('success', 'Request updated successfully!');
        } else {
            $data['status'] = 'pending';
            TutorRequest::create($data);
            session()->flash('success', 'Tutor request posted successfully!');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $request = TutorRequest::where('user_id', Auth::id())->findOrFail($id);
        
        if (!in_array($request->status, ['pending', 'reviewing'])) {
            session()->flash('error', 'Cannot delete a request that is already matched or in progress.');
            return;
        }

        $request->delete();
        session()->flash('info', 'Request deleted successfully.');
    }

    public function render()
    {
        $query = TutorRequest::where('user_id', Auth::id())
            ->with(['serviceItem', 'level'])
            ->when($this->search, function($q) {
                $q->whereHas('serviceItem', fn($sq) => $sq->where('name', 'like', '%'.$this->search.'%'));
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.client.tutor-requests-manager', [
            'requests' => $query->paginate(6),
            'serviceItems' => ServiceItem::where('is_active', true)->get(),
            'levels' => Level::all(),
            'examTypes' => ExamType::all()
        ]);
    }
}