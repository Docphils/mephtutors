<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'fullName',
        'address',
        'state',
        'city',
        'DOB',
        'image',
        'gender',
        'qualification',
        'experience',
        'CV',
        'discipline',
        'careerProfile',
        'bankName',
        'accountName',
        'accountNumber',
        'status',
        'approvalRemark',
        'video',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
