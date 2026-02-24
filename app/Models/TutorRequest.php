<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorRequest extends Model
{
    protected $fillable = [
        'user_id',
        'service_item_id',
        'level_id',
        'exam_type_id',
        'delivery_mode',
        'session_type',
        'preferred_days',
        'duration_per_session',
        'budget_min',
        'budget_max',
        'state',
        'city',
        'lesson_address',
        'preferred_tutor_gender',
        'additional_notes',
        'status',
        'matched_at',
        'started_at',
        'completed_at',
        'is_for_self',
        'learners',
        'curriculum',
        'subjects',
    ];

    protected $casts = [
        'matched_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'learners' => 'array',
        'is_for_self' => 'boolean',
        'subjects' => 'array',
        'preferred_days' => 'array',
    ];

    public function getLearnerNamesAttribute()
    {
        if ($this->is_for_self) {
            return $this->user->name;
        }

        return collect($this->learners)->pluck('name')->implode(', ');
    }

    // CLIENT
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // SERVICE
    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    // MATCHING
    public function tutorMatches(): HasMany
    {
        return $this->hasMany(TutorMatch::class);
    }

    public function acceptedMatch()
    {
        return $this->hasOne(TutorMatch::class)
            ->where('status', 'accepted');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
