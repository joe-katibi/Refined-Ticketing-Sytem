@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Appointment Reports')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Appointment Reports & Analytics
                    </h4>
                    <p class="text-muted mb-0">Comprehensive reporting and analytics for appointment management</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- SLA Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        SLA Performance
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Comprehensive SLA performance metrics including compliance rates,
                                        daily breakdowns, and trend analysis for appointment resolution times.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('appointment.reports.sla') }}" class="btn btn-primary btn-xs">
                                            <i class="fas fa-chart-line me-1"></i>
                                            View SLA Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Team Productivity Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        Team Productivity
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Team performance metrics showing appointments handled,
                                        SLA compliance rates, and average resolution times by team.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('appointment.reports.team-productivity') }}" class="btn btn-success btn-xs">
                                            <i class="fas fa-chart-bar me-1"></i>
                                            View Team Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sub Team Productivity Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-info h-100">
                                <div class="card-header bg-info text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-layer-group me-2"></i>
                                        Sub Team Productivity
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Sub team performance analysis with detailed metrics on
                                        appointment handling and SLA compliance by sub team type.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('appointment.reports.sub-team-productivity') }}" class="btn btn-info btn-xs">
                                            <i class="fas fa-sitemap me-1"></i>
                                            View Sub Team Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned Team Productivity Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-warning h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-tie me-2"></i>
                                        Individual Productivity
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Individual user performance metrics showing appointments closed,
                                        SLA compliance rates, and productivity rankings.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('appointment.reports.assigned-team-productivity') }}" class="btn btn-warning btn-xs">
                                            <i class="fas fa-user-chart me-1"></i>
                                            View Individual Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Final Reason Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-danger h-100">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-flag me-2"></i>
                                        Final Reason Analysis
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Analysis of appointment outcomes grouped by final reasons,
                                        showing resolution patterns and closure statistics.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('appointment.reports.final-reason') }}" class="btn btn-danger btn-xs">
                                            <i class="fas fa-list-alt me-1"></i>
                                            View Final Reason Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Region Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-dark h-100">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        Region Report
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Per-region breakdown of appointments: SLA status, feedback on why
                                        a ticket is still pending, and final reason for closed tickets.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('appointment.reports.region') }}" class="btn btn-dark btn-xs">
                                            <i class="fas fa-map-marked-alt me-1"></i>
                                            View Region Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Open Tickets Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-warning h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-hourglass-half me-2"></i>
                                        Open Tickets
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Still-open appointments broken down by OLT, sub category,
                                        and team, with how long each has been open.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('appointment.reports.open-tickets') }}" class="btn btn-warning btn-xs">
                                            <i class="fas fa-list-ul me-1"></i>
                                            View Open Tickets Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Excel Export Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-secondary h-100">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-file-excel me-2"></i>
                                        Excel Export
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Export appointment data and reports to Excel format
                                        for further analysis and offline reporting.
                                    </p>
                                    <div class="mt-auto">
                                        <button type="button" class="btn btn-secondary btn-xs" data-bs-toggle="modal" data-bs-target="#exportModal">
                                            <i class="fas fa-download me-1"></i>
                                            Export to Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Row -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tachometer-alt me-2"></i>
                                        Quick Statistics (This Month)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="small-box bg-info text-white p-3 rounded">
                                                <h3>{{ \Modules\Appointment\Models\Appointment::whereMonth('created_at', now()->month)->count() }}</h3>
                                                <p class="mb-0">Total Appointments</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-success text-white p-3 rounded">
                                                <h3>{{ \Modules\Appointment\Models\Appointment::whereMonth('created_at', now()->month)->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])->count() }}</h3>
                                                <p class="mb-0">Closed This Month</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-warning text-dark p-3 rounded">
                                                <h3>{{ \Modules\Appointment\Models\Appointment::whereMonth('created_at', now()->month)->whereNotIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])->count() }}</h3>
                                                <p class="mb-0">Still Open</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-primary text-white p-3 rounded">
                                                @php
                                                    $monthlyTotal = \Modules\Appointment\Models\Appointment::whereMonth('created_at', now()->month)->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])->count();
                                                    $monthlyWithinSla = \Modules\Appointment\Models\Appointment::whereMonth('created_at', now()->month)->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2')->count();
                                                    $monthlyCompliance = $monthlyTotal > 0 ? round(($monthlyWithinSla / $monthlyTotal) * 100, 1) : 0;
                                                @endphp
                                                <h3>{{ $monthlyCompliance }}%</h3>
                                                <p class="mb-0">SLA Compliance</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-file-excel me-2"></i>
                    Export Appointment Data
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('appointment.reports.export-excel') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="export_startDate" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="export_startDate" name="startDate" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="export_endDate" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="export_endDate" name="endDate" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="report_type" name="report_type">
                                <option value="appointments">All Appointments Data</option>
                                <option value="sla">SLA Report</option>
                                <option value="team_productivity">Team Productivity</option>
                                <option value="sub_team_productivity">Sub Team Productivity</option>
                                <option value="assigned_team_productivity">Individual Productivity</option>
                                <option value="final_reason">Final Reason Analysis</option>
                                <option value="region">Region Report</option>
                                <option value="open_tickets">Open Tickets Report</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-xs">
                        <i class="fas fa-download me-1"></i>
                        Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
