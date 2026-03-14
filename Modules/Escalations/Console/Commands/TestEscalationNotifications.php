<?php

namespace Modules\Escalations\Console\Commands;

use Illuminate\Console\Command;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Services\NotificationService;
use Carbon\Carbon;

class TestEscalationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escalations:test-notifications {--user=} {--type=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test escalation notifications by creating sample notifications';

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
        $userId = $this->option('user');
        $type = $this->option('type');
        
        if (!$userId) {
            $this->error('Please provide a user ID with --user option');
            return 1;
        }
        
        $this->info('Creating test notifications for user ID: ' . $userId);
        
        // Get a sample escalation (or create one if none exists)
        $escalation = $this->getSampleEscalation($userId);
        
        if (!$escalation) {
            $this->error('Could not find or create a sample escalation');
            return 1;
        }
        
        $this->info('Using escalation ID: ' . $escalation->escalation_id);
        
        // Create notifications based on type
        if ($type === 'all' || $type === 'created') {
            $this->notificationService->notifyCreation($escalation);
            $this->info('Created "escalation created" notification');
        }
        
        if ($type === 'all' || $type === 'updated') {
            $this->notificationService->notifyUpdate($escalation, ['status' => 'Escalated-Open']);
            $this->info('Created "escalation updated" notification');
        }
        
        if ($type === 'all' || $type === 'closed') {
            $this->notificationService->notifyClosing($escalation);
            $this->info('Created "escalation closed" notification');
        }
        
        if ($type === 'all' || $type === 'assigned') {
            // Assignment is handled by notifyCreation, but we'll call it again to create an assignment notification
            $escalation->assigned_to = $userId; // Ensure assigned_to is set
            $this->notificationService->notifyCreation($escalation);
            $this->info('Created "escalation assigned" notification');
        }
        
        if ($type === 'all' || $type === 'sla_warning') {
            $this->notificationService->notifySlaWarning($escalation, 60);
            $this->info('Created "SLA warning" notification');
        }
        
        if ($type === 'all' || $type === 'sla_breach') {
            $this->notificationService->notifySlaBreached($escalation);
            $this->info('Created "SLA breach" notification');
        }
        
        $this->info('Test notifications created successfully!');
        $this->info('Check the notifications UI to see the results.');
        
        return 0;
    }
    
    /**
     * Get a sample escalation for testing
     *
     * @param int $userId
     * @return Escalation|null
     */
    protected function getSampleEscalation($userId)
    {
        // Try to find an existing escalation
        $escalation = Escalation::where('created_by', $userId)
                               ->orWhere('assigned_to', $userId)
                               ->first();
        
        // If no escalation found, create a test one
        if (!$escalation) {
            $this->info('No existing escalation found, creating a test escalation');
            
            $escalation = new Escalation();
            $escalation->escalation_id = 'TEST-' . time();
            $escalation->ticket_id = 'TEST-TICKET-' . time();
            $escalation->description = 'Test escalation for notification testing';
            $escalation->priority = 'High';
            $escalation->status = 'Escalated-Open';
            $escalation->created_by = $userId;
            $escalation->assigned_to = $userId;
            $escalation->sla_deadline = Carbon::now()->addMinutes(5);
            
            try {
                $escalation->save();
                $this->info('Test escalation created successfully');
            } catch (\Exception $e) {
                $this->error('Error creating test escalation: ' . $e->getMessage());
                return null;
            }
        }
        
        return $escalation;
    }
}
