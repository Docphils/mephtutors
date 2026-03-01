<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user(){
        return $this->belongsTo(User::class);
    }
}
