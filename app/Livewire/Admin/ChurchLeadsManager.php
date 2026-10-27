<?php

namespace App\Livewire\Admin;

use App\Models\ChurchLead;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Church CMS Leads - MephEd Admin')]
class ChurchLeadsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $assigneeFilter = '';
    public string $sourceFilter = '';
    public string $interestFilter = '';
    public string $timeframeFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $sort = 'recent';
    public ?int $selectedId = null;
    public string $statusInput = '';
    public string $reason = '';
    public string $assigneeInput = '';
    public string $followUpInput = '';
    public string $note = '';
    public string $attemptOutcome = '';
    public string $duplicateId = '';
    public string $editChurch = '';
    public string $editName = '';
    public string $editRole = '';
    public string $editEmail = '';
    public string $editPhone = '';
    public string $editCountry = '';

    public function updating($property): void { if (in_array($property, ['search', 'statusFilter', 'assigneeFilter', 'sourceFilter', 'interestFilter', 'timeframeFilter', 'dateFrom', 'dateTo', 'sort'], true)) $this->resetPage(); }

    private function admin(): void { Gate::authorize('Admin'); }

    public function open(int $id): void
    {
        $this->admin();
        $lead = ChurchLead::findOrFail($id);
        $this->selectedId = $lead->id;
        $this->statusInput = $lead->status;
        $this->reason = $lead->not_proceeding_reason ?? '';
        $this->assigneeInput = (string) ($lead->assigned_to ?? '');
        $this->followUpInput = $lead->follow_up_at?->format('Y-m-d') ?? '';
        $this->editChurch = $lead->church_name;
        $this->editName = $lead->contact_name;
        $this->editRole = $lead->contact_role;
        $this->editEmail = $lead->email;
        $this->editPhone = $lead->phone ?? '';
        $this->editCountry = $lead->country_region;
        $this->duplicateId = '';
    }

    public function close(): void { $this->selectedId = null; }

    public function saveDetails(): void
    {
        $this->admin();
        $lead = $this->selected();
        $data = $this->validate([
            'editChurch' => ['required', 'string', 'max:160'], 'editName' => ['required', 'string', 'max:120'],
            'editRole' => ['required', 'string', 'max:120'], 'editEmail' => ['required', 'email', 'max:190'],
            'editPhone' => ['nullable', 'string', 'max:40'], 'editCountry' => ['required', 'string', 'max:120'],
        ]);
        $changedFields = [];
        foreach (['editChurch' => 'church_name', 'editName' => 'contact_name', 'editRole' => 'contact_role', 'editEmail' => 'email', 'editPhone' => 'phone', 'editCountry' => 'country_region'] as $input => $column) {
            $value = $data[$input] ?: null;
            if ($lead->{$column} !== $value) $changedFields[] = $column;
            $lead->{$column} = $value;
        }
        $lead->save();
        if ($changedFields) $lead->log('edited', 'Contact details updated.', ['fields' => $changedFields]);
        session()->flash('message', 'Lead details saved.');
    }

    public function updateWorkflow(): void
    {
        $this->admin();
        $lead = $this->selected();
        $data = $this->validate([
            'statusInput' => ['required', Rule::in(array_keys(ChurchLead::STATUSES))],
            'assigneeInput' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'admin')],
            'followUpInput' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:120'],
        ]);
        $oldStatus = $lead->status;
        if ($oldStatus !== $data['statusInput']) {
            $lead->status = $data['statusInput'];
            $lead->not_proceeding_reason = $data['statusInput'] === 'not_proceeding' ? ($data['reason'] ?: null) : null;
            $lead->archived_at = $data['statusInput'] === 'archived' ? now() : null;
            $lead->log('status_changed', 'Status changed from ' . (ChurchLead::STATUSES[$oldStatus] ?? $oldStatus) . ' to ' . ChurchLead::STATUSES[$data['statusInput']] . '.', ['from' => $oldStatus, 'to' => $data['statusInput'], 'reason' => $data['reason'] ?: null]);
        } else {
            $lead->not_proceeding_reason = $data['statusInput'] === 'not_proceeding' ? ($data['reason'] ?: null) : $lead->not_proceeding_reason;
        }
        $oldAssignee = $lead->assigned_to;
        $lead->assigned_to = $data['assigneeInput'] ?: null;
        if ((string) $oldAssignee !== (string) $lead->assigned_to) $lead->log('assignment_changed', 'Lead assignment changed.', ['from' => $oldAssignee, 'to' => $lead->assigned_to]);
        $oldFollowUp = $lead->follow_up_at?->format('Y-m-d');
        $lead->follow_up_at = $data['followUpInput'] ?: null;
        if ($oldFollowUp !== $lead->follow_up_at?->format('Y-m-d')) $lead->log('follow_up_changed', 'Follow-up date changed.', ['from' => $oldFollowUp, 'to' => $lead->follow_up_at?->format('Y-m-d')]);
        $lead->save();
        session()->flash('message', 'Lead workflow updated.');
    }

    public function addNote(): void
    {
        $this->admin();
        $data = $this->validate(['note' => ['required', 'string', 'max:2000']]);
        $this->selected()->log('note', $data['note']);
        $this->note = '';
    }

    public function recordAttempt(): void
    {
        $this->admin();
        $data = $this->validate(['attemptOutcome' => ['required', Rule::in(['No answer', 'Spoke with contact', 'Email sent', 'Meeting arranged', 'Other'])]]);
        $this->selected()->log('contact_attempt', 'Contact attempt: ' . $data['attemptOutcome'] . '.');
        $this->attemptOutcome = '';
    }

    public function markDuplicate(): void
    {
        $this->admin();
        $lead = $this->selected();
        $data = $this->validate(['duplicateId' => ['required', 'integer', Rule::exists('church_leads', 'id')->whereNot('id', $lead->id)]]);
        $lead->update(['status' => 'duplicate', 'duplicate_of_id' => $data['duplicateId']]);
        $lead->log('duplicate_marked', 'Marked as a duplicate of lead #' . $data['duplicateId'] . '.', ['duplicate_of_id' => (int) $data['duplicateId']]);
        $lead->duplicateOf?->log('duplicate_linked', 'Lead #' . $lead->id . ' was linked as a duplicate.', ['duplicate_id' => $lead->id]);
        $this->statusInput = 'duplicate';
    }

    public function anonymize(): void
    {
        $this->admin();
        $lead = $this->selected();
        $lead->activities()->update([
            'description' => 'Activity details removed during privacy anonymization.',
            'properties' => null,
        ]);
        $lead->update([
            'church_name' => 'Anonymized church', 'contact_name' => 'Anonymized contact', 'contact_role' => 'Unknown',
            'email' => 'anonymized+' . $lead->id . '@invalid.example', 'phone' => null, 'country_region' => 'Unknown',
            'enquiry' => null, 'interests' => [], 'source' => null, 'contact_consent' => false,
            'marketing_consent' => false, 'contact_consent_at' => null, 'marketing_consent_at' => null,
        ]);
        $lead->log('anonymized', 'Lead personal information anonymized.');
        $this->open($lead->id);
        session()->flash('message', 'Personal information anonymized; the activity record was retained.');
    }

    private function selected(): ChurchLead { return ChurchLead::with('activities.user', 'assignee')->findOrFail($this->selectedId); }

    public function render()
    {
        $this->admin();
        $query = ChurchLead::query()->with('assignee')
            ->when($this->search !== '', fn ($q) => $q->where(function ($sub) {
                $term = '%' . addcslashes($this->search, '%_') . '%';
                $sub->where('church_name', 'like', $term)->orWhere('contact_name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term);
            }))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->assigneeFilter !== '', fn ($q) => $q->where('assigned_to', $this->assigneeFilter))
            ->when($this->sourceFilter !== '', fn ($q) => $q->where('source', $this->sourceFilter))
            ->when($this->interestFilter !== '', fn ($q) => $q->whereJsonContains('interests', $this->interestFilter))
            ->when($this->timeframeFilter !== '', fn ($q) => $q->where('timeframe', $this->timeframeFilter))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));
        match ($this->sort) {
            'oldest' => $query->orderBy('created_at'),
            'follow_up' => $query->orderByRaw('follow_up_at IS NULL')->orderBy('follow_up_at'),
            default => $query->latest(),
        };
        $filteredCount = (clone $query)->reorder()->count();
        return view('livewire.admin.church-leads-manager', [
            'leads' => $query->paginate(15), 'admins' => User::where('role', 'admin')->orderBy('name')->get(['id', 'name']),
            'sources' => ChurchLead::query()->whereNotNull('source')->where('source', '<>', '')->distinct()->orderBy('source')->pluck('source'),
            'interestOptions' => ['Members and households', 'Attendance and services', 'Units or groups', 'Care and follow-up', 'Finance', 'Communications', 'Other'],
            'counts' => ChurchLead::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'assigneeCounts' => ChurchLead::query()->selectRaw('assigned_to, count(*) as total')->groupBy('assigned_to')->with('assignee:id,name')->get(),
            'filteredCount' => $filteredCount,
            'selected' => $this->selectedId ? $this->selected() : null,
            'possibleDuplicates' => $this->selectedId ? ChurchLead::query()->whereKeyNot($this->selectedId)->where(function ($q) {
                $lead = ChurchLead::find($this->selectedId);
                if (!$lead) return;
                $q->whereRaw('LOWER(email) = ?', [mb_strtolower($lead->email)])->orWhereRaw('LOWER(church_name) = ?', [mb_strtolower($lead->church_name)]);
                if ($lead->phone) $q->orWhere('phone', $lead->phone);
            })->limit(8)->get() : collect(),
            'overdueCount' => ChurchLead::whereNotIn('status', ['converted', 'not_proceeding', 'archived', 'duplicate'])->whereDate('follow_up_at', '<', today())->count(),
        ]);
    }
}
