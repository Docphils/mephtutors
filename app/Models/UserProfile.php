<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class UserProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'phone',
        'state',
        'city',
        'address',
        'DOB',
        'image',
        'gender',
    ];

    protected $casts = [
        'DOB' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
