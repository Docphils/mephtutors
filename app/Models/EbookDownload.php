<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookDownload extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'token_hash', 'token_generation', 'expires_at', 'max_downloads', 'download_count', 'last_downloaded_at', 'created_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'created_at' => 'datetime',
        'max_downloads' => 'integer',
        'token_generation' => 'integer',
        'download_count' => 'integer',
    ];

    /** @return BelongsTo<EbookOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(EbookOrder::class, 'order_id');
    }
}
