<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurchLeadActivity extends Model
{
    protected $fillable = ['user_id', 'type', 'description', 'properties'];
    protected $casts = ['properties' => 'array'];
    public function lead(): BelongsTo { return $this->belongsTo(ChurchLead::class, 'church_lead_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
