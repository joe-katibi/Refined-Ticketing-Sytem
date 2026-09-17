@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Appointment Dashboard')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Appointment Dashboard</h4>
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
                                                    <h3 class="text-white">{{ $overallMetrics['today_received'] }}</h3>
                                                    <p class="text-white">Received</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-inbox"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-success text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $overallMetrics['today_closed'] }}</h3>
                                                    <p class="text-white">Closed</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-primary text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $overallMetrics['today_closed_within_sla'] }}</h3>
                                                    <p class="text-white">Within SLA</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-warning text-center">
                                                <div class="inner">
                                                    <h3 class="text-dark">{{ $overallMetrics['today_closed_outside_sla'] }}</h3>
                                                    <p class="text-dark">Outside SLA</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-danger text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $overallMetrics['backlog'] }}</h3>
                                                    <p class="text-white">Backlog</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-tasks"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="small-box bg-info text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $overallMetrics['sla_compliance_percentage'] }}%</h3>
                                                    <p class="text-white">SLA Compliance</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-chart-line"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TAT Metrics Row (12 hours) -->
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <h5>TAT Metrics (12 Hours)</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small-box bg-primary text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $overallMetrics['within_tat'] }}</h3>
                                                    <p class="text-white">Within TAT</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small-box bg-warning text-center">
                                                <div class="inner">
                                                    <h3 class="text-dark">{{ $overallMetrics['outside_tat'] }}</h3>
                                                    <p class="text-dark">Outside TAT</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small-box bg-info text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $overallMetrics['tat_compliance_percentage'] }}%</h3>
                                                    <p class="text-white">TAT Compliance</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-chart-line"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($overallMetrics['highest_backlog_dept'])
                                    <div class="mt-4 alert alert-warning">
                                        <i class="fas fa-exclamation-circle"></i> Highest backlog in <strong>{{ $overallMetrics['highest_backlog_dept'] }}</strong> sub team
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sub Department Metrics -->
                    <div class="row">
                        @foreach($subDepartments as $subDepartment)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">{{ $dashboardData[$subDepartment->id]['name'] }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="small-box bg-info text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $dashboardData[$subDepartment->id]['metrics']['today_received'] }}</h3>
                                                    <p class="text-white">Received</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small-box bg-success text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $dashboardData[$subDepartment->id]['metrics']['today_closed'] }}</h3>
                                                    <p class="text-white">Closed</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small-box bg-danger text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $dashboardData[$subDepartment->id]['metrics']['backlog'] }}</h3>
                                                    <p class="text-white">Backlog</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="small-box bg-primary text-center">
                                                <div class="inner">
                                                    <h3 class="text-white">{{ $dashboardData[$subDepartment->id]['metrics']['today_closed_within_sla'] }}</h3>
                                                    <p class="text-white">Within SLA</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small-box bg-warning text-center">
                                                <div class="inner">
                                                    <h3 class="text-dark">{{ $dashboardData[$subDepartment->id]['metrics']['today_closed_outside_sla'] }}</h3>
                                                    <p class="text-dark">Outside SLA</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <h6>SLA Compliance: {{ $dashboardData[$subDepartment->id]['metrics']['sla_compliance_percentage'] }}%</h6>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $dashboardData[$subDepartment->id]['metrics']['sla_compliance_percentage'] }}%"
                                                aria-valuenow="{{ $dashboardData[$subDepartment->id]['metrics']['sla_compliance_percentage'] }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                                {{ $dashboardData[$subDepartment->id]['metrics']['sla_compliance_percentage'] }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Sub Team Metrics -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users text-primary me-2"></i>
                                        Sub Team Metrics
                                    </h5>
                                    <p class="text-muted mb-0">Performance metrics for individual sub teams</p>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>SUB TEAM</th>
                                                    <th>RECEIVED TODAY</th>
                                                    <th>CLOSED TODAY</th>
                                                    <th>WITHIN SLA</th>
                                                    <th>OUTSIDE SLA</th>
                                                    <th>BACKLOG</th>
                                                    <th>SLA COMPLIANCE</th>
                                                    <th>ACTIONS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($subTeams as $subTeam)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $subTeamData[$subTeam->id]['name'] }}</strong>
                                                        <br><small class="text-muted">Sub Team</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-info fs-6">{{ $subTeamData[$subTeam->id]['metrics']['today_received'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-success fs-6">{{ $subTeamData[$subTeam->id]['metrics']['today_closed'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-primary fs-6">{{ $subTeamData[$subTeam->id]['metrics']['today_closed_within_sla'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-warning fs-6">{{ $subTeamData[$subTeam->id]['metrics']['today_closed_outside_sla'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-danger fs-6">{{ $subTeamData[$subTeam->id]['metrics']['backlog'] }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress me-2" style="width: 60px; height: 20px;">
                                                                <div class="progress-bar bg-success" role="progressbar"
                                                                    style="width: {{ $subTeamData[$subTeam->id]['metrics']['sla_compliance_percentage'] }}%"
                                                                    aria-valuenow="{{ $subTeamData[$subTeam->id]['metrics']['sla_compliance_percentage'] }}"
                                                                    aria-valuemin="0"
                                                                    aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small>{{ $subTeamData[$subTeam->id]['metrics']['sla_compliance_percentage'] }}%</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('appointment.reports.sub-team-productivity', ['team_id' => $subTeam->id]) }}" 
                                                           class="btn btn-outline-primary btn-xs" 
                                                           title="View Sub-Team Details">
                                                            <i class="fas fa-eye"></i>
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

                    <!-- Assigned Team Metrics -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clipboard-list text-success me-2"></i>
                                        Assigned Team Metrics
                                    </h5>
                                    <p class="text-muted mb-0">Performance metrics for teams assigned to appointments</p>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-success">
                                                <tr>
                                                    <th>ASSIGNED TEAM</th>
                                                    <th>RECEIVED TODAY</th>
                                                    <th>CLOSED TODAY</th>
                                                    <th>WITHIN SLA</th>
                                                    <th>OUTSIDE SLA</th>
                                                    <th>BACKLOG</th>
                                                    <th>SLA COMPLIANCE</th>
                                                    <th>ACTIONS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($assignedTeams as $team)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $assignedTeamData[$team->id]['name'] }}</strong>
                                                        <br><small class="text-muted">Assigned Team</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-info fs-6">{{ $assignedTeamData[$team->id]['metrics']['today_received'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-success fs-6">{{ $assignedTeamData[$team->id]['metrics']['today_closed'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-primary fs-6">{{ $assignedTeamData[$team->id]['metrics']['today_closed_within_sla'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-warning fs-6">{{ $assignedTeamData[$team->id]['metrics']['today_closed_outside_sla'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-xs bg-danger fs-6">{{ $assignedTeamData[$team->id]['metrics']['backlog'] }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress me-2" style="width: 60px; height: 20px;">
                                                                <div class="progress-bar bg-success" role="progressbar"
                                                                    style="width: {{ $assignedTeamData[$team->id]['metrics']['sla_compliance_percentage'] }}%"
                                                                    aria-valuenow="{{ $assignedTeamData[$team->id]['metrics']['sla_compliance_percentage'] }}"
                                                                    aria-valuemin="0"
                                                                    aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                            <small>{{ $assignedTeamData[$team->id]['metrics']['sla_compliance_percentage'] }}%</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('appointment.reports.assigned-team-productivity', ['team_id' => $team->id]) }}" 
                                                           class="btn btn-outline-success btn-xs" 
                                                           title="View Assigned Team Details">
                                                            <i class="fas fa-eye"></i>
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

                    <!-- Open Tickets Aging by Region / Sub Category -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Open Tickets — Time Since Raised, by Region &amp; Sub Category</h5>
                                    <p class="text-muted mb-0 small">Still-open appointments only, aged from creation to now</p>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr class="table-primary">
                                                    <th>Region</th>
                                                    <th class="text-center">Total</th>
                                                    @foreach($openTicketsAging['boundaries'] as $boundary)
                                                        <th class="text-center">{{ $boundary }} hrs</th>
                                                    @endforeach
                                                    <th class="text-center">&gt; {{ end($openTicketsAging['boundaries']) }} hrs</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($openTicketsAging['regions'] as $regionName => $region)
                                                    <tr class="fw-bold">
                                                        <td>{{ $regionName }}</td>
                                                        <td class="text-center">{{ $region['total'] }}</td>
                                                        @foreach($region['buckets'] as $count)
                                                            <td class="text-center">{{ $count ?: '' }}</td>
                                                        @endforeach
                                                    </tr>
                                                    @foreach($region['sub_categories'] as $subCategoryName => $subCategory)
                                                        <tr>
                                                            <td class="ps-4 text-muted">{{ $subCategoryName }}</td>
                                                            <td class="text-center">{{ $subCategory['total'] }}</td>
                                                            @foreach($subCategory['buckets'] as $count)
                                                                <td class="text-center">{{ $count ?: '' }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                @empty
                                                    <tr>
                                                        <td colspan="{{ count($openTicketsAging['boundaries']) + 3 }}" class="text-center text-muted">
                                                            No open tickets found.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            @if(count($openTicketsAging['regions']) > 0)
                                                <tfoot>
                                                    <tr class="table-primary fw-bold">
                                                        <td>Grand Total</td>
                                                        <td class="text-center">{{ $openTicketsAging['grand_total']['total'] }}</td>
                                                        @foreach($openTicketsAging['grand_total']['buckets'] as $count)
                                                            <td class="text-center">{{ $count ?: '' }}</td>
                                                        @endforeach
                                                    </tr>
                                                </tfoot>
                                            @endif
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
$(document).ready(function() {
    // Additional dashboard JavaScript can be added here
});
</script>
@endpush

