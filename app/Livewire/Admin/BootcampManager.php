<?php

namespace App\Livewire\Admin;

use App\Models\Cohort;
use App\Models\Enrollee;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Bootcamp Management - MephEd Admin')]
class BootcampManager extends Component
{
    use WithPagination;

    public $search = '';
    public $status = 'all';
    public $serviceFilter = 'all';
    public $serviceItemFilter = 'all';
    public $perPage = 12;

    public $showCohortForm = false;
    public $showCohortDetail = false;
    public $showEnrolleeForm = false;
    public $showDelete = false;

    public $selectedCohort = null;
    public $selectedEnrollee = null;
    public $editingCohortId = null;
    public $editingEnrolleeId = null;

    public $service_id;
    public $service_item_id;
    public $name;
    public $code;
    public $start_date;
    public $end_date;
    public $mode = 'online';
    public $location;
    public $capacity;
    public $fee;
    public $cohort_status = 'draft';
    public $notes;

    public $cohort_id;
    public $enrollee_name;
    public $enrollee_email;
    public $enrollee_phone;
    public $enrollee_address;
    public $enrollee_status = 'pending';
    public $is_read = false;
    public $enrollee_notes;

    public $bootcampService;
    public $bootcampServices = [];
    public $bootcampItems = [];

    protected $listeners = [
        'open-create-cohort' => 'openCreateCohort'
    ];

    public function mount()
    {
        Gate::authorize('Admin');

        $this->bootcampServices = Service::where('target', 'bootcamp')
            ->orderBy('name')
            ->get();

        if ($this->bootcampServices->isEmpty()) {
            $this->bootcampService = Service::updateOrCreate(
                ['slug' => 'bootcamp'],
                [
                    'name' => 'Bootcamp',
                    'description' => 'Bootcamp training programs',
                    'target' => 'bootcamp',
                    'is_active' => true,
                ]
            );
            $this->bootcampServices = collect([$this->bootcampService]);
        } else {
            $this->bootcampService = $this->bootcampServices->first();
        }

        $this->service_id = $this->bootcampService->id;
        $this->loadBootcampItems();
    }

    public function loadBootcampItems()
    {
        $query = ServiceItem::where('target', 'bootcamp')
            ->whereHas('service', fn ($q) => $q->where('target', 'bootcamp'));

        if ($this->service_id) {
            $query->where('service_id', $this->service_id);
        }

        $this->bootcampItems = $query
            ->orderBy('display_position')
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedServiceItemFilter()
    {
        $this->resetPage();
    }

    public function updatedServiceFilter()
    {
        $this->resetPage();
    }

    public function updatedServiceId()
    {
        $this->service_item_id = null;
        $this->loadBootcampItems();
    }

    public function cohortRules()
    {
        return [
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(fn ($q) => $q->where('target', 'bootcamp')),
            ],
            'service_item_id' => [
                'required',
                Rule::exists('service_items', 'id')->where(function ($q) {
                    $q->where('service_id', $this->service_id)
                        ->where('target', 'bootcamp');
                }),
            ],
            'name' => 'required|string|max:120',
            'code' => ['required', 'string', 'max:40', Rule::unique('cohorts', 'code')->ignore($this->editingCohortId)],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'mode' => 'required|in:online,physical,hybrid',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'fee' => 'nullable|numeric|min:0',
            'cohort_status' => 'required|in:draft,open,running,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function enrolleeRules()
    {
        return [
            'cohort_id' => 'required|exists:cohorts,id',
            'enrollee_name' => 'required|string|max:120',
            'enrollee_email' => 'required|email|max:150',
            'enrollee_phone' => 'required|string|max:30',
            'enrollee_address' => 'required|string|max:255',
            'enrollee_status' => 'required|in:pending,confirmed,active,completed,withdrawn',
            'is_read' => 'boolean',
            'enrollee_notes' => 'nullable|string|max:1000',
        ];
    }

    public function openCreateCohort()
    {
        $this->resetCohortForm();
        $this->showCohortForm = true;
    }

    public function editCohort(int $id)
    {
        $cohort = Cohort::whereHas('service', fn ($q) => $q->where('target', 'bootcamp'))->findOrFail($id);

        $this->editingCohortId = $cohort->id;
        $this->service_id = $cohort->service_id;
        $this->loadBootcampItems();
        $this->service_item_id = $cohort->service_item_id;
        $this->name = $cohort->name;
        $this->code = $cohort->code;
        $this->start_date = optional($cohort->start_date)->format('Y-m-d');
        $this->end_date = optional($cohort->end_date)->format('Y-m-d');
        $this->mode = $cohort->mode;
        $this->location = $cohort->location;
        $this->capacity = $cohort->capacity;
        $this->fee = $cohort->fee;
        $this->cohort_status = $cohort->status;
        $this->notes = $cohort->notes;

        $this->showCohortForm = true;
    }

    public function saveCohort()
    {
        $this->validate($this->cohortRules());

        $payload = [
            'service_id' => $this->service_id,
            'service_item_id' => $this->service_item_id,
            'name' => $this->name,
            'code' => strtoupper($this->code),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date ?: null,
            'mode' => $this->mode,
            'location' => $this->location ?: null,
            'capacity' => $this->capacity ?: null,
            'fee' => $this->fee ?: null,
            'status' => $this->cohort_status,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingCohortId) {
            Cohort::findOrFail($this->editingCohortId)->update($payload);
            session()->flash('success', 'Cohort updated successfully.');
        } else {
            Cohort::create($payload);
            session()->flash('success', 'Cohort created successfully.');
        }

        $this->resetCohortForm();
        $this->showCohortForm = false;
    }

    public function viewCohort(int $id)
    {
        $this->selectedCohort = Cohort::with(['serviceItem', 'enrollees'])
            ->whereHas('service', fn ($q) => $q->where('target', 'bootcamp'))
            ->findOrFail($id);

        $this->showCohortDetail = true;
    }

    public function openDeleteCohort(int $id)
    {
        $this->selectedCohort = Cohort::whereHas('service', fn ($q) => $q->where('target', 'bootcamp'))->findOrFail($id);
        $this->showDelete = true;
    }

    public function deleteCohort()
    {
        if (!$this->selectedCohort) {
            return;
        }

        Cohort::findOrFail($this->selectedCohort->id)->delete();
        $this->showDelete = false;
        $this->showCohortDetail = false;
        $this->selectedCohort = null;
        session()->flash('success', 'Cohort deleted successfully.');
        $this->resetPage();
    }

    public function openCreateEnrollee(int $cohortId)
    {
        Cohort::whereHas('service', fn ($q) => $q->where('target', 'bootcamp'))->findOrFail($cohortId);

        $this->resetEnrolleeForm();
        $this->cohort_id = $cohortId;
        $this->showEnrolleeForm = true;
    }

    public function editEnrollee(int $id)
    {
        $enrollee = Enrollee::with('cohort')->findOrFail($id);
        abort_unless(optional(optional($enrollee->cohort)->service)->target === 'bootcamp', 404);

        $this->editingEnrolleeId = $enrollee->id;
        $this->cohort_id = $enrollee->cohort_id;
        $this->enrollee_name = $enrollee->name;
        $this->enrollee_email = $enrollee->email;
        $this->enrollee_phone = $enrollee->phone;
        $this->enrollee_address = $enrollee->address;
        $this->enrollee_status = $enrollee->status;
        $this->is_read = (bool) $enrollee->is_read;
        $this->enrollee_notes = $enrollee->meta['notes'] ?? null;

        $this->showEnrolleeForm = true;
    }

    public function saveEnrollee()
    {
        $this->validate($this->enrolleeRules());

        $payload = [
            'cohort_id' => $this->cohort_id,
            'name' => $this->enrollee_name,
            'email' => $this->enrollee_email,
            'phone' => $this->enrollee_phone,
            'address' => $this->enrollee_address,
            'status' => $this->enrollee_status,
            'is_read' => $this->is_read,
            'confirmed_at' => $this->enrollee_status === 'confirmed' ? now() : null,
            'meta' => [
                'notes' => $this->enrollee_notes,
            ],
        ];

        if ($this->editingEnrolleeId) {
            Enrollee::findOrFail($this->editingEnrolleeId)->update($payload);
            session()->flash('success', 'Enrollee updated successfully.');
        } else {
            Enrollee::create($payload);
            session()->flash('success', 'Enrollee added successfully.');
        }

        $this->refreshSelectedCohort();
        $this->resetEnrolleeForm();
        $this->showEnrolleeForm = false;
    }

    public function deleteEnrollee(int $id)
    {
        $enrollee = Enrollee::with('cohort.service')->findOrFail($id);
        abort_unless(optional(optional($enrollee->cohort)->service)->target === 'bootcamp', 404);
        $enrollee->delete();

        $this->refreshSelectedCohort();
        session()->flash('success', 'Enrollee removed.');
    }

    public function markAsRead(int $id)
    {
        $enrollee = Enrollee::with('cohort.service')->findOrFail($id);
        abort_unless(optional(optional($enrollee->cohort)->service)->target === 'bootcamp', 404);
        $enrollee->update(['is_read' => true]);

        $this->refreshSelectedCohort();
    }

    public function resetCohortForm()
    {
        $this->editingCohortId = null;
        $this->service_id = $this->bootcampServices->first()?->id;
        $this->service_item_id = null;
        $this->name = null;
        $this->code = null;
        $this->start_date = null;
        $this->end_date = null;
        $this->mode = 'online';
        $this->location = null;
        $this->capacity = null;
        $this->fee = null;
        $this->cohort_status = 'draft';
        $this->notes = null;
    }

    public function resetEnrolleeForm()
    {
        $this->editingEnrolleeId = null;
        $this->cohort_id = null;
        $this->enrollee_name = null;
        $this->enrollee_email = null;
        $this->enrollee_phone = null;
        $this->enrollee_address = null;
        $this->enrollee_status = 'pending';
        $this->is_read = false;
        $this->enrollee_notes = null;
    }

    public function closeDetail()
    {
        $this->showCohortDetail = false;
        $this->selectedCohort = null;
    }

    private function refreshSelectedCohort()
    {
        if (!$this->selectedCohort) {
            return;
        }

        $this->selectedCohort = Cohort::with(['serviceItem', 'enrollees'])->find($this->selectedCohort->id);
    }

    public function render()
    {
        $query = Cohort::with('serviceItem')
            ->withCount('enrollees')
            ->whereHas('service', fn ($q) => $q->where('target', 'bootcamp'));

        if ($this->search) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search)
                    ->orWhereHas('serviceItem', fn ($q2) => $q2->where('name', 'like', $search));
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->serviceItemFilter !== 'all') {
            $query->where('service_item_id', $this->serviceItemFilter);
        }

        if ($this->serviceFilter !== 'all') {
            $query->where('service_id', $this->serviceFilter);
        }

        return view('livewire.admin.bootcamp-manager', [
            'cohorts' => $query->latest()->paginate($this->perPage),
            'serviceItems' => $this->bootcampItems,
            'serviceItemsForFilter' => ServiceItem::where('target', 'bootcamp')
                ->when($this->serviceFilter !== 'all', fn ($q) => $q->where('service_id', $this->serviceFilter))
                ->orderBy('display_position')
                ->orderBy('name')
                ->get(['id', 'name']),
            'services' => $this->bootcampServices,
            'allCohorts' => Cohort::whereHas('service', fn ($q) => $q->where('target', 'bootcamp'))
                ->orderBy('start_date', 'desc')
                ->get(['id', 'name', 'code']),
        ]);
    }
}
