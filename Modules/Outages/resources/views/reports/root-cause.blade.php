@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Root Cause Analysis')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <i class="bx bx-search-alt me-2"></i>Root Cause Analysis
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
                <li class="breadcrumb-item active">Root Cause</li>
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
                        <a href="{{ route('outage-reports.root-cause.export', request()->query()) }}" class="btn btn-success btn-xs">
                            <i class="bx bx-download me-1"></i>Export CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Root Cause Analysis Content -->
    <div class="row">
        <!-- Root Cause Distribution Table -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-search-alt me-2"></i>Root Cause Distribution
                    </h5>
                    <p class="card-subtitle text-muted mb-0">Analysis of outage root causes and resolution times</p>
                </div>
                <div class="card-body">
                    @if(isset($rootCauseMetrics) && count($rootCauseMetrics) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Root Cause Category</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                        <th>Avg Resolution Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rootCauseMetrics as $cause => $data)
                                    <tr>
                                        <td><strong>{{ ucfirst(str_replace('_', ' ', $cause)) }}</strong></td>
                                        <td><span class="badge badge-xs bg-primary">{{ $data['count'] ?? 0 }}</span></td>
                                        <td>{{ $data['percentage'] ?? '0%' }}</td>
                                        <td>{{ $data['avg_resolution_time'] ?? '0h' }}</td>
                                    </tr>
                                    @endforeach
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
                            <h5 class="mb-2">No Root Cause Data Available</h5>
                            <p class="text-muted mb-0">No root cause data found for the selected date range.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Root Cause Distribution Chart -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-pie-chart-alt me-2"></i>Cause Distribution
                    </h5>
                    <p class="card-subtitle text-muted mb-0">Visual breakdown of root causes</p>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px; position: relative;">
                        <canvas id="rootCauseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prevention Opportunities -->
    <div class="card mb-4">
        <div class="card-header bg-transparent">
            <h5 class="card-title mb-0">
                <i class="bx bx-bulb me-2"></i>Prevention Opportunities
            </h5>
            <p class="card-subtitle text-muted mb-0">
                Report Period: {{ date('M d, Y', strtotime($startDate)) }} - {{ date('M d, Y', strtotime($endDate)) }}
            </p>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Top Prevention Areas</h6>
                    @if(isset($preventionOpportunities) && count($preventionOpportunities) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($preventionOpportunities as $opportunity)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="bx bx-error-circle text-warning me-2"></i>
                                    {{ $opportunity['category'] }}
                                </div>
                                <span class="badge badge-xs bg-primary rounded-pill">{{ $opportunity['count'] }} incidents</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <div class="avatar avatar-md mx-auto mb-2">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="bx bx-search-alt"></i>
                                </span>
                            </div>
                            <p class="text-muted mb-0">No prevention opportunities identified</p>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Recommendations</h6>
                    <div class="alert alert-warning d-flex align-items-start">
                        <i class="bx bx-info-circle me-2 mt-1"></i>
                        <div>
                            <ul class="mb-0 ps-3">
                                <li>Implement proactive monitoring for top root causes</li>
                                <li>Enhance preventive maintenance schedules</li>
                                <li>Improve documentation and knowledge sharing</li>
                                <li>Consider automation for recurring issues</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
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
    // Initialize root cause distribution chart
    const ctx = document.getElementById('rootCauseChart');
    if (ctx) {
        @if(isset($rootCauseMetrics) && count($rootCauseMetrics) > 0)
        const labels = {!! json_encode(array_map(function($key) { return ucfirst(str_replace('_', ' ', $key)); }, array_keys($rootCauseMetrics))) !!};
        const data = {!! json_encode(array_column($rootCauseMetrics, 'count')) !!};
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#8E5EA2', '#C9CBCF', '#FF6384', '#36A2EB'
        ];
        @else
        const labels = ['No Data'];
        const data = [1];
        const colors = ['#E0E0E0'];
        @endif
        
        const rootCauseChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
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
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 11
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

