<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }

    public function enrollees()
    {
        return $this->hasMany(Enrollee::class);
    }

}
