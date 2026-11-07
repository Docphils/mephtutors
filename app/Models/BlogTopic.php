<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogTopic extends Model
{
    public const TYPE_CURRENT_AFFAIRS = 'current_affairs';

    public const TYPE_EBOOK_INSPIRED = 'ebook_inspired';

    public const TYPE_OPINION = 'opinion';

    public const TYPE_PRACTICAL = 'practical';

    public const TYPE_RESEARCH = 'education_research';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'topic_type',
        'angle',
        'related_ebook_id',
        'status',
        'generated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = ['approved_at' => 'datetime'];

    /** @return BelongsTo<Ebook, $this> */
    public function relatedEbook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class, 'related_ebook_id');
    }

    /** @return HasMany<BlogGenerationRun, $this> */
    public function generationRuns(): HasMany
    {
        return $this->hasMany(BlogGenerationRun::class, 'blog_topic_id');
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_CURRENT_AFFAIRS,
            self::TYPE_EBOOK_INSPIRED,
            self::TYPE_OPINION,
            self::TYPE_PRACTICAL,
            self::TYPE_RESEARCH,
        ];
    }
}
