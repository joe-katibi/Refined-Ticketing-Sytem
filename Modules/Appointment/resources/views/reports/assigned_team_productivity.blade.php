@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Individual Productivity Report - Appointments')

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
                            <i class="fas fa-user-tie text-warning me-2"></i>
                            Individual Productivity Report
                        </h4>
                        <p class="text-muted mb-0">Individual user performance metrics and SLA compliance by assigned users</p>
                    </div>
                    <div>
                        <a href="{{ route('appointment.reports.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Reports
                        </a>
                        <a href="{{ route('appointment.reports.export-excel', ['report_type' => 'assigned_team_productivity', 'startDate' => $startDate, 'endDate' => $endDate]) }}" class="btn btn-success btn-xs">
                            <i class="fas fa-file-excel me-1"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form method="GET" action="{{ route('appointment.reports.assigned-team-productivity') }}" class="mb-4">
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
                                    <h5 class="card-title mb-0">Individual Summary Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="small-box bg-info text-white p-3 rounded">
                                                <h3>{{ $assignedTeamMetrics->count() }}</h3>
                                                <p class="mb-0">Active Users</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-success text-white p-3 rounded">
                                                <h3>{{ $assignedTeamMetrics->sum('total_closed') }}</h3>
                                                <p class="mb-0">Total Closed</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-primary text-white p-3 rounded">
                                                <h3>{{ $assignedTeamMetrics->sum('closed_within_sla') }}</h3>
                                                <p class="mb-0">Within SLA</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-warning text-dark p-3 rounded">
                                                @php
                                                    $totalClosed = $assignedTeamMetrics->sum('total_closed');
                                                    $totalWithinSla = $assignedTeamMetrics->sum('closed_within_sla');
                                                    $overallCompliance = $totalClosed > 0 ? round(($totalWithinSla / $totalClosed) * 100, 1) : 0;
                                                    $overallStatusClass = $overallCompliance >= 80 ? 'success' : ($overallCompliance >= 60 ? 'warning' : 'danger');
                                                @endphp
                                                <h3>{{ $overallCompliance }}%</h3>
                                                <p class="mb-0">Overall SLA</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Productivity Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Individual Performance Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>User Name</th>
                                                    <th class="text-center">Appointments Closed</th>
                                                    <th class="text-center">Closure Rate</th>
                                                    <th class="text-center">Within SLA</th>
                                                    <th class="text-center">Outside SLA</th>
                                                    <th class="text-center">SLA Compliance</th>
                                                    <th class="text-center">Avg Resolution (hrs)</th>
                                                    <th class="text-center">Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($assignedTeamMetrics as $metric)
                                                @php
                                                    $statusClass = $metric->sla_compliance_percentage >= 80 ? 'success' : ($metric->sla_compliance_percentage >= 60 ? 'warning' : 'danger');
                                                    $closureRate = 100; // Since we're only showing users who closed appointments
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                <span class="avatar-initial rounded-circle bg-warning">
                                                                    {{ strtoupper(substr($metric->name, 0, 2)) }}
                                                                </span>
                                                            </div>
                                                            <strong>{{ $metric->name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-success">{{ $metric->total_closed }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-info">{{ $closureRate }}%</span>
                                                        <small class="text-muted d-block">Completion Rate</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-primary">{{ $metric->closed_within_sla }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-warning">{{ $metric->closed_outside_sla }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-{{ $statusClass }}">{{ $metric->sla_compliance_percentage }}%</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-secondary">{{ $metric->avg_resolution_time ?? 'N/A' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($metric->sla_compliance_percentage >= 80)
                                                            <i class="fas fa-star text-success" title="Excellent Performance"></i>
                                                            <i class="fas fa-star text-success" title="Excellent Performance"></i>
                                                            <i class="fas fa-star text-success" title="Excellent Performance"></i>
                                                        @elseif($metric->sla_compliance_percentage >= 60)
                                                            <i class="fas fa-star text-warning" title="Good Performance"></i>
                                                            <i class="fas fa-star text-warning" title="Good Performance"></i>
                                                            <i class="far fa-star text-muted" title="Good Performance"></i>
                                                        @else
                                                            <i class="fas fa-star text-danger" title="Needs Improvement"></i>
                                                            <i class="far fa-star text-muted" title="Needs Improvement"></i>
                                                            <i class="far fa-star text-muted" title="Needs Improvement"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        No individual productivity data available for the selected date range
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
                                            <span class="badge badge-xs bg-warning me-2">Good</span>
                                            <small>60-79% SLA Compliance</small>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="badge badge-xs bg-danger me-2">Needs Improvement</span>
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
