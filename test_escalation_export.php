<?php

// Bootstrap Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import necessary classes
use Modules\Escalations\Exports\EscalationReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

// Create a log file for this test
$logFile = storage_path('logs/escalation_export_test.log');
File::put($logFile, "=== EscalationReportExport Test Started at " . date('Y-m-d H:i:s') . " ===\n");

function logMessage($message) {
    global $logFile;
    echo $message;
    File::append($logFile, $message);
}

try {
    logMessage("Testing EscalationReportExport...\n");
    
    // Set date range for testing
    $dateFrom = '2025-08-01';
    $dateTo = '2025-08-20';
    
    // Test different report types
    $reportTypes = ['escalations', 'sla', 'productivity', 'sub_category'];
    
    // First, check if the EscalationReportExport class exists
    if (!class_exists('Modules\\Escalations\\Exports\\EscalationReportExport')) {
        logMessage("ERROR: EscalationReportExport class does not exist!\n");
        exit(1);
    } else {
        logMessage("EscalationReportExport class found.\n\n");
    }
    
    foreach ($reportTypes as $reportType) {
        logMessage("Testing report type: {$reportType}\n");
        
        try {
            // Create export instance
            logMessage("  - Creating export instance...\n");
            $export = new EscalationReportExport($dateFrom, $dateTo, $reportType);
            logMessage("  - Export instance created successfully\n");
            
            // Get collection data (this will test the database queries)
            logMessage("  - Retrieving data collection...\n");
            $data = $export->collection();
            
            logMessage("  - Successfully retrieved data for {$reportType} report\n");
            logMessage("  - Number of records: " . count($data) . "\n");
            
            // Show sample data (first record) if available
            if (count($data) > 0) {
                logMessage("  - Sample data (first record):\n");
                $firstRecord = $data->first();
                foreach ($firstRecord as $key => $value) {
                    logMessage("    - {$key}: {$value}\n");
                }
            } else {
                logMessage("  - No data records found for this report type\n");
            }
            
            // Test Excel export
            $filename = "test_{$reportType}_report_" . date('YmdHis') . ".xlsx";
            $path = storage_path('app/public/' . $filename);
            logMessage("  - Exporting to Excel: {$filename}\n");
            Excel::store($export, 'public/' . $filename);
            logMessage("  - Successfully exported to {$path}\n");
            
        } catch (\Exception $e) {
            logMessage("  - ERROR with {$reportType} report: " . $e->getMessage() . "\n");
            logMessage("  - File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n");
            logMessage("  - Stack trace:\n" . $e->getTraceAsString() . "\n");
        }
        
        logMessage("\n");
    }
    
    logMessage("Test completed.\n");
    logMessage("Log file created at: {$logFile}\n");
    
} catch (\Exception $e) {
    logMessage("FATAL ERROR: " . $e->getMessage() . "\n");
    logMessage("File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n");
    logMessage("Stack trace:\n" . $e->getTraceAsString() . "\n");
}
