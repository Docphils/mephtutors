<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['type', 'ebook_id', 'order_id', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign', 'meta', 'created_at'];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(EbookOrder::class, 'order_id');
    }
}
