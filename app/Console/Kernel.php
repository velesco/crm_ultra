<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Gmail auto-sync every 5 minutes
        $schedule->command('gmail:auto-sync')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer();

        // Optional: More frequent sync during business hours
        $schedule->command('gmail:auto-sync --max-results=20')
                 ->everyTwoMinutes()
                 ->between('08:00', '18:00')
                 ->weekdays()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onOneServer();
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
