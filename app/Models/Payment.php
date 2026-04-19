<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'booking_id',
        'programme_enquiry_assignment_id',
        'amount',
        'evidence',
        'status',
        'dispute',
    ];

    protected $casts = [
        'dispute' => 'array',
    ];



    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function programmeAssignment(): BelongsTo
    {
        return $this->belongsTo(ProgrammeEnquiryAssignment::class, 'programme_enquiry_assignment_id');
    }
}
