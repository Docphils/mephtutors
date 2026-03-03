<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserProfile;
use App\Models\TutorProfile;
use App\Models\Booking;
use App\Models\TutorRequest;
use App\Models\Crm;
use App\Models\Payment;
use App\Models\CrmAssignment;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_subscribed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function tutorProfile(){
        return $this->hasOne(TutorProfile::class);
    }

    public function tutorRequests(){
        return $this->hasMany(TutorRequest::class);
    }

    public function bookings(){
        return $this->hasMany(Booking::class);
    }

    public function crms(){
        return $this->hasMany(Crm::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function testimonials(){
        return $this->hasMany(Testimonial::class);
    }

    // TUTOR MATCHES
    public function tutorMatches()
    {
        return $this->hasMany(TutorMatch::class, 'tutor_id');
    }

    // Accepted tutoring sessions
    public function acceptedTutorMatches()
    {
        return $this->hasMany(TutorMatch::class, 'tutor_id')
            ->where('status', 'accepted');
    }

    public function crmAssignments()
    {
        return $this->hasMany(CrmAssignment::class, 'user_id');
    }

}
