<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_request_id',
        'user_id',
        'client_id',
        'tutor_id',
        'payment_id',
        'level_id',
        'exam_type_id',
        'start_date',
        'end_date',
        'location',
        'sessions',
        'duration',
        'learners',
        'days_times',
        'tutorGender',
        'classes',
        'service_item_id',
        'amount',
        'curriculum',
        'subjects',
        'tutorRemarks',
        'clientAcceptanceRemarks',
        'clientApprovalRemarks',
        'status',
        'completed_at',
        'paymentStatus',
        'paymentEvidence',
        'payment_reference',
        'client_payment_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
        'days_times' => 'array',
        'learners' => 'array',
        'subjects' => 'array',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** @return HasOne<Payment, $this> */
    public function payments(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /** @return BelongsTo<TutorRequest, $this> */
    public function tutorRequest(): BelongsTo
    {
        return $this->belongsTo(TutorRequest::class, 'tutor_request_id');
    }

    /** @return BelongsTo<ServiceItem, $this> */
    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(ServiceItem::class, 'service_item_id');
    }

    /** @return BelongsTo<Level, $this> */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    /** @return BelongsTo<ExamType, $this> */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    /** @return HasMany<OnlineMeeting, $this> */
    public function onlineMeetings(): HasMany
    {
        return $this->hasMany(OnlineMeeting::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($booking) {
            $tutorRequest = $booking->tutorRequest;
            // keep tutor request status in sync with new tutor_requests enum
            if ($tutorRequest && strtolower($tutorRequest->status) === 'pending') {
                $tutorRequest->status = 'matched';
                $tutorRequest->matched_at = now();
                $tutorRequest->save();
            }
        });
    }

    public function getFormattedLearnersAttribute()
    {
        return $this->learners ?? [];
    }

    public function getFormattedSubjectsAttribute()
    {
        return $this->subjects ?? [];
    }

    public function getFormattedDaysTimesAttribute()
    {
        return $this->days_times ?? [];
    }

    public function getLearnersStringAttribute()
    {
        return collect($this->formatted_learners)
            ->pluck('name')
            ->filter()
            ->implode(', ') ?: 'N/A';
    }

    public function getSubjectsStringAttribute()
    {
        return implode(', ', array_filter($this->formatted_subjects)) ?: 'No Subjects';
    }
}
