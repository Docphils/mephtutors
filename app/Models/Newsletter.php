<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'title',
        'body',
        'body2',
        'recipients',
        'status',
        'sent_to',
        'attachments',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'sent_to' => 'array',
        'sent_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
