<?php

namespace Modules\Escalations\Console\Commands;

use Illuminate\Console\Command;
use Modules\Escalations\Entities\EscalationNotification;

class CheckNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escalations:check-notifications {--user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check notifications in the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get all notifications
        $notifications = EscalationNotification::all();

        $this->info("Found " . $notifications->count() . " notifications in the database:");
        $this->line("");

        foreach ($notifications as $notification) {
            $this->line("ID: {$notification->id}");
            $this->line("User ID: {$notification->user_id}");
            $this->line("Escalation ID: {$notification->escalation_id}");
            $this->line("Type: {$notification->type}");
            $this->line("Message: {$notification->message}");
            $this->line("Read: " . ($notification->read ? 'Yes' : 'No'));
            $this->line("Created: {$notification->created_at}");
            $this->line("-----------------------------------");
        }

        // Check for notifications for specific user
        $userId = $this->option('user');
        if ($userId) {
            $userNotifications = EscalationNotification::where('user_id', $userId)->get();

            $this->info("\nFound " . $userNotifications->count() . " notifications for user ID {$userId}:");
            $this->line("");

            foreach ($userNotifications as $notification) {
                $this->line("ID: {$notification->id}");
                $this->line("Type: {$notification->type}");
                $this->line("Message: {$notification->message}");
                $this->line("Created: {$notification->created_at}");
                $this->line("-----------------------------------");
            }
        }

        return 0;
    }
}
