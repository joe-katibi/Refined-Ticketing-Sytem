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
      $schedule->job(new \App\Jobs\UpdateScheduleStatusJob)->everyMinute();
      
      // Clean up old report downloads every 8 hours
      $schedule->command('reports:cleanup --hours=8')->cron('0 */8 * * *');

      // FIFO auto-dispatch — hands out any waiting ticket to an available agent.
      $schedule->command('fifo:dispatch')->everyFiveMinutes();
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
