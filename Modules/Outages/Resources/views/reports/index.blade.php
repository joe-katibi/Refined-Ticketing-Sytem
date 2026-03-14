@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Outage Reports')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-bar-chart-alt-2 me-2"></i>
                                Outage Reports Dashboard
                            </h4>
                            <p class="card-text mb-0">Comprehensive analytics and reporting for outage management</p>
                        </div>
                        <div class="text-end">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-white mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('outages.index') }}" class="text-white">Outages</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Reports</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">
                        <i class="bx bx-filter-alt me-2"></i>
                        Report Filters
                    </h5>
                    <small class="text-muted">Configure date range and team filters</small>
                </div>
                <div class="card-body">
                    <form id="reportFilters" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="{{ request('end_date', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="team_filter" class="form-label">Team Filter</label>
                            <select class="form-select" id="team_filter" name="team_filter">
                                <option value="">All Teams</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ request('team_filter') == $team->id ? 'selected' : '' }}>
                                        {{ $team->type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-xs">
                                    <i class="bx bx-search-alt me-1"></i>Apply
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('outage-reports.index') }}" class="btn btn-outline-secondary btn-xs">
                                    <i class="bx bx-reset me-1"></i>Reset Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Performance Reports -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="bx bx-trending-up me-2 text-primary"></i>
                Performance & Compliance Reports
            </h5>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- SLA Compliance Report -->
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">SLA Compliance</h5>
                            <small class="text-muted">Service Level Agreement Analysis</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('outage-reports.sla', request()->query()) }}">
                                <i class="bx bx-show me-2"></i>View Report
                            </a>
                            <a class="dropdown-item" href="{{ route('outage-reports.sla.export', request()->query()) }}">
                                <i class="bx bx-download me-2"></i>Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted mb-3">
                        Comprehensive analysis of SLA compliance rates, breach patterns, and resolution time performance.
                    </p>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-success mb-1">{{ $slaMetrics['compliance_rate'] }}%</h4>
                                <small class="text-muted">Compliance Rate</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger mb-1">{{ $slaMetrics['breach_count'] }}</h4>
                            <small class="text-muted">SLA Breaches</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('outage-reports.sla', request()->query()) }}" class="btn btn-success btn-xs">
                            <i class="bx bx-bar-chart-alt me-1"></i>View Detailed Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Productivity Report -->
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-group"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Team Productivity</h5>
                            <small class="text-muted">Performance & Efficiency Analysis</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('outage-reports.productivity', request()->query()) }}">
                                <i class="bx bx-show me-2"></i>View Report
                            </a>
                            <a class="dropdown-item" href="{{ route('outage-reports.productivity.export', request()->query()) }}">
                                <i class="bx bx-download me-2"></i>Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted mb-3">
                        Team performance metrics including resolution rates, handling times, and workload distribution.
                    </p>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-info mb-1">{{ $productivityMetrics['total_resolved'] }}</h4>
                                <small class="text-muted">Total Resolved</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning mb-1">{{ $productivityMetrics['avg_resolution_time'] }}</h4>
                            <small class="text-muted">Avg Resolution Time</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('outage-reports.productivity', request()->query()) }}" class="btn btn-info btn-xs">
                            <i class="bx bx-bar-chart-alt me-1"></i>View Detailed Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLA Breakdown Report -->
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-pie-chart-alt"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">SLA Breakdown</h5>
                            <small class="text-muted">Detailed Performance Analysis</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('outage-reports.sla-breakdown', request()->query()) }}">
                                <i class="bx bx-show me-2"></i>View Report
                            </a>
                            <a class="dropdown-item" href="{{ route('outage-reports.sla-breakdown.export', request()->query()) }}">
                                <i class="bx bx-download me-2"></i>Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted mb-3">
                        Detailed breakdown of SLA performance by priority, team, and time periods with visual analytics.
                    </p>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-success mb-1">{{ $slaBreakdown['within_sla'] }}</h4>
                                <small class="text-muted">Within SLA</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger mb-1">{{ $slaBreakdown['breached_sla'] }}</h4>
                            <small class="text-muted">Breached SLA</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('outage-reports.sla-breakdown', request()->query()) }}" class="btn btn-warning btn-xs">
                            <i class="bx bx-pie-chart-alt me-1"></i>View Detailed Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics & Insights Reports -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="bx bx-line-chart me-2 text-success"></i>
                Analytics & Business Intelligence
            </h5>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Trend Analysis Report -->
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-trending-up"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Trend Analysis</h5>
                            <small class="text-muted">Historical Pattern Analysis</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('outage-reports.trends', request()->query()) }}">
                                <i class="bx bx-show me-2"></i>View Report
                            </a>
                            <a class="dropdown-item" href="{{ route('outage-reports.trends.export', request()->query()) }}">
                                <i class="bx bx-download me-2"></i>Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted mb-3">
                        Historical trend analysis of outage patterns, frequency, and resolution improvements over time.
                    </p>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-success mb-1">{{ $trendMetrics['monthly_avg'] }}</h4>
                                <small class="text-muted">Monthly Average</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-primary mb-1">{{ $trendMetrics['trend_direction'] }}</h4>
                            <small class="text-muted">Trend Direction</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('outage-reports.trends', request()->query()) }}" class="btn btn-success btn-xs">
                            <i class="bx bx-line-chart me-1"></i>View Detailed Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Customer Impact Report -->
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="bx bx-error-alt"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Customer Impact</h5>
                            <small class="text-muted">Business Impact Assessment</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('outage-reports.impact', request()->query()) }}">
                                <i class="bx bx-show me-2"></i>View Report
                            </a>
                            <a class="dropdown-item" href="{{ route('outage-reports.impact.export', request()->query()) }}">
                                <i class="bx bx-download me-2"></i>Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted mb-3">
                        Customer impact analysis including affected services, downtime duration, and business impact assessment.
                    </p>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-danger mb-1">{{ $impactMetrics['high_impact_count'] }}</h4>
                                <small class="text-muted">High Impact</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h4 class="text-info mb-1">{{ number_format($impactMetrics['total_customers_affected']) }}</h4>
                                <small class="text-muted">Customers Affected</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning mb-1">{{ $impactMetrics['total_downtime'] }}</h4>
                            <small class="text-muted">Total Downtime</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('outage-reports.impact', request()->query()) }}" class="btn btn-danger btn-xs">
                            <i class="bx bx-error-alt me-1"></i>View Detailed Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Root Cause Analysis -->
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="bx bx-search-alt"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Root Cause Analysis</h5>
                            <small class="text-muted">Pattern Identification & Prevention</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('outage-reports.root-cause', request()->query()) }}">
                                <i class="bx bx-show me-2"></i>View Report
                            </a>
                            <a class="dropdown-item" href="{{ route('outage-reports.root-cause.export', request()->query()) }}">
                                <i class="bx bx-download me-2"></i>Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted mb-3">
                        Root cause analysis and categorization of outages to identify patterns and prevention opportunities.
                    </p>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-secondary mb-1">{{ $rootCauseMetrics['top_cause'] }}</h4>
                                <small class="text-muted">Top Cause</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-primary mb-1">{{ $rootCauseMetrics['categories_count'] }}</h4>
                            <small class="text-muted">Categories</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('outage-reports.root-cause', request()->query()) }}" class="btn btn-secondary btn-xs">
                            <i class="bx bx-search-alt me-1"></i>View Detailed Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Comprehensive Export Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="bx bx-download me-2 text-primary"></i>
                Comprehensive Data Export
            </h5>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-export"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Combined Export</h5>
                            <small class="text-muted">Export all outage data and reports in comprehensive format</small>
                        </div>
                    </div>
                    <div class="badge badge-xs bg-primary">All Reports</div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted mb-4">
                        Export all outage data and reports in a comprehensive format for external analysis and reporting purposes.
                        Includes all metrics, trends, and detailed breakdowns with customer impact data.
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3">
                                <i class="bx bx-check-circle text-success me-2"></i>
                                Export Includes:
                            </h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    <span class="fw-medium">SLA Performance Data</span>
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    <span class="fw-medium">Team Productivity Metrics</span>
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    <span class="fw-medium">Customer Impact Analysis</span>
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    <span class="fw-medium">Root Cause Breakdown</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3">
                                <i class="bx bx-calendar text-info me-2"></i>
                                Data Period:
                            </h6>
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">From:</span>
                                    <span class="fw-medium">{{ request('start_date', now()->subMonth()->format('M d, Y')) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">To:</span>
                                    <span class="fw-medium">{{ request('end_date', now()->format('M d, Y')) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-grid">
                                <a href="{{ route('outage-reports.export', request()->query()) }}" class="btn btn-primary btn-xs">
                                    <i class="bx bx-download me-2"></i>Export All Reports (CSV)
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-info btn-xs" onclick="printReports()">
                                    <i class="bx bx-printer me-2"></i>Print Summary
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('#team_filter').change(function() {
        $('#reportFilters').submit();
    });

    // Validate date range
    $('#start_date, #end_date').change(function() {
        var startDate = new Date($('#start_date').val());
        var endDate = new Date($('#end_date').val());

        if (startDate > endDate) {
            toastr.warning('Start date cannot be later than end date');
            return false;
        }

        if (endDate > new Date()) {
            toastr.warning('End date cannot be in the future');
            return false;
        }
    });
});

function printReports() {
    var printWindow = window.open('', '_blank');
    var content = `
        <html>
        <head>
            <title>Outage Reports Summary</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .metric { display: inline-block; margin: 10px; padding: 15px; border: 1px solid #ddd; }
                .metric h3 { margin: 0; color: #333; }
                .metric p { margin: 5px 0; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Outage Reports Summary</h1>
                <p>Period: {{ request('start_date', now()->subMonth()->format('M d, Y')) }} - {{ request('end_date', now()->format('M d, Y')) }}</p>
            </div>
            <div class="metrics">
                <div class="metric">
                    <h3>SLA Compliance</h3>
                    <p>{{ $slaMetrics['compliance_rate'] }}%</p>
                </div>
                <div class="metric">
                    <h3>Total Resolved</h3>
                    <p>{{ $productivityMetrics['total_resolved'] }}</p>
                </div>
                <div class="metric">
                    <h3>SLA Breaches</h3>
                    <p>{{ $slaMetrics['breach_count'] }}</p>
                </div>
                <div class="metric">
                    <h3>High Impact Outages</h3>
                    <p>{{ $impactMetrics['high_impact_count'] }}</p>
                </div>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(content);
    printWindow.document.close();
    printWindow.print();
}
</script>
@endpush

