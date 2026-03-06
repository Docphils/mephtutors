<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineMeetingAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'online_meeting_id',
        'user_id',
        'role',
        'attendance_status',
        'joined_at',
        'left_at',
        'notes',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(OnlineMeeting::class, 'online_meeting_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
