<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProgrammeEnquiry extends Model
{
    protected $fillable = [
        'academic_programme_id',
        'user_id',
        'learner_name',
        'class_level',
        'school_name',
        'exam_type',
        'subjects',
        'weak_areas',
        'recent_performance_notes',
        'lesson_mode',
        'preferred_frequency',
        'preferred_duration',
        'price_quote',
        'price_breakdown',
        'payment_reference',
        'payment_status',
        'preferred_days',
        'preferred_times',
        'parent_name',
        'parent_phone',
        'parent_address',
        'state',
        'city_area',
        'status',
        'source_page',
        'meta',
    ];

    protected $casts = [
        'subjects' => 'array',
        'preferred_days' => 'array',
        'preferred_times' => 'array',
        'meta' => 'array',
        'price_breakdown' => 'array',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(AcademicProgramme::class, 'academic_programme_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProgrammeEnquiryAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(ProgrammeEnquiryAssignment::class)
            ->whereIn('status', ['assigned', 'accepted', 'active', 'declined'])
            ->latestOfMany();
    }

    /**
     * Return the latest assignment regardless of status (completed or otherwise).
     */
    public function latestAssignment(): HasOne
    {
        return $this->hasOne(ProgrammeEnquiryAssignment::class)
            ->latestOfMany();
    }
}
