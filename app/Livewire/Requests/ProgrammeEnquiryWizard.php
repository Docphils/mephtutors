<?php

namespace App\Livewire\Requests;

use App\Mail\ProgrammeEnquiryNotification;
use App\Mail\ProgrammeGuestAcknowledgement;
use App\Models\AcademicProgramme;
use App\Models\ProgrammeEnquiry;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\PaystackService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class ProgrammeEnquiryWizard extends Component
{
    public AcademicProgramme $programme;
    public int $step = 1;
    public bool $submitted = false;
    public ?int $createdProgrammeRequestId = null;
    public ?float $submittedPrice = null;

    public string $learner_name = '';
    public string $class_level = '';
    public string $school_name = '';
    public array $subjects = [];
    public string $weak_areas = '';

    public string $lesson_mode = 'online';
    public string $preferred_frequency = '';
    public string $preferred_duration = '';
    public array $preferred_days = [];
    public string $preferred_time = '';
    public string $state = '';
    public string $city_area = '';
    public string $address = '';
    public bool $use_existing_address = true;
    public string $selected_existing_address = '';
    public array $existing_addresses = [];

    public string $account_email = '';
    public string $parent_name = '';
    public string $parent_phone = '';
    public bool $existingAccountDetected = false;

    public function mount(AcademicProgramme $academicProgramme): void
    {
        abort_unless($academicProgramme->is_active, 404);
        $this->programme = $academicProgramme;

        if (Auth::check()) {
            $role = (string) Auth::user()->role;
            $currentRouteName = request()->route()?->getName();

            if (
                $role === 'client' &&
                Route::has('client.interventions.create') &&
                $currentRouteName !== 'client.interventions.create'
            ) {
                $this->redirectRoute('client.interventions.create', ['programme' => $academicProgramme->slug], navigate: true);
                return;
            }

            if (
                $role === 'admin' &&
                Route::has('admin.dashboard') &&
                $currentRouteName !== 'admin.dashboard'
            ) {
                $this->redirectRoute('admin.dashboard', navigate: true);
                return;
            }

            if (
                $role === 'tutor' &&
                Route::has('tutor.dashboard') &&
                $currentRouteName !== 'tutor.dashboard'
            ) {
                $this->redirectRoute('tutor.dashboard', navigate: true);
                return;
            }
        }
        $this->preferred_frequency = $this->frequencyOptions[0] ?? '';
        $this->preferred_duration = $this->durationOptions[0] ?? '';
        $this->lesson_mode = $this->normalizeLessonModeValue($this->modeOptions[0] ?? ($this->modeValueOptions[0] ?? 'online'));
        $this->preferred_time = '16:00';

        $this->hydrateAuthenticatedDefaults();
        $this->hydrateDraft();
        $this->hydrateSubmissionState();
    }

    public function next(): void
    {
        $this->validateStep();
        $this->step = min($this->reviewStepNumber, $this->step + 1);
        $this->persistDraft();
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->persistDraft();
    }

    public function toggleSubject(string $subject): void
    {
        if (in_array($subject, $this->subjects, true)) {
            $this->subjects = array_values(array_filter($this->subjects, fn ($item) => $item !== $subject));
            return;
        }

        if (count($this->subjects) >= $this->maxSelectableSubjects) {
            $this->addError('subjects', "You can select up to {$this->maxSelectableSubjects} subjects for this intervention.");
            return;
        }

        $this->subjects[] = $subject;
    }

    public function submit(): void
    {
        $this->syncPreferredDaysToFrequency();
        $this->lesson_mode = $this->normalizeLessonModeValue($this->lesson_mode);
        $this->syncAddressSelection();
        $this->validateForSubmission();

        $isAuthenticatedUser = $this->isAuthenticatedUser;
        if (!$isAuthenticatedUser && $this->checkAccountEmailOwnership(strict: false)) {
            $this->persistDraft();
            session(['url.intended' => request()->fullUrl()]);
            return;
        }

        if ($isAuthenticatedUser) {
            $user = Auth::user()->loadMissing('userProfile');
            $this->account_email = (string) $user->email;
            $this->parent_name = trim((string) $user->name);
            $this->parent_phone = trim($this->parent_phone) !== ''
                ? trim($this->parent_phone)
                : trim((string) ($user->userProfile->phone ?? ''));
        } else {
            try {
                $user = User::query()->create([
                    'name' => $this->parent_name,
                    'email' => trim($this->account_email),
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'client',
                ]);
            } catch (QueryException $e) {
                // Race-condition safety: if another process created this email, force login flow.
                if ($this->checkAccountEmailOwnership(strict: false)) {
                    $this->persistDraft();
                    session(['url.intended' => request()->fullUrl()]);
                    return;
                }

                throw $e;
            }
        }

        $profile = UserProfile::query()->firstOrNew(['user_id' => $user->id]);
        $profile->phone = trim($this->parent_phone) !== '' ? $this->parent_phone : ($profile->phone ?? null);
        if ($this->isHomeLessonMode && trim($this->address) !== '') {
            $profile->state = $this->state ?: ($profile->state ?? null);
            $profile->city = $this->city_area ?: ($profile->city ?? null);
            $profile->address = $this->address ?: ($profile->address ?? null);
        }
        $profile->save();

        $quote = $this->calculatedPrice;

        $programmeRequest = ProgrammeEnquiry::query()->create([
            'academic_programme_id' => $this->programme->id,
            'user_id' => $user->id,
            'learner_name' => $this->learner_name,
            'class_level' => $this->isExamSpecificProgramme ? null : $this->class_level,
            'school_name' => $this->school_name ?: null,
            'exam_type' => null,
            'subjects' => $this->subjects,
            'weak_areas' => $this->weak_areas ?: null,
            'recent_performance_notes' => null,
            'lesson_mode' => $this->isHomeLessonMode ? 'home' : 'online',
            'preferred_frequency' => $this->preferred_frequency,
            'preferred_duration' => $this->preferred_duration,
            'price_quote' => $quote,
            'price_breakdown' => $this->priceBreakdown,
            'preferred_days' => $this->preferred_days,
            'preferred_times' => [$this->preferred_time],
            'parent_name' => $this->parent_name ?: (string) $user->name,
            'parent_phone' => $this->parent_phone ?: (string) ($profile->phone ?? ''),
            'parent_address' => $this->isHomeLessonMode ? $this->address : null,
            'state' => $this->state ?: null,
            'city_area' => $this->city_area ?: null,
            'status' => 'pending',
            'payment_status' => 'pending',
            'source_page' => request()->path(),
            'meta' => [
                'programme_slug' => $this->programme->slug,
                'submitted_at' => now()->toIso8601String(),
                'is_exam_programme' => $this->isExamSpecificProgramme,
                'used_existing_address' => $this->isHomeLessonMode && $this->canUseExistingAddress && $this->use_existing_address,
            ],
        ]);

        try {
            if (!$isAuthenticatedUser) {
                $token = Password::broker()->createToken($user);
                Mail::to($user->email)->send(new ProgrammeGuestAcknowledgement(
                    $user,
                    $token,
                    $programmeRequest->load(['programme', 'user.userProfile'])
                ));
            }

            Mail::to('support@mephed.ng')->send(
                new ProgrammeEnquiryNotification(
                    $programmeRequest->load(['programme', 'user.userProfile'])
                )
            );
        } catch (\Throwable $e) {
            logger()->warning('Intervention request acknowledgement email failed: ' . $e->getMessage());
        }

        $this->clearDraft();
        $this->createdProgrammeRequestId = $programmeRequest->id;
        $this->submittedPrice = $quote;
        $this->submitted = true;

        session()->flash($this->submissionSessionKey(), [
            'submitted' => true,
            'id' => $programmeRequest->id,
            'price' => $quote,
        ]);
    }

    public function startPayment(PaystackService $paystack)
    {
        abort_unless($this->createdProgrammeRequestId, 404);

        $programmeRequest = ProgrammeEnquiry::query()->with('user')->findOrFail($this->createdProgrammeRequestId);
        $redirectUrl = $paystack->initializeProgrammeEnquiryPayment($programmeRequest);

        return redirect()->away($redirectUrl);
    }

    protected function validateStep(): void
    {
        if ($this->step === 1) {
            $rules = [
                'learner_name' => 'required|string|min:2|max:120',
                'school_name' => 'nullable|string|max:150',
            ];

            if (!$this->isAuthenticatedUser) {
                $rules['account_email'] = 'required|email|max:120';
            }

            if (!$this->isExamSpecificProgramme) {
                $rules['class_level'] = 'required|string|min:2|max:120';
            } else {
                $rules['class_level'] = 'nullable|string|max:120';
            }

            $this->validate($rules);
            $this->checkAccountEmailOwnership(strict: true);
        }

        if ($this->step === 2) {
            $this->validate([
                'subjects' => ['required', 'array', 'min:1', 'max:' . $this->maxSelectableSubjects],
                'subjects.*' => ['string', Rule::in($this->subjectOptions)],
                'weak_areas' => 'nullable|string|max:2000',
            ]);
        }

        if ($this->step === 3) {
            $rules = [
                'lesson_mode' => ['required', Rule::in($this->modeValueOptions)],
                'preferred_frequency' => ['required', Rule::in($this->frequencyOptions)],
                'preferred_duration' => ['required', Rule::in($this->durationOptions)],
                'preferred_days' => ['required', 'array', 'size:' . $this->frequencyCount],
                'preferred_days.*' => ['required', 'string', 'distinct', Rule::in($this->dayOptions)],
                'preferred_time' => 'required|date_format:H:i',
            ];

            if ($this->isHomeLessonMode && $this->shouldUseExistingAddress) {
                $rules['selected_existing_address'] = ['required', Rule::in($this->existingAddressIds)];
                $rules['state'] = 'nullable|string|max:120';
                $rules['city_area'] = 'nullable|string|max:120';
                $rules['address'] = 'nullable|string|max:500';
            } elseif ($this->isHomeLessonMode) {
                $rules['state'] = 'required|string|max:120';
                $rules['city_area'] = 'required|string|max:120';
                $rules['address'] = 'required|string|max:500';
            } else {
                $rules['selected_existing_address'] = 'nullable|string|max:80';
                $rules['state'] = 'nullable|string|max:120';
                $rules['city_area'] = 'nullable|string|max:120';
                $rules['address'] = 'nullable|string|max:500';
            }

            $this->validate($rules);
        }

        if ($this->needsContactStep && $this->step === 4) {
            $rules = [
                'parent_name' => $this->isAuthenticatedUser
                    ? 'nullable|string|min:2|max:120'
                    : 'required|string|min:2|max:120',
            ];

            if (!$this->isAuthenticatedUser || $this->needsPhoneCapture) {
                $rules['parent_phone'] = 'required|string|min:7|max:30';
            } else {
                $rules['parent_phone'] = 'nullable|string|min:7|max:30';
            }

            $this->validate($rules);
        }
    }

    protected function validateForSubmission(): void
    {
        $this->syncAddressSelection();

        $rules = [
            'learner_name' => 'required|string|min:2|max:120',
            'school_name' => 'nullable|string|max:150',
            'subjects' => ['required', 'array', 'min:1', 'max:' . $this->maxSelectableSubjects],
            'subjects.*' => ['string', Rule::in($this->subjectOptions)],
            'weak_areas' => 'nullable|string|max:2000',
            'lesson_mode' => ['required', Rule::in($this->modeValueOptions)],
            'preferred_frequency' => ['required', Rule::in($this->frequencyOptions)],
            'preferred_duration' => ['required', Rule::in($this->durationOptions)],
            'preferred_days' => ['required', 'array', 'size:' . $this->frequencyCount],
            'preferred_days.*' => ['required', 'string', 'distinct', Rule::in($this->dayOptions)],
            'preferred_time' => 'required|date_format:H:i',
            'parent_name' => $this->isAuthenticatedUser
                ? 'nullable|string|min:2|max:120'
                : 'required|string|min:2|max:120',
            'parent_phone' => (!$this->isAuthenticatedUser || $this->needsPhoneCapture)
                ? 'required|string|min:7|max:30'
                : 'nullable|string|min:7|max:30',
        ];

        if (!$this->isAuthenticatedUser) {
            $rules['account_email'] = 'required|email|max:120';
        }

        if ($this->isExamSpecificProgramme) {
            $rules['class_level'] = 'nullable|string|max:120';
        } else {
            $rules['class_level'] = 'required|string|min:2|max:120';
        }

        if ($this->isHomeLessonMode) {
            if ($this->shouldUseExistingAddress) {
                $rules['selected_existing_address'] = ['required', Rule::in($this->existingAddressIds)];
                $rules['state'] = 'nullable|string|max:120';
                $rules['city_area'] = 'nullable|string|max:120';
                $rules['address'] = 'nullable|string|max:500';
            } else {
                $rules['state'] = 'required|string|max:120';
                $rules['city_area'] = 'required|string|max:120';
                $rules['address'] = 'required|string|max:500';
            }
        } else {
            $rules['selected_existing_address'] = 'nullable|string|max:80';
            $rules['state'] = 'nullable|string|max:120';
            $rules['city_area'] = 'nullable|string|max:120';
            $rules['address'] = 'nullable|string|max:500';
        }

        $this->validate($rules);
        $this->checkAccountEmailOwnership(strict: true);
    }

    protected function checkAccountEmailOwnership(bool $strict = false): bool
    {
        if ($this->isAuthenticatedUser) {
            $this->existingAccountDetected = false;
            return false;
        }

        $email = trim(strtolower($this->account_email));
        if ($email === '') {
            $this->existingAccountDetected = false;
            return false;
        }

        $exists = User::query()->whereRaw('LOWER(TRIM(email)) = ?', [$email])->exists();
        if (!$exists) {
            $this->existingAccountDetected = false;
            $this->resetErrorBag('account_email');
            return false;
        }

        $this->existingAccountDetected = true;
        $this->addError('account_email', 'An account already exists for this email. Please login to continue this request.');

        if ($strict) {
            $this->persistDraft();
            session(['url.intended' => request()->fullUrl()]);
            throw ValidationException::withMessages([
                'account_email' => 'An account already exists for this email. Please login to continue this request.',
            ]);
        }

        return true;
    }

    public function updatedAccountEmail($value): void
    {
        $this->account_email = trim((string) $value);
        $this->existingAccountDetected = false;
        $this->resetErrorBag('account_email');

        if ($this->isAuthenticatedUser) {
            return;
        }

        if (!filter_var($this->account_email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        if ($this->checkAccountEmailOwnership(strict: false)) {
            $this->persistDraft();
            session(['url.intended' => request()->fullUrl()]);
        }
    }

    public function getSubjectOptionsProperty(): array
    {
        return $this->programme->subject_options ?: [];
    }

    public function getFrequencyOptionsProperty(): array
    {
        return $this->programme->frequency_options ?: [];
    }

    public function getDurationOptionsProperty(): array
    {
        return $this->programme->duration_options ?: [];
    }

    public function getModeOptionsProperty(): array
    {
        return $this->programme->mode_options ?: ['online', 'home lessons'];
    }

    public function getModeValueOptionsProperty(): array
    {
        return collect($this->modeOptions)
            ->map(fn ($label) => $this->normalizeLessonModeValue((string) $label))
            ->unique()
            ->values()
            ->all();
    }

    public function getMaxSelectableSubjectsProperty(): int
    {
        return $this->programme->max_selectable_subjects > 0 ? $this->programme->max_selectable_subjects : 6;
    }

    public function getFrequencyCountProperty(): int
    {
        if (preg_match('/^(\d+)/', (string) $this->preferred_frequency, $matches)) {
            return max(1, (int) $matches[1]);
        }

        return 1;
    }

    public function updatedPreferredDays($value): void
    {
        $this->syncPreferredDaysToFrequency();
    }

    public function updatedPreferredFrequency($value): void
    {
        if (!in_array($value, $this->frequencyOptions, true)) {
            return;
        }

        $this->syncPreferredDaysToFrequency();
        $this->resetErrorBag('preferred_days');
    }

    public function updatedLessonMode($value): void
    {
        $normalized = $this->normalizeLessonModeValue((string) $value);
        if ($this->lesson_mode !== $normalized) {
            $this->lesson_mode = $normalized;
        }

        if ($this->isHomeLessonMode) {
            if ($this->shouldUseExistingAddress) {
                $this->applySelectedExistingAddress();
            }
        } else {
            $this->state = '';
            $this->city_area = '';
            $this->address = '';
            $this->selected_existing_address = '';
            $this->use_existing_address = $this->canUseExistingAddress;
            $this->resetErrorBag(['state', 'city_area', 'address', 'selected_existing_address']);
        }
    }

    public function updatedUseExistingAddress($value): void
    {
        $this->use_existing_address = (bool) $value;

        if (!$this->isHomeLessonMode) {
            return;
        }

        if ($this->shouldUseExistingAddress) {
            $this->applySelectedExistingAddress();
            return;
        }

        $this->selected_existing_address = '';
        $this->state = '';
        $this->city_area = '';
        $this->address = '';
    }

    public function updatedSelectedExistingAddress($value): void
    {
        if (!$this->isHomeLessonMode) {
            return;
        }

        $this->selected_existing_address = (string) $value;
        $this->applySelectedExistingAddress();
    }

    public function togglePreferredDay(string $day): void
    {
        if (!in_array($day, $this->dayOptions, true)) {
            return;
        }

        if (in_array($day, $this->preferred_days, true)) {
            $this->preferred_days = array_values(array_filter($this->preferred_days, fn ($d) => $d !== $day));
            $this->resetErrorBag('preferred_days');
            return;
        }

        if (count($this->preferred_days) >= $this->frequencyCount) {
            $this->addError(
                'preferred_days',
                "You selected {$this->preferred_frequency}. Please choose exactly {$this->frequencyCount} day(s)."
            );
            return;
        }

        $this->preferred_days[] = $day;
        $this->syncPreferredDaysToFrequency();
        $this->resetErrorBag('preferred_days');
    }

    public function getIsExamSpecificProgrammeProperty(): bool
    {
        return in_array($this->programme->slug, [
            'waec-final-sprint',
            'neco-final-sprint',
            'nabteb-final-sprint',
        ], true);
    }

    public function getCalculatedPriceProperty(): float
    {
        $matrix = $this->programme->pricing_matrix ?: [];
        $frequencyBase = (float) ($matrix['frequency_prices'][$this->preferred_frequency] ?? 0);
        $durationMultiplier = (float) ($matrix['duration_multipliers'][$this->preferred_duration] ?? 1);
        $modeMultiplier = (float) ($matrix['mode_multipliers'][$this->isHomeLessonMode ? 'home' : 'online'] ?? 1);
        $baseAllowance = (int) ($matrix['base_subject_allowance'] ?? 1);
        $additionalSubjectFraction = (float) ($matrix['additional_subject_fraction'] ?? 0.35);
        $locationSurcharge = $this->isHomeLessonMode ? (float) ($matrix['location_surcharge'] ?? 0) : 0;

        $oneSubjectBenchmark = $frequencyBase * $durationMultiplier * $modeMultiplier;
        $extraSubjects = max(0, count($this->subjects) - max(1, $baseAllowance));
        $additionalSubjectsCost = $extraSubjects * ($oneSubjectBenchmark * $additionalSubjectFraction);

        return round($oneSubjectBenchmark + $additionalSubjectsCost + $locationSurcharge, 2);
    }

    public function getPriceBreakdownProperty(): array
    {
        $matrix = $this->programme->pricing_matrix ?: [];
        $baseAllowance = (int) ($matrix['base_subject_allowance'] ?? 1);

        return [
            'frequency' => $this->preferred_frequency,
            'duration' => $this->preferred_duration,
            'mode' => $this->isHomeLessonMode ? 'home' : 'online',
            'selected_subject_count' => count($this->subjects),
            'included_subject_count' => $baseAllowance,
            'frequency_benchmark' => (float) ($matrix['frequency_prices'][$this->preferred_frequency] ?? 0),
            'additional_subject_fraction' => (float) ($matrix['additional_subject_fraction'] ?? 0.35),
            'calculated_total' => $this->calculatedPrice,
        ];
    }

    public function getIsHomeLessonModeProperty(): bool
    {
        return $this->normalizeLessonModeValue($this->lesson_mode) === 'home';
    }

    public function getIsAuthenticatedUserProperty(): bool
    {
        return Auth::check();
    }

    public function getNeedsContactStepProperty(): bool
    {
        if (!$this->isAuthenticatedUser) {
            return true;
        }

        return $this->needsPhoneCapture;
    }

    public function getNeedsPhoneCaptureProperty(): bool
    {
        if (!$this->isAuthenticatedUser) {
            return true;
        }

        return trim($this->parent_phone) === '';
    }

    public function getReviewStepNumberProperty(): int
    {
        return $this->needsContactStep ? 5 : 4;
    }

    public function getCanUseExistingAddressProperty(): bool
    {
        return $this->isAuthenticatedUser && count($this->existing_addresses) > 0;
    }

    public function getShouldUseExistingAddressProperty(): bool
    {
        return $this->isHomeLessonMode && $this->canUseExistingAddress && $this->use_existing_address;
    }

    public function getExistingAddressIdsProperty(): array
    {
        return collect($this->existing_addresses)->pluck('id')->all();
    }

    public function getSelectedExistingAddressOptionProperty(): ?array
    {
        if ($this->selected_existing_address === '') {
            return null;
        }

        return collect($this->existing_addresses)
            ->first(fn ($entry) => ($entry['id'] ?? '') === $this->selected_existing_address);
    }

    public function getDayOptionsProperty(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    protected function syncPreferredDaysToFrequency(): void
    {
        $this->preferred_days = collect($this->preferred_days)
            ->map(fn ($day) => trim((string) $day))
            ->filter(fn ($day) => in_array($day, $this->dayOptions, true))
            ->unique()
            ->take($this->frequencyCount)
            ->values()
            ->all();
    }

    protected function normalizeLessonModeValue(?string $mode): string
    {
        $normalized = strtolower(trim((string) $mode));
        if ($normalized === '') {
            return 'online';
        }

        if (
            str_contains($normalized, 'home') ||
            str_contains($normalized, 'physical') ||
            str_contains($normalized, 'in-person') ||
            str_contains($normalized, 'in person') ||
            str_contains($normalized, 'offline')
        ) {
            return 'home';
        }

        return 'online';
    }

    protected function hydrateAuthenticatedDefaults(): void
    {
        if (!$this->isAuthenticatedUser) {
            return;
        }

        $user = Auth::user()->loadMissing('userProfile');
        $this->account_email = trim((string) $user->email);
        $this->parent_name = trim((string) $user->name);
        $this->parent_phone = trim((string) ($user->userProfile->phone ?? ''));
        if ($this->parent_phone === '') {
            $this->parent_phone = trim((string) (
                ProgrammeEnquiry::query()
                    ->where('user_id', $user->id)
                    ->whereNotNull('parent_phone')
                    ->where('parent_phone', '<>', '')
                    ->latest('id')
                    ->value('parent_phone')
            ));
        }

        $this->existing_addresses = $this->buildExistingAddresses($user);
        $this->use_existing_address = count($this->existing_addresses) > 0;

        if ($this->use_existing_address) {
            $this->selected_existing_address = (string) ($this->existing_addresses[0]['id'] ?? '');
            $this->applySelectedExistingAddress();
        }
    }

    protected function hydrateDraft(): void
    {
        $draft = session()->get($this->draftSessionKey());
        if (!is_array($draft)) {
            return;
        }

        if (
            $this->isAuthenticatedUser &&
            !empty($draft['account_email']) &&
            strcasecmp((string) $draft['account_email'], (string) Auth::user()->email) !== 0
        ) {
            $this->clearDraft();
            return;
        }

        $fillableDraft = [
            'step',
            'learner_name',
            'class_level',
            'school_name',
            'subjects',
            'weak_areas',
            'lesson_mode',
            'preferred_frequency',
            'preferred_duration',
            'preferred_days',
            'preferred_time',
            'state',
            'city_area',
            'address',
            'use_existing_address',
            'selected_existing_address',
            'parent_name',
            'parent_phone',
            'account_email',
        ];

        foreach ($fillableDraft as $field) {
            if (array_key_exists($field, $draft)) {
                $this->{$field} = $draft[$field];
            }
        }

        $this->step = max(1, min((int) $this->step, $this->reviewStepNumber));
        if ($this->isAuthenticatedUser) {
            $this->account_email = (string) Auth::user()->email;
        }

        if ($this->isHomeLessonMode && $this->shouldUseExistingAddress) {
            $this->applySelectedExistingAddress();
        }
    }

    protected function persistDraft(): void
    {
        session()->put($this->draftSessionKey(), [
            'step' => $this->step,
            'learner_name' => $this->learner_name,
            'class_level' => $this->class_level,
            'school_name' => $this->school_name,
            'subjects' => $this->subjects,
            'weak_areas' => $this->weak_areas,
            'lesson_mode' => $this->lesson_mode,
            'preferred_frequency' => $this->preferred_frequency,
            'preferred_duration' => $this->preferred_duration,
            'preferred_days' => $this->preferred_days,
            'preferred_time' => $this->preferred_time,
            'state' => $this->state,
            'city_area' => $this->city_area,
            'address' => $this->address,
            'use_existing_address' => $this->use_existing_address,
            'selected_existing_address' => $this->selected_existing_address,
            'parent_name' => $this->parent_name,
            'parent_phone' => $this->parent_phone,
            'account_email' => $this->account_email,
        ]);
    }

    protected function clearDraft(): void
    {
        session()->forget($this->draftSessionKey());
    }

    protected function draftSessionKey(): string
    {
        return 'programme_enquiry_draft:' . $this->programme->slug;
    }

    protected function submissionSessionKey(): string
    {
        $owner = $this->isAuthenticatedUser ? 'user:' . (string) Auth::id() : 'guest';
        return 'programme_enquiry_submitted:' . $owner . ':' . $this->programme->slug;
    }

    protected function hydrateSubmissionState(): void
    {
        $submissionState = session()->pull($this->submissionSessionKey());
        if (!is_array($submissionState) || empty($submissionState['submitted'])) {
            return;
        }

        $this->submitted = true;
        $this->createdProgrammeRequestId = isset($submissionState['id']) ? (int) $submissionState['id'] : null;
        $this->submittedPrice = isset($submissionState['price']) ? (float) $submissionState['price'] : null;
    }

    protected function buildExistingAddresses(User $user): array
    {
        $unique = [];

        $appendAddress = function (?string $address, ?string $city, ?string $state, string $source) use (&$unique): void {
            $address = trim((string) $address);
            $city = trim((string) $city);
            $state = trim((string) $state);

            if ($address === '') {
                return;
            }

            $key = strtolower($address . '|' . $city . '|' . $state);
            if (isset($unique[$key])) {
                return;
            }

            $labelParts = array_filter([$address, $city, $state], fn ($part) => trim((string) $part) !== '');
            $unique[$key] = [
                'id' => sha1($key),
                'address' => $address,
                'city_area' => $city,
                'state' => $state,
                'label' => implode(', ', $labelParts),
                'source' => $source,
            ];
        };

        $appendAddress(
            $user->userProfile->address ?? null,
            $user->userProfile->city ?? null,
            $user->userProfile->state ?? null,
            'Profile'
        );

        ProgrammeEnquiry::query()
            ->where('user_id', $user->id)
            ->whereNotNull('parent_address')
            ->latest('id')
            ->limit(15)
            ->get(['parent_address', 'city_area', 'state'])
            ->each(function ($enquiry) use ($appendAddress): void {
                $appendAddress(
                    (string) $enquiry->parent_address,
                    (string) $enquiry->city_area,
                    (string) $enquiry->state,
                    'Intervention Request'
                );
            });

        return array_values($unique);
    }

    protected function syncAddressSelection(): void
    {
        if ($this->isHomeLessonMode && $this->shouldUseExistingAddress) {
            $this->applySelectedExistingAddress();
        }
    }

    protected function applySelectedExistingAddress(): void
    {
        if (!$this->shouldUseExistingAddress) {
            return;
        }

        $selected = $this->selectedExistingAddressOption;
        if (!$selected && count($this->existing_addresses) > 0) {
            $this->selected_existing_address = (string) ($this->existing_addresses[0]['id'] ?? '');
            $selected = $this->selectedExistingAddressOption;
        }

        if (!$selected) {
            return;
        }

        $this->address = (string) ($selected['address'] ?? '');
        $this->city_area = (string) ($selected['city_area'] ?? '');
        $this->state = (string) ($selected['state'] ?? '');
    }

    public function render()
    {
        return view('livewire.requests.programme-enquiry-wizard', [
            'dayOptions' => $this->dayOptions,
            'stateOptions' => [
                'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta',
                'Ebonyi','Edo','Ekiti','Enugu','FCT','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi',
                'Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto',
                'Taraba','Yobe','Zamfara',
            ],
        ]);
    }
}
