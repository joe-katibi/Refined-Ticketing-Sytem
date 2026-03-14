<?php

namespace App\Traits;

use App\Services\ReportQueueService;
use Illuminate\Http\Request;

trait QueueableReports
{
    protected $reportQueueService;

    /**
     * Initialize the report queue service
     */
    protected function initializeReportQueue()
    {
        if (!$this->reportQueueService) {
            $this->reportQueueService = app(ReportQueueService::class);
        }
    }

    /**
     * Queue a report instead of generating it immediately
     *
     * @param Request $request
     * @param string $reportType
     * @param string $reportName
     * @param string $method
     * @param array $additionalParams
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function queueReportGeneration(Request $request, string $reportType, string $reportName, string $method, array $additionalParams = [])
    {
        $this->initializeReportQueue();

        // Extract common parameters
        $startDate = $request->input('start_date') ? \Carbon\Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? \Carbon\Carbon::parse($request->input('end_date')) : null;
        
        // Combine request parameters with additional parameters
        $parameters = array_merge($request->all(), $additionalParams);

        // Queue the report based on type
        switch (strtolower($reportType)) {
            case 'outage':
                $reportDownload = $this->reportQueueService->queueOutageReport($reportName, $method, $parameters, $startDate, $endDate);
                break;
            case 'appointment':
                $reportDownload = $this->reportQueueService->queueAppointmentReport($reportName, $method, $parameters, $startDate, $endDate);
                break;
            case 'escalation':
                $reportDownload = $this->reportQueueService->queueEscalationReport($reportName, $method, $parameters, $startDate, $endDate);
                break;
            default:
                return redirect()->back()->with('error', 'Unknown report type: ' . $reportType);
        }

        return redirect()->route('report-downloads.index')
            ->with('success', "Report '{$reportName}' has been queued for generation. You will be notified when it's ready for download.");
    }

    /**
     * Queue an outage report
     */
    protected function queueOutageReport(Request $request, string $reportName, string $method, array $additionalParams = [])
    {
        return $this->queueReportGeneration($request, 'outage', $reportName, $method, $additionalParams);
    }

    /**
     * Queue an appointment report
     */
    protected function queueAppointmentReport(Request $request, string $reportName, string $method, array $additionalParams = [])
    {
        return $this->queueReportGeneration($request, 'appointment', $reportName, $method, $additionalParams);
    }

    /**
     * Queue an escalation report
     */
    protected function queueEscalationReport(Request $request, string $reportName, string $method, array $additionalParams = [])
    {
        return $this->queueReportGeneration($request, 'escalation', $reportName, $method, $additionalParams);
    }
}
