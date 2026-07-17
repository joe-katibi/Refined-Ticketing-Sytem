@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Outages Dashboard')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Outages /</span> Dashboard
            </h4>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Outages</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $totalOutages }}</h4>
                            </div>
                            <small class="text-success">
                                <a href="{{ route('outages.index') }}" class="text-decoration-none">View All</a>
                            </small>
                        </div>
                        <span class="badge badge-xs bg-primary rounded p-2">
                            <i class="bx bx-error-alt bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Active Outages</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $activeOutages }}</h4>
                            </div>
                            <small class="text-warning">
                                <a href="{{ route('outages.index', ['status' => 'In Progress']) }}" class="text-decoration-none">View Active</a>
                            </small>
                        </div>
                        <span class="badge badge-xs bg-warning rounded p-2">
                            <i class="bx bx-time-five bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Resolved Outages</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $resolvedOutages }}</h4>
                            </div>
                            <small class="text-success">
                                <a href="{{ route('outages.index', ['status' => 'Resolved']) }}" class="text-decoration-none">View Resolved</a>
                            </small>
                        </div>
                        <span class="badge badge-xs bg-success rounded p-2">
                            <i class="bx bx-check-circle bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Critical Outages</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $criticalOutages }}</h4>
                            </div>
                            <small class="text-danger">
                                <a href="{{ route('outages.index', ['priority' => 'Critical']) }}" class="text-decoration-none">View Critical</a>
                            </small>
                        </div>
                        <span class="badge badge-xs bg-danger rounded p-2">
                            <i class="bx bx-error bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Impact & Ticket Type Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Customers Affected</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ number_format($totalCustomersAffected) }}</h4>
                            </div>
                            <small class="text-info">
                                Avg: {{ number_format($avgCustomersPerOutage) }} per outage
                            </small>
                        </div>
                        <span class="badge badge-xs bg-info rounded p-2">
                            <i class="bx bx-group bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Regular Outages</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $regularOutages }}</h4>
                            </div>
                            <small class="text-primary">
                                <a href="{{ route('outages.index', ['ticket_type' => 'regular']) }}" class="text-decoration-none">View OUT-n</a>
                            </small>
                        </div>
                        <span class="badge badge-xs bg-primary rounded p-2">
                            <i class="bx bx-error-alt bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Emergency Outages</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $emergencyOutages }}</h4>
                            </div>
                            <small class="text-danger">
                                <a href="{{ route('outages.index', ['ticket_type' => 'emergency']) }}" class="text-decoration-none">View EMO-n</a>
                            </small>
                        </div>
                        <span class="badge badge-xs bg-danger rounded p-2">
                            <i class="bx bx-error bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Planned Maintenance</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $plannedMaintenanceOutages }}</h4>
                            </div>
                            <small class="text-warning">
                                <a href="{{ route('outages.index', ['ticket_type' => 'planned_maintenance']) }}" class="text-decoration-none">View PLM-n</a>
                            </small>
                        </div>
                        <span class="badge badge-xs bg-warning rounded p-2">
                            <i class="bx bx-wrench bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Performance Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div class="ms-3">
                            <div class="small mb-1">SLA Compliance</div>
                            <h5 class="card-title mb-1">{{ $slaComplianceRate }}%</h5>
                            <small class="text-muted">{{ $slaBreachedOutages }} breached out of {{ $totalOutages }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Outages by Status Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Outages by Status</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="statusChartDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="statusChartDropdown">
                            <a class="dropdown-item" href="{{ route('outage-reports.index') }}">View Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <!-- Outages by Priority Chart -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Outages by Priority</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="priorityChartDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="priorityChartDropdown">
                            <a class="dropdown-item" href="{{ route('outage-reports.index') }}">View Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Trend Chart -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">7-Day Trend</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="trendChartDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="trendChartDropdown">
                            <a class="dropdown-item" href="{{ route('outage-reports.trends') }}">View Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Section -->
    <div class="row g-4 mb-4">
        <!-- Team Performance -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Team Performance</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="teamPerformanceDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="teamPerformanceDropdown">
                            <a class="dropdown-item" href="{{ route('outage-reports.productivity') }}">View Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Team</th>
                                    <th>Total</th>
                                    <th>Resolved</th>
                                    <th>Avg Time (min)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teamPerformance as $team)
                                    <tr>
                                        <td>{{ $team->name }}</td>
                                        <td>{{ $team->total_outages }}</td>
                                        <td>{{ $team->resolved_outages }}</td>
                                        <td>{{ round($team->avg_resolution_time ?? 0, 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Recent Outages -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Recent Outages</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="recentOutagesDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="recentOutagesDropdown">
                            <a class="dropdown-item" href="{{ route('outages.index') }}">View All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap" style="max-height: 300px;">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOutages as $outage)
                                    <tr>
                                        <td>
                                            <a href="{{ route('outages.show', $outage) }}" class="text-decoration-none">
                                                {{ $outage->ticket_number }}
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($outage->title, 30) }}</td>
                                        <td>
                                            <span class="badge badge-xs bg-{{ $outage->status == 'Resolved' ? 'success' : ($outage->status == 'In Progress' ? 'warning' : 'info') }}">
                                                {{ $outage->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-xs bg-{{ $outage->priority == 'Critical' ? 'danger' : ($outage->priority == 'High' ? 'warning' : 'info') }}">
                                                {{ $outage->priority }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Impact Analysis Section -->
    <div class="row g-4 mb-4">
        <!-- Top Impacted Areas -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Top Impacted Areas</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="impactedAreas" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="impactedAreas">
                            <a class="dropdown-item" href="javascript:void(0);">View Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @foreach($impactedAreas->take(5) as $area => $count)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex flex-column">
                                <h6 class="mb-1">{{ $area }}</h6>
                                <small class="text-muted">{{ $count }} outages</small>
                            </div>
                            <div class="user-progress">
                                <small class="fw-semibold">{{ round(($count / $impactedAreas->sum()) * 100, 1) }}%</small>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($count / $impactedAreas->sum()) * 100 }}%" aria-valuenow="{{ ($count / $impactedAreas->sum()) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Top Impacted Services -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Top Impacted Services</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="impactedServices" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="impactedServices">
                            <a class="dropdown-item" href="javascript:void(0);">View Report</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @foreach($impactedServices->take(5) as $service => $count)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex flex-column">
                                <h6 class="mb-1">{{ $service }}</h6>
                                <small class="text-muted">{{ $count }} outages</small>
                            </div>
                            <div class="user-progress">
                                <small class="fw-semibold">{{ round(($count / $impactedServices->sum()) * 100, 1) }}%</small>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ ($count / $impactedServices->sum()) * 100 }}%" aria-valuenow="{{ ($count / $impactedServices->sum()) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script>
$(function () {
    // Status Chart
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    var statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($outagesByStatus->pluck('status')) !!},
            datasets: [{
                data: {!! json_encode($outagesByStatus->pluck('count')) !!},
                backgroundColor: ['#f39c12', '#00a65a', '#dd4b39', '#3c8dbc']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                position: 'bottom'
            }
        }
    });

    // Priority Chart
    var priorityCtx = document.getElementById('priorityChart').getContext('2d');
    var priorityChart = new Chart(priorityCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($outagesByPriority->pluck('priority')) !!},
            datasets: [{
                label: 'Outages',
                data: {!! json_encode($outagesByPriority->pluck('count')) !!},
                backgroundColor: ['#17a2b8', '#ffc107', '#fd7e14', '#dc3545']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    // Trend Chart
    var trendCtx = document.getElementById('trendChart').getContext('2d');
    var trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($trendData)->pluck('date')) !!},
            datasets: [{
                label: 'New Outages',
                data: {!! json_encode(collect($trendData)->pluck('outages')) !!},
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true
            }, {
                label: 'Resolved',
                data: {!! json_encode(collect($trendData)->pluck('resolved')) !!},
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
});
</script>
@endpush

