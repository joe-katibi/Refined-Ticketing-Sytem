@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Team Productivity Analysis')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-0">Team Productivity Analysis</h2>
                </div>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('outages.index') }}" class="text-decoration-none">Outages</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('outage-reports.index') }}" class="text-decoration-none">Reports</a></li>
                            <li class="breadcrumb-item active">Productivity</li>
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
                                    <a href="{{ route('outage-reports.productivity.export', request()->query()) }}" class="btn btn-success px-4">
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

    <!-- Team Productivity Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-group text-primary fs-5 me-2"></i>
                            <h5 class="mb-0 fw-semibold">Team Performance Metrics</h5>
                        </div>
                        <div class="text-muted small">
                            <i class="bx bx-calendar me-1"></i>
                            {{ date('M d, Y', strtotime($startDate)) }} - {{ date('M d, Y', strtotime($endDate)) }}
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold">Team Name</th>
                                    <th class="border-0 fw-semibold">Total Outages</th>
                                    <th class="border-0 fw-semibold">Resolved</th>
                                    <th class="border-0 fw-semibold">Closed</th>
                                    <th class="border-0 fw-semibold">Resolution Rate</th>
                                    <th class="border-0 fw-semibold">Avg Resolution Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($teamProductivity) && count($teamProductivity) > 0)
                                    @foreach($teamProductivity as $team)
                                    <tr>
                                        <td class="fw-medium">{{ $team->team_name }}</td>
                                        <td class="fw-medium">{{ $team->total_assigned }}</td>
                                        <td class="fw-medium">{{ $team->resolved_outages }}</td>
                                        <td class="fw-medium">{{ $team->closed_outages }}</td>
                                        <td>
                                            @if($team->total_assigned > 0)
                                                @php $rate = ($team->resolved_outages / $team->total_assigned) * 100; @endphp
                                                @if($rate >= 80)
                                                    <span class="badge badge-xs bg-success">{{ number_format($rate, 1) }}%</span>
                                                @elseif($rate >= 60)
                                                    <span class="badge badge-xs bg-warning">{{ number_format($rate, 1) }}%</span>
                                                @else
                                                    <span class="badge badge-xs bg-danger">{{ number_format($rate, 1) }}%</span>
                                                @endif
                                            @else
                                                <span class="badge badge-xs bg-secondary">0%</span>
                                            @endif
                                        </td>
                                        <td class="fw-medium">{{ $team->avg_resolution_time ? number_format($team->avg_resolution_time / 60, 1) . 'h' : 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bx bx-data fs-1 text-muted mb-3 d-block"></i>
                                            <h6 class="mb-1">No team productivity data available</h6>
                                            <small>No data found for the selected period</small>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Individual User Productivity Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-user text-primary fs-5 me-2"></i>
                        <h5 class="mb-0 fw-semibold">Individual User Performance</h5>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold">User Name</th>
                                    <th class="border-0 fw-semibold">Total Assigned</th>
                                    <th class="border-0 fw-semibold">Resolved</th>
                                    <th class="border-0 fw-semibold">Closed</th>
                                    <th class="border-0 fw-semibold">Resolution Rate</th>
                                    <th class="border-0 fw-semibold">Avg Resolution Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($userProductivity) && count($userProductivity) > 0)
                                    @foreach($userProductivity as $user)
                                    <tr>
                                        <td class="fw-medium">{{ $user->name }}</td>
                                        <td class="fw-medium">{{ $user->total_assigned }}</td>
                                        <td class="fw-medium">{{ $user->resolved_outages }}</td>
                                        <td class="fw-medium">{{ $user->closed_outages ?? 0 }}</td>
                                        <td>
                                            @if($user->total_assigned > 0)
                                                @php $rate = ($user->resolved_outages / $user->total_assigned) * 100; @endphp
                                                @if($rate >= 80)
                                                    <span class="badge badge-xs bg-success">{{ number_format($rate, 1) }}%</span>
                                                @elseif($rate >= 60)
                                                    <span class="badge badge-xs bg-warning">{{ number_format($rate, 1) }}%</span>
                                                @else
                                                    <span class="badge badge-xs bg-danger">{{ number_format($rate, 1) }}%</span>
                                                @endif
                                            @else
                                                <span class="badge badge-xs bg-secondary">0%</span>
                                            @endif
                                        </td>
                                        <td class="fw-medium">{{ $user->avg_resolution_time ? number_format($user->avg_resolution_time / 60, 1) . 'h' : 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bx bx-user-x fs-1 text-muted mb-3 d-block"></i>
                                            <h6 class="mb-1">No individual user productivity data available</h6>
                                            <small>No data found for the selected period</small>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Summary Section -->
    <div class="row">
        <!-- Ticket Status Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-pie-chart-alt text-primary fs-5 me-2"></i>
                        <h5 class="mb-0 fw-semibold">Ticket Status Distribution</h5>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-bar-chart-alt text-primary fs-5 me-2"></i>
                        <h5 class="mb-0 fw-semibold">Performance Summary</h5>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center p-3 bg-light-success rounded">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-success text-white rounded">
                                            <i class="bx bx-check fs-5"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 fw-semibold">Total Resolved</h6>
                                    <h4 class="mb-0 text-success">
                                        {{ isset($ticketProductivity) ? $ticketProductivity->where('status', 'noc-restore-confirmed')->sum('count') + $ticketProductivity->where('status', 'infra-resolved')->sum('count') : 0 }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-3 bg-light-info rounded">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-info text-white rounded">
                                            <i class="bx bx-time fs-5"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 fw-semibold">In Progress</h6>
                                    <h4 class="mb-0 text-info">
                                        {{ isset($ticketProductivity) ? $ticketProductivity->where('status', 'noc-confirmed-outage')->sum('count') + $ticketProductivity->where('status', 'infra-dispatched')->sum('count') + $ticketProductivity->where('status', 'infra-confirmed-outage')->sum('count') : 0 }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-3 bg-light-warning rounded">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-warning text-white rounded">
                                            <i class="bx bx-pause fs-5"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 fw-semibold">Pending</h6>
                                    <h4 class="mb-0 text-warning">
                                        {{ isset($ticketProductivity) ? $ticketProductivity->where('status', 'support-unconfirmed-outage')->sum('count') + $ticketProductivity->where('status', 'awaiting-customer-confirmation')->sum('count') + $ticketProductivity->where('status', 'awaiting-field-access')->sum('count') : 0 }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-3 bg-light-secondary rounded">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-secondary text-white rounded">
                                            <i class="bx bx-archive fs-5"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 fw-semibold">Closed</h6>
                                    <h4 class="mb-0 text-secondary">
                                        {{ isset($ticketProductivity) ? $ticketProductivity->where('status', 'support-closed')->sum('count') : 0 }}
                                    </h4>
                                </div>
                            </div>
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
    // Initialize status distribution chart
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    @if(isset($ticketProductivity) && count($ticketProductivity) > 0)
    const labels = {!! json_encode($ticketProductivity->pluck('status')) !!};
    const data = {!! json_encode($ticketProductivity->pluck('count')) !!};
    const colors = ['#28a745', '#17a2b8', '#ffc107', '#6c757d', '#dc3545'];
    @else
    const labels = ['No Data'];
    const data = [1];
    const colors = ['#E0E0E0'];
    @endif
    
    const statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
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
                    text: 'Ticket Status Distribution'
                }
            }
        }
    });
});
</script>
@endpush

