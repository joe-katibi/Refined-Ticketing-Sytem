<?php
require __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Escalations\App\Models\EscalationHistory;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Entities\EscalationList;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set up error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "===== COMPREHENSIVE ESCALATION FIX VERIFICATION =====\n\n";

try {
    // Find a user to authenticate
    $user = \App\Models\User::first();
    if (!$user) {
        throw new Exception("No users found in the database");
    }
    
    // Authenticate the user
    Auth::login($user);
    echo "Authenticated as user: " . $user->name . " (ID: " . $user->id . ")\n\n";
    
    // PART 1: Verify status column length in lists and escalations tables
    echo "PART 1: VERIFYING STATUS COLUMN LENGTH\n";
    echo "--------------------------------------\n";
    
    // Check lists table
    $listsStatusColumn = DB::select("SHOW COLUMNS FROM lists WHERE Field = 'status'");
    if (empty($listsStatusColumn)) {
        echo "ERROR: Status column not found in lists table!\n";
    } else {
        $listsStatusType = $listsStatusColumn[0]->Type;
        echo "Lists table status column type: " . $listsStatusType . "\n";
        
        // Check if the length is at least 50
        if (preg_match('/varchar\((\d+)\)/i', $listsStatusType, $matches)) {
            $length = (int)$matches[1];
            echo "Lists table status column length: " . $length . " characters\n";
            if ($length >= 50) {
                echo "✓ Lists table status column length is sufficient (>= 50 characters)\n";
            } else {
                echo "✗ Lists table status column length is insufficient (< 50 characters)\n";
            }
        }
    }
    
    // Check escalations table
    $escalationsStatusColumn = DB::select("SHOW COLUMNS FROM escalations WHERE Field = 'status'");
    if (empty($escalationsStatusColumn)) {
        echo "ERROR: Status column not found in escalations table!\n";
    } else {
        $escalationsStatusType = $escalationsStatusColumn[0]->Type;
        echo "Escalations table status column type: " . $escalationsStatusType . "\n";
        
        // Check if the length is at least 50
        if (preg_match('/varchar\((\d+)\)/i', $escalationsStatusType, $matches)) {
            $length = (int)$matches[1];
            echo "Escalations table status column length: " . $length . " characters\n";
            if ($length >= 50) {
                echo "✓ Escalations table status column length is sufficient (>= 50 characters)\n";
            } else {
                echo "✗ Escalations table status column length is insufficient (< 50 characters)\n";
            }
        }
    }
    
    echo "\n";
    
    // PART 2: Test status update with longer status value
    echo "PART 2: TESTING STATUS UPDATE WITH LONGER VALUE\n";
    echo "----------------------------------------------\n";
    
    // Find an existing escalation to work with
    $escalation = Escalation::first();
    if (!$escalation) {
        throw new Exception("No escalations found in the database");
    }
    
    echo "Found escalation with ID: " . $escalation->id . " and ticket ID: " . $escalation->ticket_id . "\n";
    
    // Find the corresponding escalation list
    $escalationList = EscalationList::where('ticket_id', $escalation->ticket_id)->first();
    if (!$escalationList) {
        throw new Exception("No escalation list found for ticket ID: " . $escalation->ticket_id);
    }
    
    echo "Found escalation list with ID: " . $escalationList->id . "\n";
    
    // Save original status for restoration later
    $originalEscalationStatus = $escalation->status;
    $originalListStatus = $escalationList->status;
    
    // Update with a longer status value
    $longStatus = "Scheduled-Open-Pending-Review-Extended";
    
    echo "Updating status to longer value: " . $longStatus . "\n";
    
    try {
        // Update the escalation
        $escalation->update([
            'status' => $longStatus,
            'edited_by' => $user->id,
        ]);
        
        // Update the escalation list
        $escalationList->update([
            'status' => $longStatus,
            'edited_by' => $user->id,
        ]);
        
        // Refresh from database
        $escalation->refresh();
        $escalationList->refresh();
        
        // Check if the status was updated correctly
        if ($escalation->status === $longStatus && $escalationList->status === $longStatus) {
            echo "✓ Status updated successfully to longer value without truncation\n";
        } else {
            echo "✗ Status update failed or was truncated\n";
            echo "  - Escalation status: " . $escalation->status . "\n";
            echo "  - EscalationList status: " . $escalationList->status . "\n";
        }
    } catch (Exception $e) {
        echo "✗ Error updating status: " . $e->getMessage() . "\n";
    }
    
    // Restore original status
    $escalation->update(['status' => $originalEscalationStatus]);
    $escalationList->update(['status' => $originalListStatus]);
    echo "Restored original status values\n";
    
    echo "\n";
    
    // PART 3: Verify escalation_histories table structure
    echo "PART 3: VERIFYING ESCALATION_HISTORIES TABLE STRUCTURE\n";
    echo "----------------------------------------------------\n";
    
    // Check if the table exists
    $tableExists = DB::select("SHOW TABLES LIKE 'escalation_histories'");
    if (empty($tableExists)) {
        echo "ERROR: Table 'escalation_histories' does not exist!\n";
    } else {
        echo "✓ Table 'escalation_histories' exists\n";
        
        // Get table columns
        $columns = DB::select("SHOW COLUMNS FROM escalation_histories");
        $columnNames = array_map(function($column) {
            return $column->Field;
        }, $columns);
        
        // Check for required columns
        $requiredColumns = [
            'support_address', 'support_date', 'support_time', 'support_notes',
            'shifting_address', 'shifting_date', 'shifting_time', 'shifting_notes',
            'installation_address', 'installation_date', 'installation_time', 'installation_notes',
            'wifi_extender_address', 'wifi_extender_date', 'wifi_extender_time', 'wifi_extender_notes'
        ];
        
        $missingColumns = array_diff($requiredColumns, $columnNames);
        
        if (empty($missingColumns)) {
            echo "✓ All required columns exist in escalation_histories table\n";
        } else {
            echo "✗ Missing columns in escalation_histories table: " . implode(', ', $missingColumns) . "\n";
        }
    }
    
    echo "\n";
    
    // PART 4: Test creating an escalation history record with appointment fields
    echo "PART 4: TESTING ESCALATION HISTORY CREATION WITH APPOINTMENT FIELDS\n";
    echo "----------------------------------------------------------------\n";
    
    try {
        // Create a history record with appointment fields
        $historyData = [
            'escalation_id' => $escalation->id,
            'ticket_id' => $escalation->ticket_id,
            'status' => 'in_progress',
            'action_by' => $user->id,
            'support_date' => '2025-08-22',
            'support_time' => '14:00',
            'support_address' => '123 Test Street, City',
            'support_notes' => 'Test support notes',
        ];
        
        $history = EscalationHistory::create($historyData);
        echo "✓ Successfully created history record with ID: " . $history->id . "\n";
        
        // Verify the history record was created correctly
        $verifyHistory = EscalationHistory::find($history->id);
        if ($verifyHistory) {
            echo "✓ History record retrieved successfully\n";
            echo "  - Support date: " . $verifyHistory->support_date . "\n";
            echo "  - Support time: " . $verifyHistory->support_time . "\n";
            echo "  - Support address: " . $verifyHistory->support_address . "\n";
        } else {
            echo "✗ Failed to retrieve history record\n";
        }
    } catch (Exception $e) {
        echo "✗ Error creating history record: " . $e->getMessage() . "\n";
    }
    
    echo "\n===== TEST COMPLETED SUCCESSFULLY =====\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
