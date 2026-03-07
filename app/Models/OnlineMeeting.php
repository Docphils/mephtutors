<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'service_item_id',
        'scheduled_by',
        'client_id',
        'tutor_id',
        'title',
        'description',
        'jitsi_domain',
        'jitsi_room',
        'jitsi_password',
        'starts_at',
        'ends_at',
        'ended_at',
        'status',
        'recording_enabled',
        'recording_url',
        'jitsi_options',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'ended_at' => 'datetime',
        'recording_enabled' => 'boolean',
        'jitsi_options' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function attendances()
    {
        return $this->hasMany(OnlineMeetingAttendance::class);
    }

    public function isParticipant(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return (int) $this->client_id === (int) $user->id
            || (int) $this->tutor_id === (int) $user->id;
    }
}
