<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbookPolicy extends Model
{
    public const REQUIRED_SLUGS = ['refund', 'terms', 'privacy'];

    protected $fillable = [
        'title',
        'content',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public static function requiredPoliciesArePublished(): bool
    {
        return static::query()
            ->whereIn('slug', self::REQUIRED_SLUGS)
            ->where('is_published', true)
            ->count() === count(self::REQUIRED_SLUGS);
    }
}
