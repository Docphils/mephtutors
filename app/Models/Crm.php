<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Crm extends Model
{
    protected $fillable = [
        'user_id',
        'institution_name',
        'institution_address',
        'service_item_id',
        'number_of_tutors_required',
        'delivery_mode',
        'engagement_type',
        'requirements',
        'quote_amount',
        'quote_notes',
        'contract_terms',
        'sessions_per_week',
        'status',
        'payment_status',
        'payment_reference',
        'payment_link',
        'contacted_at',
        'proposal_sent_at',
        'approved_at',
        'paid_at',
        'deployed_at',
        'closed_at',
        'curriculum',
        'level',
        'exam_type',
    ];

    protected $casts = [
        'quote_amount' => 'decimal:2',
        'contacted_at' => 'datetime',
        'proposal_sent_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'deployed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);   
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CrmAssignment::class);
    }

    public function tutors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'crm_assignments')
            ->withPivot(['role', 'status', 'notes', 'assigned_by', 'started_at', 'completed_at'])
            ->withTimestamps();
    }
}
