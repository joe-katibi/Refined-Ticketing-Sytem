<?php

namespace Modules\Escalations\Console\Commands;

use Illuminate\Console\Command;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Services\NotificationService;
use Carbon\Carbon;

class CheckEscalationSla extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escalations:check-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for escalations that are about to breach or have already breached their SLA';

    /**
     * The notification service instance.
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     *
     * @param NotificationService $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for escalations approaching or breaching SLA...');

        // Get open escalations
        $openEscalations = Escalation::open()->get();
        
        $now = Carbon::now();
        $warningThreshold = 60; // seconds before SLA breach to send warning
        
        $breachedCount = 0;
        $warningCount = 0;

        foreach ($openEscalations as $escalation) {
            // Skip if already notified about breach
            if ($escalation->sla_breach_notified) {
                continue;
            }

            // Calculate seconds until SLA breach
            $secondsUntilBreach = $now->diffInSeconds($escalation->sla_deadline, false);
            
            // If already breached
            if ($secondsUntilBreach <= 0) {
                // Send breach notification
                $this->notificationService->notifySlaBreached($escalation);
                
                // Mark as notified
                $escalation->update(['sla_breach_notified' => true]);
                
                $breachedCount++;
                $this->info("Escalation {$escalation->escalation_id} has breached SLA.");
            } 
            // If approaching breach
            elseif ($secondsUntilBreach <= $warningThreshold) {
                // Send warning notification
                $this->notificationService->notifySlaWarning($escalation, $secondsUntilBreach);
                
                $warningCount++;
                $this->info("Escalation {$escalation->escalation_id} is approaching SLA breach ({$secondsUntilBreach} seconds remaining).");
            }
        }

        $this->info("SLA check completed: {$breachedCount} breached, {$warningCount} approaching breach.");
        
        return 0;
    }
}
