@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Customer Impact Analysis')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <i class="bx bx-error-alt me-2"></i>Customer Impact Analysis
            </h4>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Home</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('outages.index') }}">Outages</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('outage-reports.index') }}">Reports</a>
                </li>
                <li class="breadcrumb-item active">Impact</li>
            </ol>
        </nav>
    </div>

    <!-- Date Filter Section -->
    <div class="card mb-4">
        <div class="card-header bg-transparent">
            <h5 class="card-title mb-0">
                <i class="bx bx-filter-alt me-2"></i>Report Period
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-xs">
                            <i class="bx bx-search me-1"></i>Update Report
                        </button>
                        <a href="{{ route('outage-reports.impact.export', request()->query()) }}" class="btn btn-success btn-xs">
                            <i class="bx bx-download me-1"></i>Export CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Impact Analysis Content -->
    <div class="row">
        <!-- Impact Summary -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-error-alt me-2"></i>Impact Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-md mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-white text-danger">
                                            <i class="bx bx-error fs-4"></i>
                                        </span>
                                    </div>
                                    <h6 class="text-white mb-1">High Impact</h6>
                                    <h4 class="text-white mb-0">{{ $impactMetrics['high'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-md mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-white text-warning">
                                            <i class="bx bx-error-circle fs-4"></i>
                                        </span>
                                    </div>
                                    <h6 class="text-white mb-1">Medium Impact</h6>
                                    <h4 class="text-white mb-0">{{ $impactMetrics['medium'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-md mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-white text-success">
                                            <i class="bx bx-info-circle fs-4"></i>
                                        </span>
                                    </div>
                                    <h6 class="text-white mb-1">Low Impact</h6>
                                    <h4 class="text-white mb-0">{{ $impactMetrics['low'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-md mx-auto mb-2">
                                        <span class="avatar-initial rounded bg-white text-info">
                                            <i class="bx bx-time fs-4"></i>
                                        </span>
                                    </div>
                                    <h6 class="text-white mb-1">Avg Downtime</h6>
                                    <h4 class="text-white mb-0">{{ $impactMetrics['avg_downtime'] ?? '0h' }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Impact Distribution Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-pie-chart-alt me-2"></i>Impact Distribution
                    </h5>
                    <p class="card-subtitle text-muted mb-0">Distribution of outages by impact level</p>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px; position: relative;">
                        <canvas id="impactChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Impact Analysis -->
    <div class="card mb-4">
        <div class="card-header bg-transparent">
            <h5 class="card-title mb-0">
                <i class="bx bx-table me-2"></i>Detailed Impact Analysis
            </h5>
            <p class="card-subtitle text-muted mb-0">
                Report Period: {{ date('M d, Y', strtotime($startDate)) }} - {{ date('M d, Y', strtotime($endDate)) }}
            </p>
        </div>
        <div class="card-body">
            @if(($impactMetrics['high'] ?? 0) + ($impactMetrics['medium'] ?? 0) + ($impactMetrics['low'] ?? 0) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Impact Level</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Avg Resolution Time</th>
                                <th>Total Downtime</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-xs bg-danger">High</span></td>
                                <td><strong>{{ $impactMetrics['high'] ?? 0 }}</strong></td>
                                <td>{{ $impactMetrics['high_percent'] ?? '0%' }}</td>
                                <td>{{ $impactMetrics['high_avg_time'] ?? '0h' }}</td>
                                <td>{{ $impactMetrics['high_total_downtime'] ?? '0h' }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-xs bg-warning">Medium</span></td>
                                <td><strong>{{ $impactMetrics['medium'] ?? 0 }}</strong></td>
                                <td>{{ $impactMetrics['medium_percent'] ?? '0%' }}</td>
                                <td>{{ $impactMetrics['medium_avg_time'] ?? '0h' }}</td>
                                <td>{{ $impactMetrics['medium_total_downtime'] ?? '0h' }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-xs bg-success">Low</span></td>
                                <td><strong>{{ $impactMetrics['low'] ?? 0 }}</strong></td>
                                <td>{{ $impactMetrics['low_percent'] ?? '0%' }}</td>
                                <td>{{ $impactMetrics['low_avg_time'] ?? '0h' }}</td>
                                <td>{{ $impactMetrics['low_total_downtime'] ?? '0h' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="avatar avatar-xl mx-auto mb-3">
                        <span class="avatar-initial rounded bg-label-secondary">
                            <i class="bx bx-data fs-2"></i>
                        </span>
                    </div>
                    <h5 class="mb-2">No Impact Data Available</h5>
                    <p class="text-muted mb-0">No outage impact data found for the selected date range.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Back Button -->
    <div class="d-flex justify-content-start">
        <a href="{{ route('outage-reports.index') }}" class="btn btn-outline-secondary btn-xs">
            <i class="bx bx-arrow-back me-1"></i>Back to Reports
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize impact distribution chart
    const ctx = document.getElementById('impactChart');
    if (ctx) {
        const impactChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['High Impact', 'Medium Impact', 'Low Impact'],
                datasets: [{
                    data: [
                        {{ $impactMetrics['high'] ?? 0 }},
                        {{ $impactMetrics['medium'] ?? 0 }},
                        {{ $impactMetrics['low'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#dc3545',
                        '#ffc107',
                        '#28a745'
                    ],
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverBorderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '60%',
                animation: {
                    animateRotate: true,
                    duration: 1000
                }
            }
        });
    }
});
</script>
@endpush

