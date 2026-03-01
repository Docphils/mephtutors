<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'booking_id',
        'amount',
        'evidence',
        'status',
        'dispute',
    ];

    protected $casts = [
        'dispute' => 'array',
    ];



    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
