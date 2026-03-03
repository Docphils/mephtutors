<?php

namespace App\Livewire\Admin;

use App\Models\ExamType;
use App\Models\Level;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Service Catalog Management - MephEd Admin')]
class ServiceCatalogManager extends Component
{
    use WithPagination, WithFileUploads;

    public $serviceSearch = '';
    public $itemSearch = '';
    public $examSearch = '';
    public $levelSearch = '';

    public $currentTab = 'services';

    public $serviceEditingId = null;
    public $service_name;
    public $service_slug;
    public $service_description;
    public $service_target = 'tutor_request';
    public $service_is_active = true;

    public $itemEditingId = null;
    public $item_service_id;
    public $item_name;
    public $item_slug;
    public $item_description;
    public $item_target = 'tutor_request';
    public $item_shown_on_welcome = true;
    public $item_display_position = 0;
    public $item_has_subjects = true;
    public $item_requires_curriculum = false;
    public $item_requires_level = true;
    public $item_requires_exam_type = false;
    public $item_is_active = true;
    public $item_image;
    public $item_existing_image_path;

    public $examEditingId = null;
    public $exam_name;
    public $exam_slug;

    public $levelEditingId = null;
    public $level_name;
    public $level_order;

    public function mount()
    {
        Gate::authorize('Admin');
    }

    public function setTab($tab)
    {
        $this->currentTab = $tab;
        // Optional: Reset pagination when switching tabs
        $this->resetPage('servicesPage');
        $this->resetPage('itemsPage');
        $this->resetPage('examsPage');
        $this->resetPage('levelsPage');
    }

    public function updatedServiceSearch()
    {
        $this->resetPage('servicesPage');
    }

    public function updatedItemSearch()
    {
        $this->resetPage('itemsPage');
    }

    public function updatedExamSearch()
    {
        $this->resetPage('examsPage');
    }

    public function updatedLevelSearch()
    {
        $this->resetPage('levelsPage');
    }

    public function saveService()
    {
        $this->validate([
            'service_name' => 'required|string|max:120',
            'service_slug' => ['nullable', 'string', 'max:140', Rule::unique('services', 'slug')->ignore($this->serviceEditingId)],
            'service_description' => 'nullable|string',
            'service_target' => 'required|in:tutor_request,institutions,bootcamp',
            'service_is_active' => 'boolean',
        ]);

        $slug = Str::slug($this->service_slug ?: $this->service_name);

        $payload = [
            'name' => $this->service_name,
            'slug' => $slug,
            'description' => $this->service_description ?: null,
            'target' => $this->service_target,
            'is_active' => (bool) $this->service_is_active,
        ];

        if ($this->serviceEditingId) {
            Service::findOrFail($this->serviceEditingId)->update($payload);
            session()->flash('success', 'Service updated.');
        } else {
            Service::create($payload);
            session()->flash('success', 'Service created.');
        }

        $this->resetServiceForm();
    }

    public function editService(int $id)
    {
        $service = Service::findOrFail($id);
        $this->serviceEditingId = $service->id;
        $this->service_name = $service->name;
        $this->service_slug = $service->slug;
        $this->service_description = $service->description;
        $this->service_target = $service->target;
        $this->service_is_active = (bool) $service->is_active;
    }

    public function deleteService(int $id)
    {
        Service::findOrFail($id)->delete();
        session()->flash('success', 'Service deleted.');
    }

    public function resetServiceForm()
    {
        $this->serviceEditingId = null;
        $this->service_name = null;
        $this->service_slug = null;
        $this->service_description = null;
        $this->service_target = 'tutor_request';
        $this->service_is_active = true;
    }

    public function saveServiceItem()
    {
        $this->validate([
            'item_service_id' => 'required|exists:services,id',
            'item_name' => 'required|string|max:120',
            'item_slug' => ['nullable', 'string', 'max:140', Rule::unique('service_items', 'slug')->ignore($this->itemEditingId)],
            'item_description' => 'nullable|string',
            'item_target' => 'required|in:tutor_request,institutions,bootcamp',
            'item_shown_on_welcome' => 'boolean',
            'item_display_position' => 'nullable|integer|min:0',
            'item_has_subjects' => 'boolean',
            'item_requires_curriculum' => 'boolean',
            'item_requires_level' => 'boolean',
            'item_requires_exam_type' => 'boolean',
            'item_is_active' => 'boolean',
            'item_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $slug = Str::slug($this->item_slug ?: $this->item_name);
        $position = (int) ($this->item_display_position ?? 0);

        if ($position > 0) {
            $positionTaken = ServiceItem::where('display_position', $position)
                ->when($this->itemEditingId, fn ($q) => $q->where('id', '!=', $this->itemEditingId))
                ->exists();

            if ($positionTaken) {
                $this->addError('item_display_position', 'This display position is already taken. Choose another one.');
                return;
            }
        }

        $imagePath = $this->item_existing_image_path;
        if ($this->item_image) {
            $imagePath = $this->item_image->store('service-items', 'public');
        }

        $payload = [
            'service_id' => $this->item_service_id,
            'name' => $this->item_name,
            'slug' => $slug,
            'description' => $this->item_description ?: null,
            'image_path' => $imagePath,
            'target' => $this->item_target,
            'shown_on_welcome' => (bool) $this->item_shown_on_welcome,
            'display_position' => $position,
            'has_subjects' => (bool) $this->item_has_subjects,
            'requires_curriculum' => (bool) $this->item_requires_curriculum,
            'requires_level' => (bool) $this->item_requires_level,
            'requires_exam_type' => (bool) $this->item_requires_exam_type,
            'is_active' => (bool) $this->item_is_active,
        ];

        if ($this->itemEditingId) {
            ServiceItem::findOrFail($this->itemEditingId)->update($payload);
            session()->flash('success', 'Service item updated.');
        } else {
            ServiceItem::create($payload);
            session()->flash('success', 'Service item created.');
        }

        $this->resetServiceItemForm();
    }

    public function editServiceItem(int $id)
    {
        $item = ServiceItem::findOrFail($id);
        $this->itemEditingId = $item->id;
        $this->item_service_id = $item->service_id;
        $this->item_name = $item->name;
        $this->item_slug = $item->slug;
        $this->item_description = $item->description;
        $this->item_existing_image_path = $item->image_path;
        $this->item_target = $item->target;
        $this->item_shown_on_welcome = (bool) $item->shown_on_welcome;
        $this->item_display_position = $item->display_position;
        $this->item_has_subjects = (bool) $item->has_subjects;
        $this->item_requires_curriculum = (bool) $item->requires_curriculum;
        $this->item_requires_level = (bool) $item->requires_level;
        $this->item_requires_exam_type = (bool) $item->requires_exam_type;
        $this->item_is_active = (bool) $item->is_active;
    }

    public function deleteServiceItem(int $id)
    {
        ServiceItem::findOrFail($id)->delete();
        session()->flash('success', 'Service item deleted.');
    }

    public function resetServiceItemForm()
    {
        $this->itemEditingId = null;
        $this->item_service_id = null;
        $this->item_name = null;
        $this->item_slug = null;
        $this->item_description = null;
        $this->item_target = 'tutor_request';
        $this->item_shown_on_welcome = true;
        $this->item_display_position = 0;
        $this->item_image = null;
        $this->item_existing_image_path = null;
        $this->item_has_subjects = true;
        $this->item_requires_curriculum = false;
        $this->item_requires_level = true;
        $this->item_requires_exam_type = false;
        $this->item_is_active = true;
    }

    public function saveExamType()
    {
        $this->validate([
            'exam_name' => 'required|string|max:120',
            'exam_slug' => ['nullable', 'string', 'max:140', Rule::unique('exam_types', 'slug')->ignore($this->examEditingId)],
        ]);

        $payload = [
            'name' => $this->exam_name,
            'slug' => Str::slug($this->exam_slug ?: $this->exam_name),
        ];

        if ($this->examEditingId) {
            ExamType::findOrFail($this->examEditingId)->update($payload);
            session()->flash('success', 'Exam type updated.');
        } else {
            ExamType::create($payload);
            session()->flash('success', 'Exam type created.');
        }

        $this->resetExamForm();
    }

    public function editExamType(int $id)
    {
        $exam = ExamType::findOrFail($id);
        $this->examEditingId = $exam->id;
        $this->exam_name = $exam->name;
        $this->exam_slug = $exam->slug;
    }

    public function deleteExamType(int $id)
    {
        ExamType::findOrFail($id)->delete();
        session()->flash('success', 'Exam type deleted.');
    }

    public function resetExamForm()
    {
        $this->examEditingId = null;
        $this->exam_name = null;
        $this->exam_slug = null;
    }

    public function saveLevel()
    {
        $this->validate([
            'level_name' => 'required|string|max:120',
            'level_order' => 'nullable|integer|min:0',
        ]);

        $payload = [
            'name' => $this->level_name,
            'order' => $this->level_order,
        ];

        if ($this->levelEditingId) {
            Level::findOrFail($this->levelEditingId)->update($payload);
            session()->flash('success', 'Level updated.');
        } else {
            Level::create($payload);
            session()->flash('success', 'Level created.');
        }

        $this->resetLevelForm();
    }

    public function editLevel(int $id)
    {
        $level = Level::findOrFail($id);
        $this->levelEditingId = $level->id;
        $this->level_name = $level->name;
        $this->level_order = $level->order;
    }

    public function deleteLevel(int $id)
    {
        Level::findOrFail($id)->delete();
        session()->flash('success', 'Level deleted.');
    }

    public function resetLevelForm()
    {
        $this->levelEditingId = null;
        $this->level_name = null;
        $this->level_order = null;
    }

    public function render()
    {
        $services = Service::withCount('serviceItems')
            ->when($this->serviceSearch, fn ($q) => $q->where('name', 'like', '%' . $this->serviceSearch . '%')
                ->orWhere('slug', 'like', '%' . $this->serviceSearch . '%'))
            ->latest()
            ->paginate(8, ['*'], 'servicesPage');

        $serviceItems = ServiceItem::with('service')
            ->when($this->itemSearch, fn ($q) => $q->where('name', 'like', '%' . $this->itemSearch . '%')
                ->orWhere('slug', 'like', '%' . $this->itemSearch . '%'))
            ->latest()
            ->paginate(8, ['*'], 'itemsPage');

        $examTypes = ExamType::query()
            ->when($this->examSearch, fn ($q) => $q->where('name', 'like', '%' . $this->examSearch . '%')
                ->orWhere('slug', 'like', '%' . $this->examSearch . '%'))
            ->latest()
            ->paginate(8, ['*'], 'examsPage');

        $levels = Level::query()
            ->when($this->levelSearch, fn ($q) => $q->where('name', 'like', '%' . $this->levelSearch . '%'))
            ->orderByRaw('`order` is null, `order` asc')
            ->paginate(8, ['*'], 'levelsPage');

        return view('livewire.admin.service-catalog-manager', [
            'services' => $services,
            'serviceItems' => $serviceItems,
            'examTypes' => $examTypes,
            'levels' => $levels,
            'serviceOptions' => Service::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
