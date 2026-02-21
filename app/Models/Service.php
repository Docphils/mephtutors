<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'target',
        'is_active',
    ];

    public function serviceItems(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }
}
