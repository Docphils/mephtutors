<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbookTaskRun extends Model
{
    protected $fillable = ['task', 'started_at', 'finished_at', 'status', 'checked_count', 'recovered_count', 'last_error'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'checked_count' => 'integer',
        'recovered_count' => 'integer',
    ];
}
