<?php

namespace App\Livewire\Client;

use App\Models\Crm;
use App\Models\ServiceItem;
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
    public $service_item_id;
    public $number_of_tutors_required = 1;
    public $delivery_mode = 'onsite';
    public $requirements;
    public $engagement_type;
    
    public $activeRequest;

    protected $listeners = ['openCrmCreate' => 'openCreate'];

    // Reset pagination when search or filter changes
    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

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
        $record = Crm::findOrFail($id);
        $this->selectedId = $id;
        $this->isEditing = true;
        
        $this->institution_name = $record->institution_name;
        $this->institution_address = $record->institution_address;
        $this->service_item_id = $record->service_item_id;
        $this->number_of_tutors_required = $record->number_of_tutors_required;
        $this->delivery_mode = $record->delivery_mode;
        $this->requirements = $record->requirements;
        $this->engagement_type = $record->engagement_type;

        $this->showModal = true;
    }

    public function openView($id)
    {
        $this->activeRequest = Crm::with('serviceItem')->findOrFail($id);
        $this->showDetails = true;
    }

    public function save()
    {
        $rules = [
            'institution_name' => 'required|string|min:3',
            'institution_address' => 'required|string',
            'service_item_id' => 'required|exists:service_items,id',
            'number_of_tutors_required' => 'required|integer|min:1',
            'delivery_mode' => 'required|in:onsite,online,hybrid',
            'engagement_type' => 'required|in:short_term,long_term,contract,club_management',
            'requirements' => 'nullable|string',
        ];

        $validated = $this->validate($rules);

        if ($this->isEditing) {
            Crm::find($this->selectedId)->update($validated);
            session()->flash('success', 'Request updated successfully.');
        } else {
            $validated['user_id'] = Auth::id();
            $validated['status'] = 'new';
            Crm::create($validated);
            session()->flash('success', 'Institution request posted!');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        Crm::where('id', $id)->where('user_id', Auth::id())->delete();
        session()->flash('success', 'Request removed.');
    }

    private function resetForm()
    {
        $this->reset(['institution_name', 'institution_address', 'service_item_id', 'requirements', 'engagement_type']);
        $this->number_of_tutors_required = 1;
        $this->delivery_mode = 'onsite';
    }

    public function render()
    {
        $query = Crm::with('serviceItem')
            ->where('user_id', Auth::id())
            // Search logic
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('institution_name', 'like', '%' . $this->search . '%')
                        ->orWhere('requirements', 'like', '%' . $this->search . '%');
                });
            })
            // Filter logic
            ->when($this->statusFilter, function($q) {
                $q->where('status', $this->statusFilter);
            })
            // Sort logic
            ->orderBy($this->sortBy, $this->sortDir);

        return view('livewire.client.crm-manager', [
            'items' => $query->paginate(9),
            'serviceItems' => ServiceItem::whereIn('target', ['institutions', 'both'])->get()
        ]);
    }
}