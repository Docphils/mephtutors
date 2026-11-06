<?php

namespace App\Console;

use App\Models\EbookTaskRun;
use App\Models\User;
use App\Services\BlogGenerationWorkflow;
use App\Services\EbookAlertService;
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
        $schedule->command('ebooks:reconcile-payments')->everyTenMinutes()->withoutOverlapping();
        $schedule->call(fn () => app(BlogGenerationWorkflow::class)->dispatchScheduled())
            ->everyMinute()
            ->timezone(config('blog.timezone', 'Africa/Lagos'))
            ->name('blog:daily-draft-dispatch');
        $schedule->call(function (): void {
            $threshold = now()->subMinutes(config('ebooks.reconciliation_minutes', 10) * 3);
            $last = EbookTaskRun::query()->where('task', 'reconciliation')->first();
            if (! $last || ! $last->finished_at || $last->finished_at->lt($threshold)) {
                app(EbookAlertService::class)->sendOnce('scheduler-stale', 'eBook scheduler appears stale', 'The eBook reconciliation task has not completed within the expected interval. Confirm the host scheduler is running.');
            }
        })->everyTenMinutes()->name('ebooks:scheduler-watch')->withoutOverlapping();

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
