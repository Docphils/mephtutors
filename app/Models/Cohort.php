<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'service_item_id',
        'code',
        'start_date',
        'end_date',
        'status',
        'capacity',
        'mode',
        'location',
        'notes',
        'name',
        'fee',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function enrollees(): HasMany
    {
        return $this->hasMany(Enrollee::class);
    }
}
