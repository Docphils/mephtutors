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

    protected int $maxFormStep = 4;

    public string $homepage_mode = 'default';
    public bool $programme_trial_class_enabled = true;
    public bool $programme_probationary_classes_allowed = true;
    public string $programme_pricing_note_default = '';
    public string $programme_whatsapp_number = '';

    public string $search = '';
    public bool $showProgrammeForm = false;
    public int $formStep = 1;
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
    public array $frequency_option_rows = [];
    public array $duration_option_rows = [];
    public array $mode_option_rows = [];
    public array $subject_option_rows = [];
    public int $max_selectable_subjects = 6;
    public array $pricing_frequency_rows = [];
    public array $pricing_duration_rows = [];
    public array $pricing_mode_rows = [];
    public string $pricing_additional_subject_fraction = '0.35';
    public string $pricing_base_subject_allowance = '1';
    public string $pricing_location_surcharge = '0';
    public array $faq_rows = [];
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

        $this->resetProgrammeForm();
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

    public function openProgrammeForm(): void
    {
        $this->resetProgrammeForm();
        $this->showProgrammeForm = true;
    }

    public function cancelProgrammeForm(): void
    {
        $this->resetProgrammeForm();
    }

    public function goToFormStep(int $step): void
    {
        $step = max(1, min($this->maxFormStep, $step));
        if ($step > $this->formStep) {
            $this->validateCurrentStep();
        }
        $this->formStep = $step;
    }

    public function nextFormStep(): void
    {
        if ($this->formStep >= $this->maxFormStep) {
            return;
        }

        $this->validateCurrentStep();
        $this->formStep++;
    }

    public function previousFormStep(): void
    {
        if ($this->formStep <= 1) {
            return;
        }

        $this->formStep--;
    }

    public function saveProgramme(): void
    {
        $this->normalizeDynamicRows();
        $this->validate($this->programmeValidationRules());

        $slug = Str::slug($this->slug ?: $this->name);
        $frequencyOptions = $this->extractLabelValues($this->frequency_option_rows);
        $durationOptions = $this->extractLabelValues($this->duration_option_rows);
        $modeOptions = $this->extractLabelValues($this->mode_option_rows);
        $subjectOptions = $this->extractLabelValues($this->subject_option_rows);

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
            'frequency_options' => $frequencyOptions,
            'duration_options' => $durationOptions,
            'mode_options' => $modeOptions,
            'subject_options' => $subjectOptions,
            'max_selectable_subjects' => $this->max_selectable_subjects,
            'pricing_matrix' => $this->buildPricingMatrix(),
            'faq_items' => $this->extractFaqItems(),
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
        $this->frequency_option_rows = $this->makeLabelRows($programme->frequency_options ?: []);
        $this->duration_option_rows = $this->makeLabelRows($programme->duration_options ?: []);
        $this->mode_option_rows = $this->makeLabelRows($programme->mode_options ?: []);
        $this->subject_option_rows = $this->makeLabelRows($programme->subject_options ?: []);
        $this->max_selectable_subjects = (int) ($programme->max_selectable_subjects ?: 6);
        $matrix = (array) ($programme->pricing_matrix ?: []);
        $this->pricing_frequency_rows = $this->makePairRows($matrix['frequency_prices'] ?? [], 'frequency', 'amount');
        $this->pricing_duration_rows = $this->makePairRows($matrix['duration_multipliers'] ?? [], 'duration', 'multiplier');
        $this->pricing_mode_rows = $this->makePairRows($matrix['mode_multipliers'] ?? [], 'mode', 'multiplier');
        $this->pricing_additional_subject_fraction = (string) ((float) ($matrix['additional_subject_fraction'] ?? 0.35));
        $this->pricing_base_subject_allowance = (string) ((int) ($matrix['base_subject_allowance'] ?? 1));
        $this->pricing_location_surcharge = (string) ((float) ($matrix['location_surcharge'] ?? 0));
        $this->faq_rows = collect($programme->faq_items ?: [])
            ->map(fn ($faq) => [
                'q' => trim((string) ($faq['q'] ?? '')),
                'a' => trim((string) ($faq['a'] ?? '')),
            ])
            ->filter(fn ($faq) => $faq['q'] !== '' || $faq['a'] !== '')
            ->values()
            ->all();
        if ($this->faq_rows === []) {
            $this->faq_rows = [['q' => '', 'a' => '']];
        }
        $this->sort_order = (int) $programme->sort_order;
        $this->is_active = (bool) $programme->is_active;
        $this->meta_title = (string) $programme->meta_title;
        $this->meta_description = (string) $programme->meta_description;
        $this->og_image = (string) $programme->og_image;
        $this->hero_image = (string) $programme->hero_image;
        $this->showProgrammeForm = true;
        $this->formStep = 1;
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
        $this->frequency_option_rows = [['label' => '']];
        $this->duration_option_rows = [['label' => '']];
        $this->mode_option_rows = [
            ['label' => 'online'],
            ['label' => 'home'],
        ];
        $this->subject_option_rows = [['label' => '']];
        $this->max_selectable_subjects = 6;
        $this->pricing_frequency_rows = [['frequency' => '', 'amount' => '']];
        $this->pricing_duration_rows = [['duration' => '', 'multiplier' => '1']];
        $this->pricing_mode_rows = [
            ['mode' => 'online', 'multiplier' => '1'],
            ['mode' => 'home', 'multiplier' => '1.2'],
        ];
        $this->pricing_additional_subject_fraction = '0.35';
        $this->pricing_base_subject_allowance = '1';
        $this->pricing_location_surcharge = '0';
        $this->faq_rows = [['q' => '', 'a' => '']];
        $this->sort_order = 0;
        $this->is_active = true;
        $this->meta_title = '';
        $this->meta_description = '';
        $this->og_image = '';
        $this->hero_image = '';
        $this->formStep = 1;
        $this->showProgrammeForm = false;
    }

    public function addFrequencyOptionRow(): void
    {
        $this->frequency_option_rows[] = ['label' => ''];
    }

    public function removeFrequencyOptionRow(int $index): void
    {
        $this->removeRowByIndex($this->frequency_option_rows, $index, ['label' => '']);
    }

    public function addDurationOptionRow(): void
    {
        $this->duration_option_rows[] = ['label' => ''];
    }

    public function removeDurationOptionRow(int $index): void
    {
        $this->removeRowByIndex($this->duration_option_rows, $index, ['label' => '']);
    }

    public function addModeOptionRow(): void
    {
        $this->mode_option_rows[] = ['label' => ''];
    }

    public function removeModeOptionRow(int $index): void
    {
        $this->removeRowByIndex($this->mode_option_rows, $index, ['label' => '']);
    }

    public function addSubjectOptionRow(): void
    {
        $this->subject_option_rows[] = ['label' => ''];
    }

    public function removeSubjectOptionRow(int $index): void
    {
        $this->removeRowByIndex($this->subject_option_rows, $index, ['label' => '']);
    }

    public function addFrequencyPricingRow(): void
    {
        $this->pricing_frequency_rows[] = ['frequency' => '', 'amount' => ''];
    }

    public function removeFrequencyPricingRow(int $index): void
    {
        $this->removeRowByIndex($this->pricing_frequency_rows, $index, ['frequency' => '', 'amount' => '']);
    }

    public function addDurationPricingRow(): void
    {
        $this->pricing_duration_rows[] = ['duration' => '', 'multiplier' => '1'];
    }

    public function removeDurationPricingRow(int $index): void
    {
        $this->removeRowByIndex($this->pricing_duration_rows, $index, ['duration' => '', 'multiplier' => '1']);
    }

    public function addModePricingRow(): void
    {
        $this->pricing_mode_rows[] = ['mode' => '', 'multiplier' => '1'];
    }

    public function removeModePricingRow(int $index): void
    {
        $this->removeRowByIndex($this->pricing_mode_rows, $index, ['mode' => '', 'multiplier' => '1']);
    }

    public function addFaqRow(): void
    {
        $this->faq_rows[] = ['q' => '', 'a' => ''];
    }

    public function removeFaqRow(int $index): void
    {
        $this->removeRowByIndex($this->faq_rows, $index, ['q' => '', 'a' => '']);
    }

    protected function validateCurrentStep(): void
    {
        $this->normalizeDynamicRows();

        if ($this->formStep === 1) {
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
            ]);
            return;
        }

        if ($this->formStep === 2) {
            $this->validate([
                'frequency_option_rows' => 'required|array|min:1',
                'frequency_option_rows.*.label' => 'required|string|max:120|distinct',
                'duration_option_rows' => 'required|array|min:1',
                'duration_option_rows.*.label' => 'required|string|max:120|distinct',
                'mode_option_rows' => 'required|array|min:1',
                'mode_option_rows.*.label' => 'required|string|max:120|distinct',
                'subject_option_rows' => 'required|array|min:1',
                'subject_option_rows.*.label' => 'required|string|max:120|distinct',
                'max_selectable_subjects' => 'required|integer|min:1|max:20',
            ]);
            return;
        }

        if ($this->formStep === 3) {
            $this->validate([
                'pricing_frequency_rows' => 'required|array|min:1',
                'pricing_frequency_rows.*.frequency' => 'required|string|max:120|distinct',
                'pricing_frequency_rows.*.amount' => 'required|numeric|min:0',
                'pricing_duration_rows' => 'required|array|min:1',
                'pricing_duration_rows.*.duration' => 'required|string|max:120|distinct',
                'pricing_duration_rows.*.multiplier' => 'required|numeric|min:0',
                'pricing_mode_rows' => 'required|array|min:1',
                'pricing_mode_rows.*.mode' => 'required|string|max:120|distinct',
                'pricing_mode_rows.*.multiplier' => 'required|numeric|min:0',
                'pricing_additional_subject_fraction' => 'required|numeric|min:0|max:5',
                'pricing_base_subject_allowance' => 'required|integer|min:1|max:10',
                'pricing_location_surcharge' => 'required|numeric|min:0',
            ]);
        }
    }

    protected function programmeValidationRules(): array
    {
        return [
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
            'frequency_option_rows' => 'required|array|min:1',
            'frequency_option_rows.*.label' => 'required|string|max:120|distinct',
            'duration_option_rows' => 'required|array|min:1',
            'duration_option_rows.*.label' => 'required|string|max:120|distinct',
            'mode_option_rows' => 'required|array|min:1',
            'mode_option_rows.*.label' => 'required|string|max:120|distinct',
            'subject_option_rows' => 'required|array|min:1',
            'subject_option_rows.*.label' => 'required|string|max:120|distinct',
            'max_selectable_subjects' => 'required|integer|min:1|max:20',
            'pricing_frequency_rows' => 'required|array|min:1',
            'pricing_frequency_rows.*.frequency' => 'required|string|max:120|distinct',
            'pricing_frequency_rows.*.amount' => 'required|numeric|min:0',
            'pricing_duration_rows' => 'required|array|min:1',
            'pricing_duration_rows.*.duration' => 'required|string|max:120|distinct',
            'pricing_duration_rows.*.multiplier' => 'required|numeric|min:0',
            'pricing_mode_rows' => 'required|array|min:1',
            'pricing_mode_rows.*.mode' => 'required|string|max:120|distinct',
            'pricing_mode_rows.*.multiplier' => 'required|numeric|min:0',
            'pricing_additional_subject_fraction' => 'required|numeric|min:0|max:5',
            'pricing_base_subject_allowance' => 'required|integer|min:1|max:10',
            'pricing_location_surcharge' => 'required|numeric|min:0',
            'faq_rows' => 'nullable|array',
            'faq_rows.*.q' => 'nullable|string|max:300',
            'faq_rows.*.a' => 'nullable|string|max:1200',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    protected function normalizeDynamicRows(): void
    {
        $this->frequency_option_rows = $this->normalizeLabelRows($this->frequency_option_rows);
        $this->duration_option_rows = $this->normalizeLabelRows($this->duration_option_rows);
        $this->mode_option_rows = $this->normalizeLabelRows($this->mode_option_rows);
        $this->subject_option_rows = $this->normalizeLabelRows($this->subject_option_rows);
        $this->pricing_frequency_rows = $this->normalizePairRows($this->pricing_frequency_rows, 'frequency', 'amount');
        $this->pricing_duration_rows = $this->normalizePairRows($this->pricing_duration_rows, 'duration', 'multiplier');
        $this->pricing_mode_rows = $this->normalizePairRows($this->pricing_mode_rows, 'mode', 'multiplier');
        $this->faq_rows = $this->normalizeFaqRows($this->faq_rows);
    }

    protected function normalizeLabelRows(array $rows): array
    {
        $normalized = collect($rows)
            ->map(fn ($row) => ['label' => trim((string) ($row['label'] ?? ''))])
            ->filter(fn ($row) => $row['label'] !== '')
            ->values()
            ->all();

        return $normalized === [] ? [['label' => '']] : $normalized;
    }

    protected function normalizePairRows(array $rows, string $keyField, string $valueField): array
    {
        $normalized = collect($rows)
            ->map(function ($row) use ($keyField, $valueField) {
                return [
                    $keyField => trim((string) ($row[$keyField] ?? '')),
                    $valueField => trim((string) ($row[$valueField] ?? '')),
                ];
            })
            ->filter(function ($row) use ($keyField, $valueField) {
                return $row[$keyField] !== '' || $row[$valueField] !== '';
            })
            ->values()
            ->all();

        return $normalized === [] ? [[$keyField => '', $valueField => '']] : $normalized;
    }

    protected function normalizeFaqRows(array $rows): array
    {
        $normalized = collect($rows)
            ->map(fn ($row) => [
                'q' => trim((string) ($row['q'] ?? '')),
                'a' => trim((string) ($row['a'] ?? '')),
            ])
            ->filter(fn ($row) => $row['q'] !== '' || $row['a'] !== '')
            ->values()
            ->all();

        return $normalized === [] ? [['q' => '', 'a' => '']] : $normalized;
    }

    protected function extractLabelValues(array $rows): array
    {
        return collect($rows)
            ->map(fn ($row) => trim((string) ($row['label'] ?? '')))
            ->filter(fn ($label) => $label !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected function extractFaqItems(): array
    {
        return collect($this->faq_rows)
            ->map(fn ($row) => [
                'q' => trim((string) ($row['q'] ?? '')),
                'a' => trim((string) ($row['a'] ?? '')),
            ])
            ->filter(fn ($row) => $row['q'] !== '' && $row['a'] !== '')
            ->values()
            ->all();
    }

    protected function buildPricingMatrix(): array
    {
        $frequencyPrices = [];
        foreach ($this->pricing_frequency_rows as $row) {
            $frequency = trim((string) ($row['frequency'] ?? ''));
            $amount = $row['amount'] ?? null;
            if ($frequency === '' || !is_numeric($amount)) {
                continue;
            }
            $frequencyPrices[$frequency] = (float) $amount;
        }

        $durationMultipliers = [];
        foreach ($this->pricing_duration_rows as $row) {
            $duration = trim((string) ($row['duration'] ?? ''));
            $multiplier = $row['multiplier'] ?? null;
            if ($duration === '' || !is_numeric($multiplier)) {
                continue;
            }
            $durationMultipliers[$duration] = (float) $multiplier;
        }

        $modeMultipliers = [];
        foreach ($this->pricing_mode_rows as $row) {
            $mode = trim((string) ($row['mode'] ?? ''));
            $multiplier = $row['multiplier'] ?? null;
            if ($mode === '' || !is_numeric($multiplier)) {
                continue;
            }
            $modeMultipliers[$mode] = (float) $multiplier;
        }

        return [
            'frequency_prices' => $frequencyPrices,
            'duration_multipliers' => $durationMultipliers,
            'mode_multipliers' => $modeMultipliers,
            'additional_subject_fraction' => (float) $this->pricing_additional_subject_fraction,
            'base_subject_allowance' => (int) $this->pricing_base_subject_allowance,
            'location_surcharge' => (float) $this->pricing_location_surcharge,
        ];
    }

    protected function removeRowByIndex(array &$rows, int $index, array $fallbackRow): void
    {
        if (!array_key_exists($index, $rows)) {
            return;
        }

        unset($rows[$index]);
        $rows = array_values($rows);
        if ($rows === []) {
            $rows = [$fallbackRow];
        }
    }

    protected function makeLabelRows(array $values): array
    {
        $rows = collect($values)
            ->map(fn ($value) => ['label' => trim((string) $value)])
            ->filter(fn ($row) => $row['label'] !== '')
            ->values()
            ->all();

        return $rows === [] ? [['label' => '']] : $rows;
    }

    protected function makePairRows(array $map, string $keyName, string $valueName): array
    {
        $rows = collect($map)
            ->map(fn ($value, $key) => [
                $keyName => (string) $key,
                $valueName => (string) $value,
            ])
            ->filter(fn ($row) => trim((string) $row[$keyName]) !== '')
            ->values()
            ->all();

        return $rows === [] ? [[$keyName => '', $valueName => '']] : $rows;
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
