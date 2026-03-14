<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ScheduleExam; // Update with your model's namespace
use Carbon\Carbon;

class UpdateScheduleStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get the current date and time
        $currentDate = Carbon::now();

        // Query schedules where the completion_date has passed and schedule_status is null
        $schedules = ScheduleExam::whereNull('schedule_status')
            ->where('completion_date', '<=', $currentDate)
            ->get();

        foreach ($schedules as $schedule) {
            // Update the schedule_status to 0
            $schedule->update(['schedule_status' => 0]);
        }
    }
}
