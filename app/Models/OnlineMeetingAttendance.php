<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(OnlineMeeting::class, 'online_meeting_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
