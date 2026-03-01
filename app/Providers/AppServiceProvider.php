<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\TutorProfile;
use App\Observers\BookingObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
        $tutorProfile = auth()->user()?->tutorProfile;
        $userProfile = auth()->user()?->userProfile;

        $incompleteTutorProfile = false;
        if ($tutorProfile) {
            $requiredFields = [
                'phone', 'fullName', 'address', 'state', 'city', 'DOB', 'image',
                'gender', 'qualification', 'experience', 'CV', 'discipline',
                'careerProfile', 'bankName', 'accountName', 'accountNumber', 'video',
            ];

            foreach ($requiredFields as $field) {
                if (empty($tutorProfile->$field)) {
                    $incompleteTutorProfile = true;
                    break;
                }
            }
        }

        
        $incompleteProfile = false;
        if ($userProfile) {
            $requiredFields = [
                'phone', 'address', 'state', 'city', 'DOB', 'image', 'gender',
            ];

            foreach ($requiredFields as $field) {
                if (empty($userProfile->$field)) {
                    $incompleteProfile = true;
                    break;
                }
            }
        }

        $approvedTutorProfile = $tutorProfile && $tutorProfile->status === 'Approved';

        $view->with([
            'tutorProfile' => $tutorProfile,
            'incompleteTutorProfile' => $incompleteTutorProfile,
            'approvedTutorProfile' => $approvedTutorProfile,
            'userProfile' => $userProfile,
            'incompleteProfile' => $incompleteProfile,
            ]);
        });

        Blade::if('notAuth', function () {
            $route = Route::current();
            if (!$route) return true;

            $middleware = $route->middleware();
            return !in_array('auth', $middleware);
        });
    
    }

}
