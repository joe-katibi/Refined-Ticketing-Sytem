@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'SLA Compliance Analysis')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-0">SLA Compliance Analysis</h2>
                </div>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('outages.index') }}" class="text-decoration-none">Outages</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('outage-reports.index') }}" class="text-decoration-none">Reports</a></li>
                            <li class="breadcrumb-item active">SLA</li>
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
                                    <a href="{{ route('outage-reports.sla.export', request()->query()) }}" class="btn btn-success px-4">
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

    <!-- SLA Overview Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-chart text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 fw-normal">Total Outages</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalOutages ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-error text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 fw-normal">SLA Breached</h6>
                            <h3 class="mb-0 fw-bold">{{ $slaBreachedOutages ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 fw-normal">Compliance Rate</h6>
                            <h3 class="mb-0 fw-bold">{{ $complianceRate ?? '100' }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-time text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 fw-normal">Avg Resolution</h6>
                            <h3 class="mb-0 fw-bold">2.5h</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Analysis Tables -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-error-circle text-primary fs-5 me-2"></i>
                        <h5 class="mb-0 fw-semibold">SLA Compliance by Priority</h5>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold">Priority</th>
                                    <th class="border-0 fw-semibold">Total</th>
                                    <th class="border-0 fw-semibold">Breached</th>
                                    <th class="border-0 fw-semibold">Compliance Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($slaByPriority))
                                    @foreach($slaByPriority as $priority => $data)
                                    <tr>
                                        <td>
                                            @if($priority == 'Critical')
                                                <span class="badge badge-xs bg-danger">{{ $priority }}</span>
                                            @elseif($priority == 'High')
                                                <span class="badge badge-xs bg-warning">{{ $priority }}</span>
                                            @elseif($priority == 'Medium')
                                                <span class="badge badge-xs bg-info">{{ $priority }}</span>
                                            @else
                                                <span class="badge badge-xs bg-success">{{ $priority }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-medium">{{ $data['total'] ?? 0 }}</td>
                                        <td class="fw-medium">{{ $data['breached'] ?? 0 }}</td>
                                        <td>
                                            <span class="fw-medium">{{ $data['compliance_rate'] ?? '100.00' }}%</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td><span class="badge badge-xs bg-danger">Critical</span></td>
                                        <td class="fw-medium">1</td>
                                        <td class="fw-medium">0</td>
                                        <td><span class="fw-medium">100.00%</span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge badge-xs bg-warning">High</span></td>
                                        <td class="fw-medium">1</td>
                                        <td class="fw-medium">0</td>
                                        <td><span class="fw-medium">100.00%</span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge badge-xs bg-success">Low</span></td>
                                        <td class="fw-medium">4</td>
                                        <td class="fw-medium">0</td>
                                        <td><span class="fw-medium">100.00%</span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge badge-xs bg-info">Medium</span></td>
                                        <td class="fw-medium">1</td>
                                        <td class="fw-medium">0</td>
                                        <td><span class="fw-medium">100.00%</span></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-group text-primary fs-5 me-2"></i>
                        <h5 class="mb-0 fw-semibold">SLA Compliance by Team</h5>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold">Team</th>
                                    <th class="border-0 fw-semibold">Total</th>
                                    <th class="border-0 fw-semibold">Breached</th>
                                    <th class="border-0 fw-semibold">Compliance Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($slaByTeam) && count($slaByTeam) > 0)
                                    @foreach($slaByTeam as $team)
                                    <tr>
                                        <td class="fw-medium">{{ $team->team_name }}</td>
                                        <td class="fw-medium">{{ $team->total }}</td>
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
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No data available for the selected period</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent SLA Breached Outages -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-error text-danger fs-5 me-2"></i>
                            <h5 class="mb-0 fw-semibold">Recent SLA Breached Outages</h5>
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
                                    <th class="border-0 fw-semibold">Ticket Number</th>
                                    <th class="border-0 fw-semibold">Title</th>
                                    <th class="border-0 fw-semibold">Priority</th>
                                    <th class="border-0 fw-semibold">Assigned Team</th>
                                    <th class="border-0 fw-semibold">Start Time</th>
                                    <th class="border-0 fw-semibold">SLA Breach Time</th>
                                    <th class="border-0 fw-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($breachedOutages) && count($breachedOutages) > 0)
                                    @foreach($breachedOutages as $outage)
                                    <tr>
                                        <td>
                                            <a href="{{ route('outages.show', $outage) }}" class="text-decoration-none fw-medium">
                                                {{ $outage->ticket_number }}
                                            </a>
                                        </td>
                                        <td class="fw-medium">{{ $outage->title }}</td>
                                        <td>
                                            @if($outage->priority == 'Critical')
                                                <span class="badge badge-xs bg-danger">{{ $outage->priority }}</span>
                                            @elseif($outage->priority == 'High')
                                                <span class="badge badge-xs bg-warning">{{ $outage->priority }}</span>
                                            @elseif($outage->priority == 'Medium')
                                                <span class="badge badge-xs bg-info">{{ $outage->priority }}</span>
                                            @else
                                                <span class="badge badge-xs bg-success">{{ $outage->priority }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $outage->assignedTeam->type_name ?? 'Unassigned' }}</td>
                                        <td>{{ $outage->start_time ? date('M d, Y H:i', strtotime($outage->start_time)) : 'N/A' }}</td>
                                        <td>{{ $outage->sla_breach_time ? date('M d, Y H:i', strtotime($outage->sla_breach_time)) : 'N/A' }}</td>
                                        <td>
                                            @if($outage->status == 'Resolved')
                                                <span class="badge badge-xs bg-success">{{ $outage->status }}</span>
                                            @elseif($outage->status == 'In Progress')
                                                <span class="badge badge-xs bg-warning">{{ $outage->status }}</span>
                                            @else
                                                <span class="badge badge-xs bg-info">{{ $outage->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="bx bx-check-circle fs-1 text-success mb-3 d-block"></i>
                                            <h6 class="mb-1">No SLA breached outages found</h6>
                                            <small>All outages are within SLA compliance for the selected period</small>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('outage-reports.index') }}" class="btn btn-outline-secondary btn-xs">
                            <i class="bx bx-arrow-back me-1"></i>Back to Reports
                        </a>
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
    // Initialize any additional functionality here
});
</script>
@endpush

