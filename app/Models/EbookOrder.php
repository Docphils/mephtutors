<?php

namespace App\Models;

use App\Enums\EbookOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EbookOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'ebook_id', 'buyer_name', 'buyer_email', 'buyer_phone', 'amount_kobo', 'currency',
        'status', 'paystack_reference', 'paid_at', 'fulfilled_at', 'fulfilled_pdf_path', 'referrer', 'utm_source',
        'utm_medium', 'utm_campaign',
    ];

    protected $casts = [
        'amount_kobo' => 'integer',
        'status' => EbookOrderStatus::class,
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    /** @return BelongsTo<Ebook, $this> */
    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class);
    }

    /** @return HasOne<EbookDownload, $this> */
    public function download(): HasOne
    {
        return $this->hasOne(EbookDownload::class, 'order_id');
    }

    /** @return HasMany<EbookEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(EbookEvent::class, 'order_id');
    }
}
