<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        'name',
        'order',
    ];

    public function tutorRequests(): HasMany
    {
        return $this->hasMany(TutorRequest::class);
    }
}
