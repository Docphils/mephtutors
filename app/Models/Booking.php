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
        'start_date',
        'end_date',
        'location',
        'sessions',
        'duration',
        'learners',
        'days_times',
        'tutorGender',
        'classes',
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

}
