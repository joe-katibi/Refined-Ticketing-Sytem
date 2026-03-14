@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Sub Department Details')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ $subDepartment->sub_department_name }} - Escalation Metrics</h4>
                    <a href="{{ route('dashboard.index') }}" class="btn btn-secondary float-right">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <!-- Metrics Summary -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="small-box bg-info text-center">
                                <div class="inner">
                                    <h3 style="color: white !important; font-weight: bold;">{{ $metrics['today_received'] }}</h3>
                                    <p style="color: white !important;">Received Today</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-inbox" style="color: white;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-success text-center">
                                <div class="inner">
                                    <h3 style="color: white !important; font-weight: bold;">{{ $metrics['today_closed'] }}</h3>
                                    <p style="color: white !important;">Closed Today</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle" style="color: white;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-primary text-center">
                                <div class="inner">
                                    <h3 style="color: white !important; font-weight: bold;">{{ $metrics['today_closed_within_sla'] }}</h3>
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
                                    <h3 style="color: #212529 !important; font-weight: bold;">{{ $metrics['today_closed_outside_sla'] }}</h3>
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
                                    <h3 style="color: white !important; font-weight: bold;">{{ $metrics['backlog'] }}</h3>
                                    <p style="color: white !important;">Backlog</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-hourglass-half" style="color: white;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="small-box bg-info text-center">
                                <div class="inner">
                                    <h3 style="color: white !important; font-weight: bold;">{{ $metrics['sla_compliance_percentage'] }}%</h3>
                                    <p style="color: white !important;">SLA Compliance</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line" style="color: white;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Open Escalations -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Open Escalations ({{ $openEscalations->count() }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Title</th>
                                                    <th>Status</th>
                                                    <th>Created By</th>
                                                    <th>Assigned To</th>
                                                    <th>Created At</th>
                                                    <th>SLA Deadline</th>
                                                    <th>SLA Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($openEscalations as $escalation)
                                                <tr class="{{ $escalation->sla_deadline && !$escalation->isWithinSla() ? 'table-danger' : '' }}">
                                                    <td>{{ $escalation->escalation_id }}</td>
                                                    <td>{{ $escalation->title }}</td>
                                                    <td>{{ $escalation->status }}</td>
                                                    <td>{{ $escalation->creator ? $escalation->creator->name : 'N/A' }}</td>
                                                    <td>{{ $escalation->assignedUser ? $escalation->assignedUser->name : 'Unassigned' }}</td>
                                                    <td>{{ $escalation->created_at->format('Y-m-d H:i') }}</td>
                                                    <td>{{ $escalation->sla_deadline ? $escalation->sla_deadline->format('Y-m-d H:i') : 'N/A' }}</td>
                                                    <td>
                                                        @if($escalation->sla_deadline)
                                                            @if($escalation->isWithinSla())
                                                                <span class="badge badge-success">
                                                                    {{ $escalation->formattedSlaTimeRemaining() }} remaining
                                                                </span>
                                                            @else
                                                                <span class="badge badge-danger">SLA Breached</span>
                                                            @endif
                                                        @else
                                                            <span class="badge badge-secondary">No SLA</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('escalations.show', $escalation->id) }}" class="btn btn-info btn-xs">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('escalations.edit', $escalation->id) }}" class="btn btn-primary btn-xs">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="9" class="text-center">No open escalations found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recently Closed Escalations -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Recently Closed Escalations</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Title</th>
                                                    <th>Created By</th>
                                                    <th>Closed By</th>
                                                    <th>Created At</th>
                                                    <th>Closed At</th>
                                                    <th>SLA Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($closedEscalations as $escalation)
                                                <tr class="{{ $escalation->sla_breached ? 'table-danger' : 'table-success' }}">
                                                    <td>{{ $escalation->escalation_id }}</td>
                                                    <td>{{ $escalation->title }}</td>
                                                    <td>{{ $escalation->creator ? $escalation->creator->name : 'N/A' }}</td>
                                                    <td>{{ $escalation->closer ? $escalation->closer->name : 'N/A' }}</td>
                                                    <td>{{ $escalation->created_at->format('Y-m-d H:i') }}</td>
                                                    <td>{{ $escalation->closed_at ? $escalation->closed_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                                    <td>
                                                        @if($escalation->sla_breached)
                                                            <span class="badge badge-danger">Outside SLA</span>
                                                        @else
                                                            <span class="badge badge-success">Within SLA</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('escalations.show', $escalation->id) }}" class="btn btn-info btn-xs">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No closed escalations found</td>
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

@push('scripts')
<script>
    $(function() {
        // Auto-refresh the page every 2 minutes
        setTimeout(function() {
            location.reload();
        }, 2 * 60 * 1000);
    });
</script>
@endpush

