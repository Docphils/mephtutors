<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Crm extends Model
{
    protected $fillable = [
        'user_id',
        'institution_name',
        'institution_address',
        'service_item_id',
        'number_of_tutors_required',
        'delivery_mode',
        'engagement_type',
        'requirements',
        'status',
        'contacted_at',
        'closed_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);   
    }

    public function serviceItem()
    {
        return $this->belongsTo(ServiceItem::class);
    }
}
