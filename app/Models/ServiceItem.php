<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceItem extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'slug',
        'shown_on_welcome',
        'display_position',
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

    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return asset('images/MephEd.png');
        }

        $path = trim((string) $this->image_path);
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (Str::startsWith($normalized, ['images/', 'storage/'])) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    }
}
