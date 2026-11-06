<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogFacebookShare extends Model
{
    public const STATUS_MANUAL = 'manual';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PUBLISHING = 'publishing';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_FAILED = 'failed';

    public const STATUS_UNCERTAIN = 'uncertain';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'blog_post_id',
        'status',
        'meta_post_id',
        'error_message',
        'attempt_count',
        'last_attempt_at',
        'published_at',
    ];

    protected $casts = [
        'attempt_count' => 'integer',
        'last_attempt_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /** @return BelongsTo<BlogPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
