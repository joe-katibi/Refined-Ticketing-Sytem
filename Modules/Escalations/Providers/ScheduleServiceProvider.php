<?php

namespace Modules\Escalations\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Run SLA checker every minute
            $schedule->command('escalations:check-sla')
                     ->everyMinute()
                     ->withoutOverlapping()
                     ->appendOutputTo(storage_path('logs/escalation-sla-checker.log'));
        });
    }
}
