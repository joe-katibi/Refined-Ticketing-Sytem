@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Escalation Dashboard')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Escalation Dashboard</h4>
                </div>
                <div class="card-body">
                    <!-- Overall Metrics Card -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title">Overall Metrics (Today)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="small-box bg-info text-center">
                                                <div class="inner">
                                                    <h3 style="color: white !important; font-weight: bold;">{{ $overallMetrics['today_received'] }}</h3>
                                                    <p style="color: white !important;">Received</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-inbox" style="color: white;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-success text-center">
                                                <div class="inner">
                                                    <h3 style="color: white !important; font-weight: bold;">{{ $overallMetrics['today_closed'] }}</h3>
                                                    <p style="color: white !important;">Closed</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-check-circle" style="color: white;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-primary text-center">
                                                <div class="inner">
                                                    <h3 style="color: white !important; font-weight: bold;">{{ $overallMetrics['today_closed_within_sla'] }}</h3>
                                                    <p style="color: white !important;">Within SLA</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-clock" style="color: white;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-warning text-center">
                                                <div class="inner">
                                                    <h3 style="color: #212529 !important; font-weight: bold;">{{ $overallMetrics['today_closed_outside_sla'] }}</h3>
                                                    <p style="color: #212529 !important;">Outside SLA</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-exclamation-triangle" style="color: #212529;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-danger text-center">
                                                <div class="inner">
                                                    <h3 style="color: white !important; font-weight: bold;">{{ $overallMetrics['backlog'] }}</h3>
                                                    <p style="color: white !important;">Backlog</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-tasks" style="color: white;"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-info text-center">
                                                <div class="inner">
                                                    <h3 style="color: white !important; font-weight: bold;">{{ $overallMetrics['sla_compliance_percentage'] }}%</h3>
                                                    <p style="color: white !important;">SLA Compliance</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-chart-line" style="color: white;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($overallMetrics['highest_backlog_dept'])
                                    <div class="mt-4 alert alert-warning">
                                        <i class="fas fa-exclamation-circle"></i> Highest backlog in <strong>{{ $overallMetrics['highest_backlog_dept'] }}</strong> department
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sub Department Metrics -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Sub Department Metrics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Sub Department</th>
                                                    <th>Received Today</th>
                                                    <th>Closed Today</th>
                                                    <th>Within SLA</th>
                                                    <th>Outside SLA</th>
                                                    <th>Backlog</th>
                                                    <th>SLA Compliance</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dashboardData as $subDeptId => $data)
                                                <tr>
                                                    <td>{{ $data['name'] }}</td>
                                                    <td>{{ $data['metrics']['today_received'] }}</td>
                                                    <td>{{ $data['metrics']['today_closed'] }}</td>
                                                    <td>{{ $data['metrics']['today_closed_within_sla'] }}</td>
                                                    <td>{{ $data['metrics']['today_closed_outside_sla'] }}</td>
                                                    <td>{{ $data['metrics']['backlog'] }}</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar {{ $data['metrics']['sla_compliance_percentage'] >= 80 ? 'bg-success' : ($data['metrics']['sla_compliance_percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                                role="progressbar"
                                                                style="width: {{ $data['metrics']['sla_compliance_percentage'] }}%"
                                                                aria-valuenow="{{ $data['metrics']['sla_compliance_percentage'] }}"
                                                                aria-valuemin="0"
                                                                aria-valuemax="100">
                                                                {{ $data['metrics']['sla_compliance_percentage'] }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('dashboard.sub-department', $subDeptId) }}" class="btn btn-info btn-xs">
                                                            <i class="fas fa-eye"></i> Details
                                                        </a>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Auto-refresh the dashboard every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 5 * 60 * 1000);
    });
</script>
@endpush

