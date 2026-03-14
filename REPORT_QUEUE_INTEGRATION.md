# Report Queue System Integration Guide

This guide shows how to integrate the new report queue system with existing report controllers to prevent performance issues with large reports.

## How to Update Existing Report Controllers

### Step 1: Add the QueueableReports Trait

```php
<?php

namespace Modules\Outages\Http\Controllers;

use App\Traits\QueueableReports; // Add this import
use Illuminate\Http\Request;
// ... other imports

class OutageReportController extends OutagesController
{
    use QueueableReports; // Add this trait

    // ... existing code
}
```

### Step 2: Update Export Methods

Instead of directly generating and returning reports, queue them:

**Before (Direct Generation):**
```php
public function exportExcel(Request $request)
{
    // Generate report immediately
    $data = $this->generateReportData($request);
    return Excel::download(new OutageExport($data), 'outages.xlsx');
}
```

**After (Queued Generation):**
```php
public function exportExcel(Request $request)
{
    // Queue the report for background processing
    return $this->queueOutageReport(
        $request, 
        'Outage Excel Report', 
        'generateExcelReport', // Method that will actually generate the report
        [] // Additional parameters if needed
    );
}

// Create a separate method for actual report generation (used by the job)
public function generateExcelReport($startDate, $endDate, $teamFilter = null, ...$otherParams)
{
    // Your existing report generation logic here
    $data = $this->generateReportData($startDate, $endDate, $teamFilter);
    
    // Return the file path or binary data
    $tempFile = tempnam(sys_get_temp_dir(), 'outage_report_');
    Excel::store(new OutageExport($data), $tempFile);
    return $tempFile;
}
```

### Step 3: Update Report Views

Add a link to the report downloads page in your report views:

```html
<div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Reports</h5>
    <a href="{{ route('report-downloads.index') }}" class="btn btn-info">
        <i class="bx bx-download me-1"></i>My Downloads
    </a>
</div>
```

## Example Integration for Outage Reports

```php
<?php

namespace Modules\Outages\Http\Controllers;

use App\Traits\QueueableReports;
use Illuminate\Http\Request;
// ... other imports

class OutageReportController extends OutagesController
{
    use QueueableReports;

    // Queue Excel export
    public function exportExcel(Request $request)
    {
        return $this->queueOutageReport($request, 'Outage Excel Report', 'generateExcelReport');
    }

    // Queue PDF export
    public function exportPdf(Request $request)
    {
        return $this->queueOutageReport($request, 'Outage PDF Report', 'generatePdfReport');
    }

    // Queue SLA report
    public function exportSlaReport(Request $request)
    {
        return $this->queueOutageReport($request, 'SLA Compliance Report', 'generateSlaReport');
    }

    // Actual report generation methods (called by background jobs)
    public function generateExcelReport($startDate, $endDate, $teamFilter = null)
    {
        // Your existing Excel generation logic
        $outages = $this->getOutageData($startDate, $endDate, $teamFilter);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'outage_excel_');
        Excel::store(new OutageExport($outages), $tempFile);
        return $tempFile;
    }

    public function generatePdfReport($startDate, $endDate, $teamFilter = null)
    {
        // Your existing PDF generation logic
        $outages = $this->getOutageData($startDate, $endDate, $teamFilter);
        
        $pdf = PDF::loadView('outages::reports.pdf', compact('outages'));
        $tempFile = tempnam(sys_get_temp_dir(), 'outage_pdf_') . '.pdf';
        $pdf->save($tempFile);
        return $tempFile;
    }

    private function getOutageData($startDate, $endDate, $teamFilter = null)
    {
        $query = Outage::whereBetween('start_time', [$startDate, $endDate]);
        
        if ($teamFilter) {
            $query->where('assigned_team_id', $teamFilter);
        }
        
        return $query->with(['team', 'assignedUser'])->get();
    }
}
```

## Setting Up Queue Workers

To process the background jobs, you need to run queue workers:

```bash
# Start a queue worker
php artisan queue:work

# Or use supervisor for production
php artisan queue:work --daemon
```

## Automatic Cleanup

The system automatically cleans up old reports every 8 hours. You can also run cleanup manually:

```bash
# Clean up reports older than 8 hours (default)
php artisan reports:cleanup

# Clean up reports older than 24 hours
php artisan reports:cleanup --hours=24
```

## Benefits

1. **Performance**: Large reports don't block the web interface
2. **User Experience**: Users get immediate feedback and can continue working
3. **Storage Management**: Automatic cleanup prevents storage bloat
4. **Monitoring**: Users can track report generation progress
5. **Scalability**: Background processing handles multiple concurrent reports

## Usage Flow

1. User clicks "Export" button
2. System queues the report and redirects to downloads page
3. Background job processes the report
4. User sees status updates in real-time
5. When complete, user can download the file
6. Files are automatically cleaned up after 8 hours
