<?php
/**
 * Escalations Module Test Script
 * 
 * This script helps test the Escalations module functionality including:
 * - SLA tracking
 * - Dashboard metrics
 * - Notification system
 * 
 * Usage: php test-escalations.php [action] [options]
 */

// Bootstrap Laravel
require __DIR__ . '/../../bootstrap/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

// Parse command line arguments
$action = $argv[1] ?? 'help';
$options = array_slice($argv, 2);

// Set a default user for testing
$userId = 1; // Change this to an actual user ID in your system

// Process the action
switch ($action) {
    case 'create-test-data':
        createTestData($userId);
        break;
        
    case 'test-notifications':
        testNotifications($userId);
        break;
        
    case 'test-dashboard':
        testDashboard();
        break;
        
    case 'test-sla':
        testSla();
        break;
        
    case 'help':
    default:
        showHelp();
        break;
}

/**
 * Create test data for the Escalations module
 */
function createTestData($userId) {
    echo "Creating test escalations data...\n";
    
    // Create escalations with different statuses and SLA conditions
    $statuses = ['Escalated-Open', 'Escalated-Closed', 'Scheduled-Open', 'Scheduled-Closed'];
    $subDepts = [1, 2, 3]; // Replace with actual sub-department IDs
    
    // Create escalations in the past (some breached, some not)
    for ($i = 0; $i < 20; $i++) {
        $status = $statuses[array_rand($statuses)];
        $subDept = $subDepts[array_rand($subDepts)];
        $createdAt = Carbon::now()->subHours(rand(1, 48));
        $slaDeadline = (clone $createdAt)->addMinutes(5);
        
        // Determine if closed and when
        $isClosed = strpos($status, 'Closed') !== false;
        $closedAt = null;
        $slaBreached = false;
        
        if ($isClosed) {
            // 70% chance to close within SLA, 30% chance to breach
            $withinSla = (rand(1, 10) <= 7);
            
            if ($withinSla) {
                $closedAt = (clone $createdAt)->addMinutes(rand(1, 4))->addSeconds(rand(0, 59));
            } else {
                $closedAt = (clone $slaDeadline)->addMinutes(rand(1, 60));
                $slaBreached = true;
            }
        }
        
        $escalation = new Escalation();
        $escalation->escalation_id = 'TEST-' . time() . '-' . $i;
        $escalation->ticket_id = 'TEST-TICKET-' . time() . '-' . $i;
        $escalation->description = 'Test escalation #' . $i;
        $escalation->priority = ['Low', 'Medium', 'High'][array_rand(['Low', 'Medium', 'High'])];
        $escalation->status = $status;
        $escalation->created_by = $userId;
        $escalation->assigned_to = $userId;
        $escalation->sub_department_id = $subDept;
        $escalation->sla_deadline = $slaDeadline;
        $escalation->sla_breached = $slaBreached;
        $escalation->closed_at = $closedAt;
        $escalation->closed_by = $isClosed ? $userId : null;
        
        // Override timestamps for testing
        $escalation->created_at = $createdAt;
        $escalation->updated_at = $isClosed ? $closedAt : $createdAt;
        
        $escalation->save();
        echo "Created escalation {$escalation->escalation_id} with status {$status}" . 
             ($isClosed ? " (closed " . ($slaBreached ? "outside" : "within") . " SLA)" : "") . "\n";
    }
    
    // Create a few current open escalations
    for ($i = 0; $i < 5; $i++) {
        $subDept = $subDepts[array_rand($subDepts)];
        $createdAt = Carbon::now()->subMinutes(rand(1, 4));
        
        $escalation = new Escalation();
        $escalation->escalation_id = 'TEST-CURRENT-' . time() . '-' . $i;
        $escalation->ticket_id = 'TEST-TICKET-CURRENT-' . time() . '-' . $i;
        $escalation->description = 'Current test escalation #' . $i;
        $escalation->priority = ['Low', 'Medium', 'High'][array_rand(['Low', 'Medium', 'High'])];
        $escalation->status = 'Escalated-Open';
        $escalation->created_by = $userId;
        $escalation->assigned_to = $userId;
        $escalation->sub_department_id = $subDept;
        $escalation->sla_deadline = (clone $createdAt)->addMinutes(5);
        
        // Override timestamps for testing
        $escalation->created_at = $createdAt;
        $escalation->updated_at = $createdAt;
        
        $escalation->save();
        echo "Created current open escalation {$escalation->escalation_id}\n";
    }
    
    echo "Test data creation complete!\n";
}

/**
 * Test the notification system
 */
function testNotifications($userId) {
    echo "Testing notification system...\n";
    
    // Get the notification service
    $notificationService = app(NotificationService::class);
    
    // Get or create a test escalation
    $escalation = Escalation::where('created_by', $userId)->first();
    
    if (!$escalation) {
        echo "No escalation found for user $userId. Creating one...\n";
        
        $escalation = new Escalation();
        $escalation->escalation_id = 'TEST-NOTIF-' . time();
        $escalation->ticket_id = 'TEST-TICKET-NOTIF-' . time();
        $escalation->description = 'Test escalation for notifications';
        $escalation->priority = 'High';
        $escalation->status = 'Escalated-Open';
        $escalation->created_by = $userId;
        $escalation->assigned_to = $userId;
        $escalation->sla_deadline = Carbon::now()->addMinutes(5);
        $escalation->save();
    }
    
    echo "Using escalation {$escalation->escalation_id} for notification tests\n";
    
    // Create all types of notifications
    $notificationService->notifyCreated($escalation);
    echo "Created 'escalation created' notification\n";
    
    $notificationService->notifyUpdated($escalation);
    echo "Created 'escalation updated' notification\n";
    
    $notificationService->notifyAssigned($escalation);
    echo "Created 'escalation assigned' notification\n";
    
    $notificationService->notifySlaWarning($escalation, 60);
    echo "Created 'SLA warning' notification\n";
    
    $notificationService->notifySlaBreached($escalation);
    echo "Created 'SLA breach' notification\n";
    
    $notificationService->notifyClosed($escalation);
    echo "Created 'escalation closed' notification\n";
    
    echo "Notification testing complete! Check the UI to see the notifications.\n";
}

/**
 * Test the dashboard metrics
 */
function testDashboard() {
    echo "Testing dashboard metrics...\n";
    
    // Get counts from the database
    $totalEscalations = Escalation::count();
    $openEscalations = Escalation::whereIn('status', ['Escalated-Open', 'Scheduled-Open'])->count();
    $closedEscalations = Escalation::whereIn('status', ['Escalated-Closed', 'Scheduled-Closed'])->count();
    $closedWithinSla = Escalation::whereIn('status', ['Escalated-Closed', 'Scheduled-Closed'])
                                 ->where('sla_breached', false)
                                 ->count();
    $closedOutsideSla = Escalation::whereIn('status', ['Escalated-Closed', 'Scheduled-Closed'])
                                  ->where('sla_breached', true)
                                  ->count();
    
    // Calculate SLA compliance percentage
    $slaCompliancePercent = $closedEscalations > 0 
        ? round(($closedWithinSla / $closedEscalations) * 100, 2) 
        : 0;
    
    // Display metrics
    echo "Dashboard Metrics:\n";
    echo "----------------\n";
    echo "Total Escalations: $totalEscalations\n";
    echo "Open Escalations: $openEscalations\n";
    echo "Closed Escalations: $closedEscalations\n";
    echo "Closed Within SLA: $closedWithinSla\n";
    echo "Closed Outside SLA: $closedOutsideSla\n";
    echo "SLA Compliance: $slaCompliancePercent%\n";
    echo "\n";
    
    // Get metrics per sub-department
    $subDepartments = Escalation::select('sub_department_id')
                               ->distinct()
                               ->whereNotNull('sub_department_id')
                               ->get()
                               ->pluck('sub_department_id');
    
    echo "Metrics per Sub-Department:\n";
    echo "-------------------------\n";
    
    foreach ($subDepartments as $subDeptId) {
        $totalForDept = Escalation::where('sub_department_id', $subDeptId)->count();
        $openForDept = Escalation::where('sub_department_id', $subDeptId)
                                ->whereIn('status', ['Escalated-Open', 'Scheduled-Open'])
                                ->count();
        $closedForDept = Escalation::where('sub_department_id', $subDeptId)
                                  ->whereIn('status', ['Escalated-Closed', 'Scheduled-Closed'])
                                  ->count();
        $closedWithinSlaForDept = Escalation::where('sub_department_id', $subDeptId)
                                          ->whereIn('status', ['Escalated-Closed', 'Scheduled-Closed'])
                                          ->where('sla_breached', false)
                                          ->count();
        $closedOutsideSlaForDept = Escalation::where('sub_department_id', $subDeptId)
                                           ->whereIn('status', ['Escalated-Closed', 'Scheduled-Closed'])
                                           ->where('sla_breached', true)
                                           ->count();
        
        $slaCompliancePercentForDept = $closedForDept > 0 
            ? round(($closedWithinSlaForDept / $closedForDept) * 100, 2) 
            : 0;
        
        echo "Sub-Department ID: $subDeptId\n";
        echo "  Total: $totalForDept\n";
        echo "  Open: $openForDept\n";
        echo "  Closed: $closedForDept\n";
        echo "  Closed Within SLA: $closedWithinSlaForDept\n";
        echo "  Closed Outside SLA: $closedOutsideSlaForDept\n";
        echo "  SLA Compliance: $slaCompliancePercentForDept%\n";
        echo "\n";
    }
    
    echo "Dashboard testing complete! Check the dashboard UI to see the metrics.\n";
}

/**
 * Test the SLA calculation and tracking
 */
function testSla() {
    echo "Testing SLA calculation and tracking...\n";
    
    // Create an escalation that will breach SLA soon
    $escalation = new Escalation();
    $escalation->escalation_id = 'TEST-SLA-' . time();
    $escalation->ticket_id = 'TEST-TICKET-SLA-' . time();
    $escalation->description = 'Test escalation for SLA tracking';
    $escalation->priority = 'High';
    $escalation->status = 'Escalated-Open';
    $escalation->created_by = 1;
    $escalation->assigned_to = 1;
    
    // Set SLA deadline to 30 seconds from now
    $escalation->created_at = Carbon::now()->subMinutes(4)->subSeconds(30);
    $escalation->sla_deadline = Carbon::now()->addSeconds(30);
    
    $escalation->save();
    
    echo "Created escalation {$escalation->escalation_id} with SLA deadline in 30 seconds\n";
    echo "Run the SLA checker to test breach detection:\n";
    echo "  php artisan escalations:check-sla\n";
    
    // Wait and then close the escalation after SLA breach
    echo "Waiting 35 seconds to breach SLA...\n";
    sleep(35);
    
    // Close the escalation (should be marked as breached)
    $escalation->status = 'Escalated-Closed';
    $escalation->closed_at = Carbon::now();
    $escalation->closed_by = 1;
    $escalation->save();
    
    // Reload the escalation to check SLA breach status
    $escalation = Escalation::find($escalation->id);
    
    echo "Closed escalation after SLA deadline\n";
    echo "SLA breached: " . ($escalation->sla_breached ? "Yes" : "No") . "\n";
    
    echo "SLA testing complete!\n";
}

/**
 * Show help information
 */
function showHelp() {
    echo "Escalations Module Test Script\n";
    echo "=============================\n\n";
    echo "Usage: php test-escalations.php [action] [options]\n\n";
    echo "Available actions:\n";
    echo "  create-test-data   - Create test escalations with various statuses and SLA conditions\n";
    echo "  test-notifications - Test the notification system\n";
    echo "  test-dashboard     - Test the dashboard metrics\n";
    echo "  test-sla           - Test the SLA calculation and tracking\n";
    echo "  help               - Show this help information\n\n";
}
