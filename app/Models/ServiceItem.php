<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceItem extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'slug',
        'description',
        'image_path',
        'target',
        'requires_level',
        'requires_exam_type',
        'is_active',
        'has_subjects',
        'requires_curriculum',
    ];

    protected $casts = [
        'requires_level' => 'boolean',
        'requires_exam_type' => 'boolean',
        'has_subjects' => 'boolean',
        'requires_curriculum' => 'boolean',
        'is_active' => 'boolean',
    ]; 

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function tutorRequests(): HasMany
    {
        return $this->hasMany(TutorRequest::class);
    }

    public function crms(): BelongsToMany
    {
        return $this->belongsToMany(Crm::class, 'crm_service_items')
            ->withPivot('number_of_tutors')
            ->withTimestamps();
    }
}