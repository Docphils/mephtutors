<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeTransaction extends Model
{
    protected $fillable = [
        'source_type',
        'source_id',
        'description',
        'payer_name',
        'amount',
        'currency',
        'method',
        'reference',
        'gateway_reference',
        'received_at',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
