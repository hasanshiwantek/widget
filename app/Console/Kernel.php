<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        Commands\FetchTrustpilotReviews::class, // Register your custom command here
        Commands\UpdateStatsTable::class,
        Commands\FetchServerblinkReviews::class, // Register your custom command here
        Commands\UpdateServerblinkStats::class,
    ];

    
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('fetch:trustpilot-reviews')->weeklyOn(1, '01:00');
        $schedule->command('update:stats')->weeklyOn(1, '02:00');
        $schedule->command('fetch:serverblink-reviews')->weeklyOn(1, '01:00');
        $schedule->command('update:serverblink-stats')->weeklyOn(1, '02:00');

        $schedule->call(function () {
            \Log::info('Scheduler is running!');
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
