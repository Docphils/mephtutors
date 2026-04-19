<?php

namespace App\Console;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('bookings:close-completed')->everySixHours();
        $schedule->command('interventions:close-completed')->everyFiveMinutes();
        $schedule->command('sessions:close-completed')->everyFiveMinutes();

        // $schedule->call(function(){
        //     User::whereNull('email_verified_at')->delete();
        // })->monthly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
