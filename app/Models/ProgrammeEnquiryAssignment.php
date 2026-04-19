<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProgrammeEnquiryAssignment extends Model
{
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CLOSED = 'closed';

    public const VISIBLE_TO_TUTOR = [
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_DECLINED,
        self::STATUS_CANCELLED,
        self::STATUS_CLOSED,
    ];

    public const CAN_BE_MARKED_COMPLETE = [
        self::STATUS_ACTIVE,
        self::STATUS_DECLINED,
    ];
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

    public static function visibleToTutorStatuses(): array
    {
        return self::VISIBLE_TO_TUTOR;
    }

    public static function canBeMarkedCompleteStatuses(): array
    {
        return self::CAN_BE_MARKED_COMPLETE;
    }
}
