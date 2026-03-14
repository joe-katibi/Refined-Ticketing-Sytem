@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Outage Trends Analysis')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-0">Outage Trends Analysis</h2>
                </div>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('outages.index') }}" class="text-decoration-none">Outages</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('outage-reports.index') }}" class="text-decoration-none">Reports</a></li>
                            <li class="breadcrumb-item active">Trends</li>
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
                                    <a href="{{ route('outage-reports.trends.export', request()->query()) }}" class="btn btn-success px-4">
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

    <!-- Trends Analysis Chart Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-line-chart text-primary fs-5 me-2"></i>
                            <h5 class="mb-0 fw-semibold">Trends Analysis Report</h5>
                        </div>
                        <div class="text-muted small">
                            <i class="bx bx-calendar me-1"></i>
                            {{ date('M d, Y', strtotime($startDate)) }} - {{ date('M d, Y', strtotime($endDate)) }}
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="mb-4">
                        <p class="text-muted mb-0">
                            This report provides historical trend analysis of outage patterns, frequency, 
                            and resolution improvements over time. Use this data to identify patterns and 
                            improve incident response strategies.
                        </p>
                    </div>

                    <!-- Chart Container -->
                    <div class="chart-container mb-4" style="height: 400px;">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Section -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-info text-white rounded">
                                    <i class="bx bx-line-chart fs-5"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 fw-semibold">Monthly Average</h6>
                            <h4 class="mb-0 text-info">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-success text-white rounded">
                                    <i class="bx bx-trending-up fs-5"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 fw-semibold">Trend Direction</h6>
                            <h4 class="mb-0 text-success">→</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-warning text-white rounded">
                                    <i class="bx bx-time fs-5"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 fw-semibold">Avg Resolution</h6>
                            <h4 class="mb-0 text-warning">0h</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-danger text-white rounded">
                                    <i class="bx bx-error fs-5"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 fw-semibold">Peak Month</h6>
                            <h4 class="mb-0 text-danger">N/A</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="row">
        <div class="col-12">
            <a href="{{ route('outage-reports.index') }}" class="btn btn-outline-secondary btn-xs">
                <i class="bx bx-arrow-back me-1"></i>Back to Reports
            </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize trends chart
    const ctx = document.getElementById('trendsChart').getContext('2d');
    const trendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Total Outages',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Resolved Outages',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Outage Trends Over Time'
                }
            }
        }
    });
});
</script>
@endpush

