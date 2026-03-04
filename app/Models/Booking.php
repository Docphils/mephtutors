<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function payments()
    {
        return $this->hasOne(Payment::class);
    }

    public function tutorRequest()
    {
        return $this->belongsTo(TutorRequest::class, 'tutor_request_id');
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class, 'service_item_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
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
        return is_array($this->learners)
            ? $this->learners
            : (json_decode($this->learners, true) ?: []);
    }

    public function getFormattedSubjectsAttribute()
    {
        return is_array($this->subjects)
            ? $this->subjects
            : (json_decode($this->subjects, true) ?: []);
    }

    public function getFormattedDaysTimesAttribute()
    {
        return is_array($this->days_times)
            ? $this->days_times
            : (json_decode($this->days_times, true) ?: []);
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
