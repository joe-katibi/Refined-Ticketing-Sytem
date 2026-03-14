<?php

namespace App\Services;

use App\Jobs\GenerateReportJob;
use App\Models\ReportDownload;
use Illuminate\Support\Facades\Auth;

class ReportQueueService
{
    /**
     * Queue a report for background generation
     *
     * @param string $reportType
     * @param string $reportName
     * @param string $reportClass
     * @param string $reportMethod
     * @param array $parameters
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return ReportDownload
     */
    public function queueReport(
        string $reportType,
        string $reportName,
        string $reportClass,
        string $reportMethod,
        array $parameters = [],
        $startDate = null,
        $endDate = null
    ): ReportDownload {
        // Create the report download record
        $reportDownload = ReportDownload::create([
            'user_id' => Auth::id(),
            'report_type' => $reportType,
            'report_name' => $reportName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
            'parameters' => $parameters,
            'requested_at' => now(),
        ]);

        // Dispatch the job to generate the report
        GenerateReportJob::dispatch($reportDownload, $reportClass, $reportMethod, $parameters);

        return $reportDownload;
    }

    /**
     * Queue an outage report
     */
    public function queueOutageReport(string $reportName, string $method, array $parameters = [], $startDate = null, $endDate = null): ReportDownload
    {
        return $this->queueReport(
            'Outage',
            $reportName,
            'Modules\Outages\Http\Controllers\OutageReportController',
            $method,
            $parameters,
            $startDate,
            $endDate
        );
    }

    /**
     * Queue an appointment report
     */
    public function queueAppointmentReport(string $reportName, string $method, array $parameters = [], $startDate = null, $endDate = null): ReportDownload
    {
        return $this->queueReport(
            'Appointment',
            $reportName,
            'Modules\Appointment\Http\Controllers\AppointmentReportController',
            $method,
            $parameters,
            $startDate,
            $endDate
        );
    }

    /**
     * Queue an escalation report
     */
    public function queueEscalationReport(string $reportName, string $method, array $parameters = [], $startDate = null, $endDate = null): ReportDownload
    {
        return $this->queueReport(
            'Escalation',
            $reportName,
            'Modules\Escalations\Http\Controllers\EscalationReportController',
            $method,
            $parameters,
            $startDate,
            $endDate
        );
    }
}
