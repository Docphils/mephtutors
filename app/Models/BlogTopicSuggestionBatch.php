<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogTopicSuggestionBatch extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'batch_key',
        'status',
        'topic_type',
        'requested_count',
        'requested_by',
        'error_message',
        'finished_at',
    ];

    protected $casts = ['finished_at' => 'datetime'];
}
