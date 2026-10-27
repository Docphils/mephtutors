<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChurchLead extends Model
{
    public const STATUSES = [
        'new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified',
        'meeting_scheduled' => 'Demo or meeting scheduled', 'follow_up_required' => 'Follow-up required',
        'converted' => 'Converted', 'not_proceeding' => 'Not proceeding', 'duplicate' => 'Duplicate', 'archived' => 'Archived',
    ];

    protected $fillable = [
        'church_name', 'contact_name', 'contact_role', 'email', 'phone', 'country_region',
        'congregation_size', 'current_records_method', 'interests', 'timeframe',
        'preferred_contact_method', 'source', 'enquiry', 'contact_consent', 'contact_consent_at',
        'marketing_consent', 'marketing_consent_at', 'status', 'assigned_to', 'follow_up_at',
        'not_proceeding_reason', 'duplicate_of_id', 'archived_at',
    ];

    protected $casts = [
        'interests' => 'array', 'contact_consent' => 'boolean', 'marketing_consent' => 'boolean',
        'contact_consent_at' => 'datetime', 'marketing_consent_at' => 'datetime',
        'follow_up_at' => 'date', 'archived_at' => 'datetime',
    ];

    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function duplicateOf(): BelongsTo { return $this->belongsTo(self::class, 'duplicate_of_id'); }
    public function activities(): HasMany { return $this->hasMany(ChurchLeadActivity::class)->latest(); }

    public function log(string $type, ?string $description = null, array $properties = [], ?int $userId = null): void
    {
        $this->activities()->create([
            'user_id' => $userId ?? auth()->id(), 'type' => $type,
            'description' => $description, 'properties' => $properties ?: null,
        ]);
    }
}
