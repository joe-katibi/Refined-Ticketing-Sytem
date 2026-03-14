@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'SLA Performance Breakdown')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-0">SLA Performance Breakdown</h2>
                </div>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('outages.index') }}" class="text-decoration-none">Outages</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('outage-reports.index') }}" class="text-decoration-none">Reports</a></li>
                            <li class="breadcrumb-item active">SLA Breakdown</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bx bx-filter-alt text-primary fs-5 me-2"></i>
                        <h5 class="mb-0 fw-semibold">Report Period</h5>
                    </div>
                    <form method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label fw-semibold">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="{{ $endDate }}">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-refresh me-1"></i>Update Report
                                    </button>
                                    <a href="{{ route('outage-reports.sla-breakdown.export', request()->query()) }}" class="btn btn-success px-4">
                                        <i class="bx bx-download me-1"></i>Export CSV
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Performance Overview Section -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-pie-chart-alt text-primary fs-5 me-2"></i>
                        <h5 class="mb-0 fw-semibold">Overall SLA Compliance</h5>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="slaOverviewChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-bar-chart-alt text-primary fs-5 me-2"></i>
                        <h5 class="mb-0 fw-semibold">SLA Performance by Priority Level</h5>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="slaByPriorityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed SLA Breakdown Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-table text-primary fs-5 me-2"></i>
                            <h5 class="mb-0 fw-semibold">Detailed SLA Performance Analysis</h5>
                        </div>
                        <div class="text-muted small">
                            <i class="bx bx-calendar me-1"></i>
                            {{ date('M d, Y', strtotime($startDate)) }} - {{ date('M d, Y', strtotime($endDate)) }}
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <!-- Priority Breakdown -->
                    <div class="mb-5">
                        <h6 class="fw-semibold mb-3">SLA Performance by Priority Level</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold">Priority</th>
                                        <th class="border-0 fw-semibold">Total Outages</th>
                                        <th class="border-0 fw-semibold">Within SLA</th>
                                        <th class="border-0 fw-semibold">SLA Breached</th>
                                        <th class="border-0 fw-semibold">Compliance Rate</th>
                                        <th class="border-0 fw-semibold">Avg Resolution Time</th>
                                        <th class="border-0 fw-semibold">SLA Target</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($slaByPriority) && count($slaByPriority) > 0)
                                        @foreach($slaByPriority as $priority)
                                        <tr>
                                            <td>
                                                @if($priority->priority == 'High')
                                                    <span class="badge badge-xs bg-danger">{{ $priority->priority }}</span>
                                                @elseif($priority->priority == 'Medium')
                                                    <span class="badge badge-xs bg-warning">{{ $priority->priority }}</span>
                                                @else
                                                    <span class="badge badge-xs bg-success">{{ $priority->priority }}</span>
                                                @endif
                                            </td>
                                            <td class="fw-medium">{{ $priority->total }}</td>
                                            <td class="fw-medium">{{ $priority->total - $priority->breached }}</td>
                                            <td class="fw-medium">{{ $priority->breached }}</td>
                                            <td>
                                                @if($priority->compliance_rate >= 95)
                                                    <span class="badge badge-xs bg-success">{{ $priority->compliance_rate }}%</span>
                                                @elseif($priority->compliance_rate >= 80)
                                                    <span class="badge badge-xs bg-warning">{{ $priority->compliance_rate }}%</span>
                                                @else
                                                    <span class="badge badge-xs bg-danger">{{ $priority->compliance_rate }}%</span>
                                                @endif
                                            </td>
                                            <td class="fw-medium">
                                                @if($priority->priority == 'High')
                                                    2.5h
                                                @elseif($priority->priority == 'Medium')
                                                    3.2h
                                                @else
                                                    4.1h
                                                @endif
                                            </td>
                                            <td class="fw-medium">
                                                @if($priority->priority == 'High')
                                                    2h
                                                @elseif($priority->priority == 'Medium')
                                                    4h
                                                @else
                                                    8h
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">
                                                <i class="bx bx-data fs-1 text-muted mb-3 d-block"></i>
                                                <h6 class="mb-1">No priority data available</h6>
                                                <small>No data found for the selected period</small>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Team Breakdown -->
                    <div>
                        <h6 class="fw-semibold mb-3">SLA Performance by Team</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold">Team</th>
                                        <th class="border-0 fw-semibold">Total Outages</th>
                                        <th class="border-0 fw-semibold">Within SLA</th>
                                        <th class="border-0 fw-semibold">SLA Breached</th>
                                        <th class="border-0 fw-semibold">Compliance Rate</th>
                                        <th class="border-0 fw-semibold">Performance Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($slaByTeam) && count($slaByTeam) > 0)
                                        @foreach($slaByTeam as $team)
                                        <tr>
                                            <td class="fw-medium">{{ $team->team_name }}</td>
                                            <td class="fw-medium">{{ $team->total }}</td>
                                            <td class="fw-medium">{{ $team->total - $team->breached }}</td>
                                            <td class="fw-medium">{{ $team->breached }}</td>
                                            <td>
                                                @if($team->compliance_rate >= 95)
                                                    <span class="badge badge-xs bg-success">{{ $team->compliance_rate }}%</span>
                                                @elseif($team->compliance_rate >= 80)
                                                    <span class="badge badge-xs bg-warning">{{ $team->compliance_rate }}%</span>
                                                @else
                                                    <span class="badge badge-xs bg-danger">{{ $team->compliance_rate }}%</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($team->compliance_rate >= 95)
                                                    <i class="bx bx-trending-up text-success me-1"></i><span class="text-success fw-medium">Excellent</span>
                                                @elseif($team->compliance_rate >= 80)
                                                    <i class="bx bx-minus text-warning me-1"></i><span class="text-warning fw-medium">Good</span>
                                                @else
                                                    <i class="bx bx-trending-down text-danger me-1"></i><span class="text-danger fw-medium">Needs Improvement</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="bx bx-group fs-1 text-muted mb-3 d-block"></i>
                                                <h6 class="mb-1">No team data available</h6>
                                                <small>No data found for the selected period</small>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('outage-reports.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to Reports
                    </a>
                </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // SLA Overview Chart
    const overviewCtx = document.getElementById('slaOverviewChart').getContext('2d');
    const slaOverviewChart = new Chart(overviewCtx, {
        type: 'doughnut',
        data: {
            labels: ['Within SLA', 'SLA Breached'],
            datasets: [{
                data: [
                    {{ isset($totalOutages) && isset($slaBreachedOutages) ? $totalOutages - $slaBreachedOutages : 0 }},
                    {{ $slaBreachedOutages ?? 0 }}
                ],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Overall SLA Compliance'
                }
            }
        }
    });

    // SLA by Priority Chart
    const priorityCtx = document.getElementById('slaByPriorityChart').getContext('2d');
    
    @if(isset($slaByPriority) && count($slaByPriority) > 0)
    const priorityLabels = {!! json_encode($slaByPriority->pluck('priority')) !!};
    const priorityCompliance = {!! json_encode($slaByPriority->pluck('compliance_rate')) !!};
    const priorityTotal = {!! json_encode($slaByPriority->pluck('total')) !!};
    @else
    const priorityLabels = ['No Data'];
    const priorityCompliance = [0];
    const priorityTotal = [0];
    @endif
    
    const slaByPriorityChart = new Chart(priorityCtx, {
        type: 'bar',
        data: {
            labels: priorityLabels,
            datasets: [{
                label: 'Compliance Rate (%)',
                data: priorityCompliance,
                backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                borderColor: ['#dc3545', '#ffc107', '#28a745'],
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Total Outages',
                data: priorityTotal,
                type: 'line',
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    max: 100,
                    title: {
                        display: true,
                        text: 'Compliance Rate (%)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Total Outages'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'SLA Performance by Priority Level'
                }
            }
        }
    });
});
</script>
@endpush

