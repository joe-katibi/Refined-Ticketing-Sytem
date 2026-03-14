@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'SLA Report - Escalations')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="fas fa-clock text-primary me-2"></i>
                            SLA Performance Report
                        </h4>
                        <p class="text-muted mb-0">Service Level Agreement compliance and performance metrics</p>
                    </div>
                    <div>
                        <a href="{{ route('escalations.reports.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Reports
                        </a>
                        <a href="{{ route('escalations.reports.export-excel', ['report_type' => 'sla', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-success btn-xs">
                            <i class="fas fa-file-excel me-1"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form method="GET" action="{{ route('escalations.reports.sla') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-md-4">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-xs">
                                        <i class="fas fa-filter me-1"></i>
                                        Apply Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Overall SLA Metrics -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Overall SLA Performance</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-2 col-md-4 col-sm-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body text-center bg-info text-white rounded">
                                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                                        <i class="fas fa-list-alt fa-2x me-2"></i>
                                                        <h3 class="mb-0">{{ $totalEscalations }}</h3>
                                                    </div>
                                                    <p class="mb-0 small">Total Escalations</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body text-center bg-success text-white rounded">
                                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                                        <i class="fas fa-check-circle fa-2x me-2"></i>
                                                        <h3 class="mb-0">{{ $closedEscalations }}</h3>
                                                    </div>
                                                    <p class="mb-0 small">Closed</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body text-center bg-primary text-white rounded">
                                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                                        <i class="fas fa-clock fa-2x me-2"></i>
                                                        <h3 class="mb-0">{{ $withinSla }}</h3>
                                                    </div>
                                                    <p class="mb-0 small">Within SLA</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body text-center bg-warning text-dark rounded">
                                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                                        <i class="fas fa-exclamation-triangle fa-2x me-2"></i>
                                                        <h3 class="mb-0">{{ $outsideSla }}</h3>
                                                    </div>
                                                    <p class="mb-0 small">Outside SLA</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body text-center bg-{{ $slaCompliancePercentage >= 80 ? 'success' : ($slaCompliancePercentage >= 60 ? 'warning' : 'danger') }} text-white rounded">
                                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                                        <i class="fas fa-chart-line fa-2x me-2"></i>
                                                        <h3 class="mb-0">{{ $slaCompliancePercentage }}%</h3>
                                                    </div>
                                                    <p class="mb-0 small">SLA Compliance</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body text-center bg-secondary text-white rounded">
                                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                                        <i class="fas fa-target fa-2x me-2"></i>
                                                        <h3 class="mb-0">4h</h3>
                                                    </div>
                                                    <p class="mb-0 small">SLA Target</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily SLA Breakdown -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Daily SLA Breakdown</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Date</th>
                                                    <th class="text-center">Total</th>
                                                    <th class="text-center">Closed</th>
                                                    <th class="text-center">Within SLA</th>
                                                    <th class="text-center">Outside SLA</th>
                                                    <th class="text-center">SLA Compliance</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($dailyMetrics as $metric)
                                                @php
                                                    $dailyCompliance = $metric->closed > 0 ? round(($metric->within_sla / $metric->closed) * 100, 1) : 0;
                                                    $statusClass = $dailyCompliance >= 80 ? 'success' : ($dailyCompliance >= 60 ? 'warning' : 'danger');
                                                @endphp
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($metric->date)->format('M d, Y') }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-info">{{ $metric->total }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-success">{{ $metric->closed }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-primary">{{ $metric->within_sla }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-warning">{{ $metric->outside_sla }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-{{ $statusClass }}">{{ $dailyCompliance }}%</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($dailyCompliance >= 80)
                                                            <i class="fas fa-check-circle text-success" title="Excellent"></i>
                                                        @elseif($dailyCompliance >= 60)
                                                            <i class="fas fa-exclamation-triangle text-warning" title="Needs Improvement"></i>
                                                        @else
                                                            <i class="fas fa-times-circle text-danger" title="Poor Performance"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        No data available for the selected date range
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
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
@endsection

{{-- Using native HTML5 date inputs instead of Flatpickr for cleaner UI --}}

