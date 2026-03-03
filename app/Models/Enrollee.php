<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollee extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'phone', 'address', 'is_read', 'cohort_id', 'status', 'confirmed_at', 'meta'];


    protected $casts = [
        'confirmed_at' => 'datetime',
        'meta' => 'array',
        'is_read' => 'boolean',
    ];

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }
}
