<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogTavilyMonthlyUsage extends Model
{
    protected $fillable = ['month_key', 'credits_used', 'last_used_at'];

    protected $casts = ['last_used_at' => 'datetime'];
}
