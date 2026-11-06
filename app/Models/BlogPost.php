<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BlogPost extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'author_id',
        'editor_id',
        'category_id',
        'meta_title',
        'meta_description',
        'canonical_url',
        'published_at',
        'hero_image_path',
    ];

    protected $casts = ['published_at' => 'datetime'];

    /** @param Builder<BlogPost> $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<User, $this> */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    /** @return BelongsTo<BlogCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /** @return BelongsToMany<BlogTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    /** @return HasMany<BlogSlugRedirect, $this> */
    public function slugRedirects(): HasMany
    {
        return $this->hasMany(BlogSlugRedirect::class);
    }

    /** @return HasOne<BlogFacebookShare, $this> */
    public function facebookShare(): HasOne
    {
        return $this->hasOne(BlogFacebookShare::class);
    }
}
