<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProgrammeEnquiryAssignment extends Model
{
    protected $fillable = [
        'programme_enquiry_id',
        'tutor_id',
        'assigned_by',
        'status',
        'admin_notes',
        'tutor_notes',
        'start_date',
        'accepted_at',
        'declined_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function programmeEnquiry(): BelongsTo
    {
        return $this->belongsTo(ProgrammeEnquiry::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'programme_enquiry_assignment_id');
    }
}
