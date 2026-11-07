<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogGenerationRun extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_AWAITING_TOPIC = 'awaiting_topic';

    protected $fillable = [
        'run_key',
        'scheduled_for',
        'status',
        'blog_post_id',
        'blog_topic_id',
        'word_target',
        'author_id',
        'provider',
        'metadata',
        'error_message',
        'notification_status',
        'notification_error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'scheduled_for' => 'date',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /** @return BelongsTo<BlogPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    /** @return BelongsTo<BlogTopic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(BlogTopic::class, 'blog_topic_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
