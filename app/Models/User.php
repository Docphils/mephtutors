<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
     * @var list<string>
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

    /** @return HasOne<UserProfile, $this> */
    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /** @return HasOne<TutorProfile, $this> */
    public function tutorProfile(): HasOne
    {
        return $this->hasOne(TutorProfile::class);
    }

    /** @return HasMany<TutorRequest, $this> */
    public function tutorRequests(): HasMany
    {
        return $this->hasMany(TutorRequest::class);
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** @return HasMany<Crm, $this> */
    public function crms(): HasMany
    {
        return $this->hasMany(Crm::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<Testimonial, $this> */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    // TUTOR MATCHES
    /** @return HasMany<TutorMatch, $this> */
    public function tutorMatches(): HasMany
    {
        return $this->hasMany(TutorMatch::class, 'tutor_id');
    }

    // Accepted tutoring sessions
    /** @return HasMany<TutorMatch, $this> */
    public function acceptedTutorMatches(): HasMany
    {
        return $this->hasMany(TutorMatch::class, 'tutor_id')
            ->where('status', 'accepted');
    }

    /** @return HasMany<CrmAssignment, $this> */
    public function crmAssignments(): HasMany
    {
        return $this->hasMany(CrmAssignment::class, 'user_id');
    }

    /** @return HasMany<OnlineMeeting, $this> */
    public function scheduledOnlineMeetings(): HasMany
    {
        return $this->hasMany(OnlineMeeting::class, 'scheduled_by');
    }

    /** @return HasMany<OnlineMeeting, $this> */
    public function onlineMeetingsAsClient(): HasMany
    {
        return $this->hasMany(OnlineMeeting::class, 'client_id');
    }

    /** @return HasMany<OnlineMeeting, $this> */
    public function onlineMeetingsAsTutor(): HasMany
    {
        return $this->hasMany(OnlineMeeting::class, 'tutor_id');
    }

    /** @return HasMany<OnlineMeetingAttendance, $this> */
    public function onlineMeetingAttendances(): HasMany
    {
        return $this->hasMany(OnlineMeetingAttendance::class);
    }

    /** @return HasMany<ProgrammeEnquiry, $this> */
    public function programmeEnquiries(): HasMany
    {
        return $this->hasMany(ProgrammeEnquiry::class);
    }

    /** @return HasMany<ProgrammeEnquiryAssignment, $this> */
    public function programmeAssignmentsAsTutor(): HasMany
    {
        return $this->hasMany(ProgrammeEnquiryAssignment::class, 'tutor_id');
    }
}
