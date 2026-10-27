<?php

namespace App\Livewire\Pages;

use App\Models\ChurchLead;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class ChurchLeadForm extends Component
{
    public string $church_name = '';
    public string $contact_name = '';
    public string $contact_role = '';
    public string $email = '';
    public string $phone = '';
    public string $country_region = '';
    public string $congregation_size = '';
    public string $current_records_method = '';
    public array $interests = [];
    public string $timeframe = '';
    public string $preferred_contact_method = '';
    public string $source = '';
    public string $enquiry = '';
    public bool $contact_consent = false;
    public bool $marketing_consent = false;
    public string $website = '';
    public bool $submitted = false;

    private const INTERESTS = ['Members and households', 'Attendance and services', 'Units or groups', 'Care and follow-up', 'Finance', 'Communications', 'Other'];

    protected function rules(): array
    {
        return [
            'church_name' => ['required', 'string', 'max:160'], 'contact_name' => ['required', 'string', 'max:120'],
            'contact_role' => ['required', 'string', 'max:120'], 'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'], 'country_region' => ['required', 'string', 'max:120'],
            'congregation_size' => ['nullable', Rule::in(['Under 100', '100–500', '500–2,000', 'More than 2,000', 'Prefer not to say'])],
            'current_records_method' => ['nullable', Rule::in(['Paper', 'Spreadsheets', 'Another system', 'Other', 'Prefer not to say'])],
            'interests' => ['array', 'max:7'], 'interests.*' => [Rule::in(self::INTERESTS)],
            'timeframe' => ['nullable', Rule::in(['Exploring', 'Within 3 months', 'Within 6 months', 'Later'])],
            'preferred_contact_method' => ['nullable', Rule::in(['Email', 'Phone'])],
            'source' => ['nullable', 'string', 'max:160'], 'enquiry' => ['nullable', 'string', 'max:2000'],
            'contact_consent' => ['accepted'], 'marketing_consent' => ['boolean'], 'website' => ['max:0'],
        ];
    }

    public function submit(): void
    {
        $key = 'church-lead:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('form', 'Please wait before submitting another enquiry.');
            return;
        }
        RateLimiter::hit($key, 3600);

        $data = $this->validate();
        if ($this->website !== '') return;
        unset($data['website']);
        $data['contact_consent'] = true;
        $data['contact_consent_at'] = now();
        $data['marketing_consent'] = (bool) $data['marketing_consent'];
        $data['marketing_consent_at'] = $data['marketing_consent'] ? now() : null;
        $lead = ChurchLead::create($data);
        $lead->log('created', 'Enquiry submitted through the public form.', [
            'possible_duplicates' => ChurchLead::query()->whereKeyNot($lead->id)->where(function ($query) use ($lead) {
                $query->whereRaw('LOWER(email) = ?', [mb_strtolower($lead->email)]);
                if ($lead->phone) $query->orWhere('phone', $lead->phone);
                $query->orWhereRaw('LOWER(church_name) = ?', [mb_strtolower($lead->church_name)]);
            })->pluck('id')->all(),
        ], null);
        $this->submitted = true;
        $this->resetExcept('submitted');
    }

    public function render()
    {
        return view('livewire.pages.church-lead-form')->title('Church Management System Enquiry | MephEd');
    }
}
