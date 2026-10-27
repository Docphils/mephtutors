<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanyProduct extends Model
{
    public const CATEGORIES = [
        'software' => 'Software',
        'ebooks' => 'eBooks and Publishing',
        'digital_solutions' => 'Digital Solutions',
        'education' => 'Education',
    ];

    public const RELEASE_STATUSES = [
        'available' => 'Available',
        'coming_soon' => 'Coming soon',
        'in_development' => 'In development',
    ];

    protected $fillable = [
        'name', 'slug', 'category', 'release_status', 'summary', 'description', 'website_url',
        'cta_label', 'image_path', 'is_featured', 'is_published', 'display_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'display_order' => 'integer',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path) return asset('images/MephEd.png');
        if (Str::startsWith($this->image_path, ['http://', 'https://'])) return $this->image_path;
        if (Str::startsWith($this->image_path, ['images/', 'storage/'])) return asset($this->image_path);
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
