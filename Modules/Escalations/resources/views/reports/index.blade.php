@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Escalation Reports')

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
                        Escalation Reports
                    </h4>
                    <p class="text-muted mb-0">Comprehensive reporting and analytics for escalation management</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- SLA Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        SLA Report
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Comprehensive SLA performance metrics including compliance rates, 
                                        daily breakdowns, and trend analysis for escalation resolution times.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('escalations.reports.sla') }}" class="btn btn-primary btn-xs">
                                            <i class="fas fa-chart-line me-1"></i>
                                            View SLA Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productivity Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        Productivity Report
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Individual user performance metrics showing tickets closed, 
                                        SLA compliance rates, and average resolution times by team member.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('escalations.reports.productivity') }}" class="btn btn-success btn-xs">
                                            <i class="fas fa-user-chart me-1"></i>
                                            View Productivity Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sub Category Report Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card border-warning h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tags me-2"></i>
                                        Sub Category Report
                                    </h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text flex-grow-1">
                                        Analysis of escalated items grouped by sub categories, 
                                        showing volume trends and resolution patterns by issue type.
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ route('escalations.reports.sub-category') }}" class="btn btn-warning btn-xs">
                                            <i class="fas fa-list-alt me-1"></i>
                                            View Category Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Excel Export Card -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-file-excel me-2"></i>
                                        Excel Export
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        Export escalation data and reports to Excel format for further analysis and offline reporting.
                                    </p>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-success btn-xs" data-bs-toggle="modal" data-bs-target="#exportModal">
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
                                                <h3>{{ \Modules\Escalations\Entities\Escalation::whereMonth('created_at', now()->month)->count() }}</h3>
                                                <p class="mb-0">Total Escalations</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-success text-white p-3 rounded">
                                                <h3>{{ \Modules\Escalations\Entities\Escalation::whereMonth('created_at', now()->month)->whereIn('status', ['Scheduled-Closed', 'Escalated-Closed'])->count() }}</h3>
                                                <p class="mb-0">Closed This Month</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-warning text-dark p-3 rounded">
                                                <h3>{{ \Modules\Escalations\Entities\Escalation::whereMonth('created_at', now()->month)->whereNotIn('status', ['Scheduled-Closed', 'Escalated-Closed'])->count() }}</h3>
                                                <p class="mb-0">Still Open</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-primary text-white p-3 rounded">
                                                @php
                                                    $monthlyTotal = \Modules\Escalations\Entities\Escalation::whereMonth('created_at', now()->month)->whereIn('status', ['Scheduled-Closed', 'Escalated-Closed'])->count();
                                                    $monthlyWithinSla = \Modules\Escalations\Entities\Escalation::whereMonth('created_at', now()->month)->whereIn('status', ['Scheduled-Closed', 'Escalated-Closed'])->whereRaw('TIMESTAMPDIFF(HOUR, created_at, updated_at) <= 4')->count();
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
                    <i class="fas fa-file-excel text-success me-2"></i>
                    Export Escalation Reports to Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('escalations.reports.export-excel') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="">Select Report Type</option>
                                <option value="sla">SLA Performance Report</option>
                                <option value="productivity">User Productivity Report</option>
                                <option value="sub_category">Sub Category Report</option>
                                <option value="escalations">Complete Escalation Data</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="export_date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="export_date_from" name="date_from" value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="export_date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="export_date_to" name="date_to" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Export Options:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>SLA Performance:</strong> Daily SLA compliance metrics</li>
                            <li><strong>User Productivity:</strong> Individual user performance data</li>
                            <li><strong>Sub Category:</strong> Category-wise escalation analysis</li>
                            <li><strong>Complete Data:</strong> All escalation records with details</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancel
                    </button>
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

