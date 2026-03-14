@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Final Reason Report - Appointments')

{{-- Using native HTML5 date inputs instead of Flatpickr for cleaner UI --}}

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
                            <i class="fas fa-flag text-danger me-2"></i>
                            Final Reason Analysis Report
                        </h4>
                        <p class="text-muted mb-0">Appointment outcomes breakdown by final reasons and resolution patterns</p>
                    </div>
                    <div>
                        <a href="{{ route('appointment.reports.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Reports
                        </a>
                        <a href="{{ route('appointment.reports.export-excel', ['report_type' => 'final_reason', 'startDate' => $startDate, 'endDate' => $endDate]) }}" class="btn btn-success btn-xs">
                            <i class="fas fa-file-excel me-1"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form method="GET" action="{{ route('appointment.reports.final-reason') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="startDate" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="startDate" name="startDate" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-4">
                                <label for="endDate" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="endDate" name="endDate" value="{{ $endDate }}">
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

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Final Reason Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="small-box bg-info text-white p-3 rounded">
                                                <h3>{{ $finalReasonMetrics->count() }}</h3>
                                                <p class="mb-0">Final Reasons</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-primary text-white p-3 rounded">
                                                <h3>{{ $finalReasonMetrics->sum('total_appointments') }}</h3>
                                                <p class="mb-0">Total Appointments</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-success text-white p-3 rounded">
                                                <h3>{{ $finalReasonMetrics->sum('closed_appointments') }}</h3>
                                                <p class="mb-0">Closed Appointments</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-warning text-dark p-3 rounded">
                                                <h3>{{ $finalReasonMetrics->sum('open_appointments') }}</h3>
                                                <p class="mb-0">Open Appointments</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Reason Analysis Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Final Reason Analysis Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Final Reason</th>
                                                    <th class="text-center">Total</th>
                                                    <th class="text-center">Closed</th>
                                                    <th class="text-center">Open</th>
                                                    <th class="text-center">Within SLA</th>
                                                    <th class="text-center">SLA Compliance</th>
                                                    <th class="text-center">Avg Resolution (hrs)</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($finalReasonMetrics as $metric)
                                                @php
                                                    $statusClass = $metric->sla_compliance_percentage >= 80 ? 'success' : ($metric->sla_compliance_percentage >= 60 ? 'warning' : 'danger');
                                                    $closureRate = $metric->total_appointments > 0 ? round(($metric->closed_appointments / $metric->total_appointments) * 100, 1) : 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                <span class="avatar-initial rounded-circle bg-danger">
                                                                    <i class="fas fa-flag"></i>
                                                                </span>
                                                            </div>
                                                            <strong>{{ $metric->final_reason_name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-info">{{ $metric->total_appointments }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-success">{{ $metric->closed_appointments }}</span>
                                                        <small class="text-muted d-block">({{ $closureRate }}%)</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-warning">{{ $metric->open_appointments }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-primary">{{ $metric->closed_within_sla }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-{{ $statusClass }}">{{ $metric->sla_compliance_percentage }}%</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-secondary">{{ $metric->avg_resolution_time ?? 'N/A' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($metric->sla_compliance_percentage >= 80)
                                                            <i class="fas fa-check-circle text-success" title="Excellent"></i>
                                                        @elseif($metric->sla_compliance_percentage >= 60)
                                                            <i class="fas fa-exclamation-triangle text-warning" title="Needs Attention"></i>
                                                        @else
                                                            <i class="fas fa-times-circle text-danger" title="Critical"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        No final reason data available for the selected date range
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th>Total</th>
                                                    <th class="text-center">
                                                        <span class="badge badge-xs bg-info">{{ $finalReasonMetrics->sum('total_appointments') }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <span class="badge badge-xs bg-success">{{ $finalReasonMetrics->sum('closed_appointments') }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <span class="badge badge-xs bg-warning">{{ $finalReasonMetrics->sum('open_appointments') }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <span class="badge badge-xs bg-primary">{{ $finalReasonMetrics->sum('closed_within_sla') }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        @php
                                                            $totalClosed = $finalReasonMetrics->sum('closed_appointments');
                                                            $totalWithinSla = $finalReasonMetrics->sum('closed_within_sla');
                                                            $overallCompliance = $totalClosed > 0 ? round(($totalWithinSla / $totalClosed) * 100, 1) : 0;
                                                            $overallStatusClass = $overallCompliance >= 80 ? 'success' : ($overallCompliance >= 60 ? 'warning' : 'danger');
                                                        @endphp
                                                        <span class="badge badge-xs bg-{{ $overallStatusClass }}">{{ $overallCompliance }}%</span>
                                                    </th>
                                                    <th class="text-center">
                                                        @php
                                                            $overallAvgResolution = $finalReasonMetrics->where('avg_resolution_time', '!=', null)->avg('avg_resolution_time');
                                                        @endphp
                                                        <span class="badge badge-xs bg-secondary">{{ $overallAvgResolution ? round($overallAvgResolution, 2) : 'N/A' }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        @if($overallCompliance >= 80)
                                                            <i class="fas fa-thumbs-up text-success"></i>
                                                        @elseif($overallCompliance >= 60)
                                                            <i class="fas fa-thumbs-down text-warning"></i>
                                                        @else
                                                            <i class="fas fa-exclamation-circle text-danger"></i>
                                                        @endif
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Legend -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Performance Legend:</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="badge badge-xs bg-success me-2">Excellent</span>
                                            <small>≥ 80% SLA Compliance</small>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="badge badge-xs bg-warning me-2">Needs Attention</span>
                                            <small>60-79% SLA Compliance</small>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="badge badge-xs bg-danger me-2">Critical</span>
                                            <small>< 60% SLA Compliance</small>
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
@endsection

{{-- Using native HTML5 date inputs instead of Flatpickr for cleaner UI --}}
