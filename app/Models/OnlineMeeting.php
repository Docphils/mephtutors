<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return BelongsTo<ServiceItem, $this> */
    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(ServiceItem::class);
    }

    /** @return BelongsTo<User, $this> */
    public function scheduler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    /** @return BelongsTo<User, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** @return BelongsTo<User, $this> */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    /** @return HasMany<OnlineMeetingAttendance, $this> */
    public function attendances(): HasMany
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
