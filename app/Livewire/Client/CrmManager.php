<?php

namespace App\Livewire\Client;

use App\Models\Crm;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
#[Title('Institution Management')]
class CrmManager extends Component
{
    use WithPagination;

    // Search & Filter State
    #[Url(history: true)]
    public $search = '';
    #[Url(history: true)]
    public $statusFilter = '';
    public $sortBy = 'created_at';
    public $sortDir = 'desc';

    // UI State
    public $showModal = false;
    public $showDetails = false;
    public $isEditing = false;
    public $selectedId;

    // Form Fields
    public $institution_name;
    public $institution_address;
    public $service_id; // Added Service Category
    public $service_item_id;
    public $number_of_tutors_required = 1;
    public $delivery_mode = 'onsite';
    public $requirements;
    public $engagement_type;
    public $curriculum;
    public $level;
    public $exam_type;
    public $sessions_per_week = 1;

    // UI Helpers for conditional logic
    public $needsCurriculum = false;
    public $needsLevel = false;
    public $needsExamType = false;
    
    public $activeRequest;

    protected $listeners = ['openCrmCreate' => 'openCreate'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    /**
     * Logic to load specific service items when a broad service category is selected.
     * Matches logic found in CrmRequestWizard.
     */
    public function updatedServiceId()
    {
        $this->service_item_id = null;
        $this->needsCurriculum = $this->needsLevel = $this->needsExamType = false;
    }

    /**
     * Logic to handle conditional fields (Curriculum, Level, etc) 
     * based on the specific Service Item selected.
     */
    public function updatedServiceItemId($value)
    {
        $item = ServiceItem::find($value);
        if ($item) {
            $this->needsCurriculum = $item->requires_curriculum;
            $this->needsLevel = $item->requires_level;
            $this->needsExamType = $item->requires_exam_type;
        } else {
            $this->needsCurriculum = $this->needsLevel = $this->needsExamType = false;
        }
    }

    public function openCreate()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $this->resetValidation();
        $record = Crm::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        if (!in_array($record->status, ['new', 'contacted', 'negotiating'], true)) {
            session()->flash('success', 'This request can no longer be edited at the current stage.');
            return;
        }
        $this->selectedId = $id;
        $this->isEditing = true;
        
        $this->institution_name = $record->institution_name;
        $this->institution_address = $record->institution_address;
        
        // Load the Service ID based on the Service Item relationship
        $this->service_item_id = $record->service_item_id;
        $this->service_id = $record->serviceItem?->service_id;

        $this->number_of_tutors_required = $record->number_of_tutors_required;
        $this->delivery_mode = $record->delivery_mode;
        $this->requirements = $record->requirements;
        $this->engagement_type = $record->engagement_type;
        $this->curriculum = $record->curriculum;
        $this->level = $record->level;
        $this->exam_type = $record->exam_type;
        $this->sessions_per_week = $record->sessions_per_week;

        $this->updatedServiceItemId($this->service_item_id);
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'institution_name' => 'required|string|min:3',
            'institution_address' => 'required|string',
            'service_id' => 'required|exists:services,id',
            'service_item_id' => 'required|exists:service_items,id',
            'number_of_tutors_required' => 'required|integer|min:1',
            'sessions_per_week' => 'required|integer|min:1',
            'delivery_mode' => 'required|in:onsite,online,hybrid',
            'engagement_type' => 'required|in:short_term,long_term,contract,club_management',
            'requirements' => 'nullable|string',
            'curriculum' => $this->needsCurriculum ? 'required|string' : 'nullable',
            'level' => $this->needsLevel ? 'required|string' : 'nullable',
            'exam_type' => $this->needsExamType ? 'required|string' : 'nullable',
        ];

        $validated = $this->validate($rules);
        unset($validated['service_id']); // service_id is for UI categorization, not in Crm table

        if ($this->isEditing) {
            Crm::where('id', $this->selectedId)
                ->where('user_id', Auth::id())
                ->update($validated);
            session()->flash('success', 'Request updated successfully.');
        } else {
            $validated['user_id'] = Auth::id();
            $validated['status'] = 'new';
            $validated['payment_status'] = 'pending';
            Crm::create($validated);
            session()->flash('success', 'Institution request posted!');
        }

        $this->showModal = false;
    }

    public function openView($id)
    {
        $this->activeRequest = Crm::with(['serviceItem.service', 'assignments.assignee'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $this->showDetails = true;
    }

    public function pay($id, PaystackService $paystack)
    {
        $crm = Crm::with('user')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ((float) $crm->quote_amount <= 0) {
            session()->flash('success', 'This request has no quote amount yet.');
            return;
        }

        if ($crm->payment_status === 'paid') {
            session()->flash('success', 'This request is already paid.');
            return;
        }

        try {
            $authorizationUrl = $paystack->initializeCrmPayment($crm);
            return redirect()->away($authorizationUrl);
        } catch (\Exception $e) {
            session()->flash('success', 'Unable to initialize CRM payment right now.');
        }
    }

    public function delete($id)
    {
        $crm = Crm::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$crm) {
            return;
        }

        if (!in_array($crm->status, ['new', 'contacted'], true)) {
            session()->flash('success', 'Only new or contacted requests can be deleted.');
            return;
        }

        $crm->delete();
        session()->flash('success', 'Request removed.');
    }

    private function resetForm()
    {
        $this->reset([
            'institution_name', 'institution_address', 'service_id', 'service_item_id', 
            'requirements', 'engagement_type', 'curriculum', 'level', 'exam_type'
        ]);
        $this->number_of_tutors_required = 1;
        $this->sessions_per_week = 1;
        $this->delivery_mode = 'onsite';
        $this->needsCurriculum = $this->needsLevel = $this->needsExamType = false;
    }

    public function render()
    {
        // Fetch Service Items for the selected category
        $availableServiceItems = $this->service_id 
            ? ServiceItem::where('service_id', $this->service_id)
                ->where('is_active', true)
                ->whereIn('target', ['institutions', 'both'])
                ->get()
            : collect();

        $query = Crm::with(['serviceItem.service', 'assignments.assignee'])
            ->where('user_id', Auth::id())
            ->when($this->search, fn($q) => $q->where('institution_name', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortBy, $this->sortDir);

        return view('livewire.client.crm-manager', [
            'items' => $query->paginate(9),
            'services' => Service::where('target', 'institutions')->get(),
            'serviceItems' => $availableServiceItems
        ]);
    }
}
