<?php

namespace App\Livewire\Admin;

use App\Models\AcademicProgramme;
use App\Models\ServiceItem;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Intervention Settings - MephEd Admin')]
class ProgrammeSettingsManager extends Component
{
    use WithPagination;

    public string $homepage_mode = 'default';
    public bool $programme_trial_class_enabled = true;
    public bool $programme_probationary_classes_allowed = true;
    public string $programme_pricing_note_default = '';
    public string $programme_whatsapp_number = '';

    public string $search = '';
    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public string $tagline = '';
    public string $summary = '';
    public string $overview = '';
    public string $who_it_is_for = '';
    public string $what_parents_can_expect = '';
    public string $starting_from_text = '';
    public string $pricing_note = '';
    public string $renewability_note = '';
    public string $frequency_options = '';
    public string $duration_options = '';
    public string $mode_options = '';
    public string $subject_options = '';
    public int $max_selectable_subjects = 6;
    public string $pricing_matrix = '';
    public string $faq_items = '';
    public int $sort_order = 0;
    public bool $is_active = true;
    public string $meta_title = '';
    public string $meta_description = '';
    public string $og_image = '';
    public string $hero_image = '';
    public ?int $service_item_id = null;

    public function mount(): void
    {
        Gate::authorize('Admin');

        $this->homepage_mode = (string) SiteSetting::getValue('homepage_mode', 'default');
        $this->programme_trial_class_enabled = SiteSetting::getBoolean('programme_trial_class_enabled', true);
        $this->programme_probationary_classes_allowed = SiteSetting::getBoolean('programme_probationary_classes_allowed', true);
        $this->programme_pricing_note_default = (string) SiteSetting::getValue(
            'programme_pricing_note_default',
            'Pricing depends on frequency, session duration, mode, and location for physical lessons.'
        );
        $this->programme_whatsapp_number = (string) SiteSetting::getValue('programme_whatsapp_number', '2348000000000');
    }

    public function saveSiteSettings(): void
    {
        $this->validate([
            'homepage_mode' => 'required|in:default,academic',
            'programme_trial_class_enabled' => 'boolean',
            'programme_probationary_classes_allowed' => 'boolean',
            'programme_pricing_note_default' => 'nullable|string|max:1200',
            'programme_whatsapp_number' => 'nullable|string|max:25',
        ]);

        SiteSetting::setValue('homepage_mode', $this->homepage_mode);
        SiteSetting::setValue('programme_trial_class_enabled', $this->programme_trial_class_enabled);
        SiteSetting::setValue('programme_probationary_classes_allowed', $this->programme_probationary_classes_allowed);
        SiteSetting::setValue('programme_pricing_note_default', $this->programme_pricing_note_default);
        SiteSetting::setValue('programme_whatsapp_number', preg_replace('/\D+/', '', $this->programme_whatsapp_number));

        session()->flash('success', 'Intervention and homepage settings updated.');
    }

    public function saveProgramme(): void
    {
        $this->validate([
            'name' => 'required|string|max:140',
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('academic_programmes', 'slug')->ignore($this->editingId)],
            'tagline' => 'required|string|max:300',
            'summary' => 'required|string|max:500',
            'overview' => 'required|string|max:2500',
            'who_it_is_for' => 'required|string|max:1200',
            'what_parents_can_expect' => 'required|string|max:1200',
            'starting_from_text' => 'nullable|string|max:140',
            'pricing_note' => 'nullable|string|max:1200',
            'renewability_note' => 'nullable|string|max:240',
            'service_item_id' => 'nullable|exists:service_items,id',
            'meta_title' => 'nullable|string|max:170',
            'meta_description' => 'nullable|string|max:300',
            'og_image' => 'nullable|string|max:255',
            'hero_image' => 'nullable|string|max:255',
            'subject_options' => 'required|string|min:5',
            'max_selectable_subjects' => 'required|integer|min:1|max:20',
            'pricing_matrix' => 'required|string|min:5',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($this->slug ?: $this->name);
        $payload = [
            'name' => $this->name,
            'slug' => $slug,
            'tagline' => $this->tagline,
            'summary' => $this->summary,
            'overview' => $this->overview,
            'who_it_is_for' => $this->who_it_is_for,
            'what_parents_can_expect' => $this->what_parents_can_expect,
            'starting_from_text' => $this->starting_from_text ?: null,
            'pricing_note' => $this->pricing_note ?: null,
            'renewability_note' => $this->renewability_note ?: null,
            'service_item_id' => $this->service_item_id ?: null,
            'frequency_options' => $this->splitCommaValues($this->frequency_options),
            'duration_options' => $this->splitCommaValues($this->duration_options),
            'mode_options' => $this->splitCommaValues($this->mode_options),
            'subject_options' => $this->splitCommaValues($this->subject_options),
            'max_selectable_subjects' => $this->max_selectable_subjects,
            'pricing_matrix' => $this->parseJsonObject($this->pricing_matrix),
            'faq_items' => $this->parseFaq($this->faq_items),
            'sort_order' => $this->sort_order ?: 0,
            'is_active' => $this->is_active,
            'meta_title' => $this->meta_title ?: null,
            'meta_description' => $this->meta_description ?: null,
            'og_image' => $this->og_image ?: null,
            'hero_image' => $this->hero_image ?: null,
        ];

        if ($this->editingId) {
            AcademicProgramme::query()->findOrFail($this->editingId)->update($payload);
            session()->flash('success', 'Intervention updated.');
        } else {
            AcademicProgramme::query()->create($payload);
            session()->flash('success', 'Intervention created.');
        }

        $this->resetProgrammeForm();
    }

    public function editProgramme(int $id): void
    {
        $programme = AcademicProgramme::query()->findOrFail($id);

        $this->editingId = $programme->id;
        $this->name = $programme->name;
        $this->slug = $programme->slug;
        $this->tagline = (string) $programme->tagline;
        $this->summary = (string) $programme->summary;
        $this->overview = (string) $programme->overview;
        $this->who_it_is_for = (string) $programme->who_it_is_for;
        $this->what_parents_can_expect = (string) $programme->what_parents_can_expect;
        $this->starting_from_text = (string) $programme->starting_from_text;
        $this->pricing_note = (string) $programme->pricing_note;
        $this->renewability_note = (string) $programme->renewability_note;
        $this->service_item_id = $programme->service_item_id;
        $this->frequency_options = implode(', ', $programme->frequency_options ?: []);
        $this->duration_options = implode(', ', $programme->duration_options ?: []);
        $this->mode_options = implode(', ', $programme->mode_options ?: []);
        $this->subject_options = implode(', ', $programme->subject_options ?: []);
        $this->max_selectable_subjects = (int) ($programme->max_selectable_subjects ?: 6);
        $this->pricing_matrix = json_encode($programme->pricing_matrix ?: [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->faq_items = collect($programme->faq_items ?: [])
            ->map(fn ($faq) => trim(($faq['q'] ?? '') . ' | ' . ($faq['a'] ?? '')))
            ->implode(PHP_EOL);
        $this->sort_order = (int) $programme->sort_order;
        $this->is_active = (bool) $programme->is_active;
        $this->meta_title = (string) $programme->meta_title;
        $this->meta_description = (string) $programme->meta_description;
        $this->og_image = (string) $programme->og_image;
        $this->hero_image = (string) $programme->hero_image;
    }

    public function deleteProgramme(int $id): void
    {
        AcademicProgramme::query()->findOrFail($id)->delete();
        session()->flash('success', 'Intervention deleted.');
    }

    public function resetProgrammeForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->tagline = '';
        $this->summary = '';
        $this->overview = '';
        $this->who_it_is_for = '';
        $this->what_parents_can_expect = '';
        $this->starting_from_text = '';
        $this->pricing_note = '';
        $this->renewability_note = '';
        $this->service_item_id = null;
        $this->frequency_options = '';
        $this->duration_options = '';
        $this->mode_options = '';
        $this->subject_options = '';
        $this->max_selectable_subjects = 6;
        $this->pricing_matrix = '';
        $this->faq_items = '';
        $this->sort_order = 0;
        $this->is_active = true;
        $this->meta_title = '';
        $this->meta_description = '';
        $this->og_image = '';
        $this->hero_image = '';
    }

    protected function splitCommaValues(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($entry) => trim($entry))
            ->filter()
            ->values()
            ->all();
    }

    protected function parseFaq(string $value): array
    {
        return collect(explode(PHP_EOL, $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->map(function ($line) {
                $parts = explode('|', $line, 2);
                return [
                    'q' => trim($parts[0] ?? ''),
                    'a' => trim($parts[1] ?? ''),
                ];
            })
            ->filter(fn ($faq) => $faq['q'] !== '' && $faq['a'] !== '')
            ->values()
            ->all();
    }

    protected function parseJsonObject(string $value): array
    {
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'pricing_matrix' => 'Pricing matrix must be valid JSON object.',
            ]);
        }

        return $decoded;
    }

    public function render()
    {
        return view('livewire.admin.programme-settings-manager', [
            'programmes' => AcademicProgramme::query()
                ->when($this->search, fn ($query) => $query
                    ->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%'))
                ->orderBy('sort_order')
                ->paginate(10),
            'serviceItems' => ServiceItem::query()
                ->where('target', 'tutor_request')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
