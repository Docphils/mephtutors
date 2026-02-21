<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorMatch extends Model
{
    protected $fillable = [
        'tutor_request_id',
        'tutor_id',
        'status',
    ];

    public function tutorRequest(): BelongsTo
    {
        return $this->belongsTo(TutorRequest::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
}

