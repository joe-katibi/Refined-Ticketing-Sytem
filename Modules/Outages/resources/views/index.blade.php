@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Outages Management')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-2">
                    <span class="text-muted fw-light">Outages /</span> Management
                </h4>
                <div class="d-flex gap-2">
                    @can('view-create-outage')
                    <a href="{{ route('outages.create') }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i> Create Outage
                    </a>
                    @endcan
                    @can('view-dashboard-outage')
                    <a href="{{ route('outage-dashboard.index') }}" class="btn btn-info btn-xs">
                        <i class="bx bx-bar-chart-alt-2 me-1"></i> Dashboard
                    </a>
                    @endcan
                    @can('view-outage-download-reports')
                    <a href="{{ route('outage-reports.index') }}" class="btn btn-warning btn-xs">
                        <i class="bx bx-file me-1"></i> Reports
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">
                        <i class="bx bx-filter-alt me-2"></i>Filters
                    </h5>
                    <button class="btn btn-outline-secondary btn-xs" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="true" aria-controls="filtersCollapse">
                        <i class="bx bx-chevron-up"></i>
                    </button>
                </div>
                <div class="collapse show" id="filtersCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('outages.index') }}">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}" placeholder="Search outages...">
                                </div>
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        @php
                                            $statusOptions = \Modules\Outages\Models\Outage::getStatusOptions();
                                        @endphp
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="ticket_type" class="form-label">Ticket Type</label>
                                    <select class="form-select" id="ticket_type" name="ticket_type">
                                        <option value="">All Types</option>
                                        <option value="regular" {{ request('ticket_type') == 'regular' ? 'selected' : '' }}>Regular Outage</option>
                                        <option value="emergency" {{ request('ticket_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                        <option value="planned_maintenance" {{ request('ticket_type') == 'planned_maintenance' ? 'selected' : '' }}>Planned Maintenance</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="">All Priorities</option>
                                        @foreach($priorities as $priority)
                                            <option value="{{ $priority }}" {{ request('priority') == $priority ? 'selected' : '' }}>
                                                {{ $priority }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="assigned_team_id" class="form-label">Assigned Team</label>
                                    <select class="form-select" id="assigned_team_id" name="assigned_team_id">
                                        <option value="">All Teams</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" {{ request('assigned_team_id') == $team->id ? 'selected' : '' }}>
                                                {{ $team->type_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="olt_id" class="form-label">OLT</label>
                                    <select class="form-select" id="olt_id" name="olt_id">
                                        <option value="">All OLTs</option>
                                        @foreach($olts as $olt)
                                            <option value="{{ $olt->id }}" {{ request('olt_id') == $olt->id ? 'selected' : '' }}>
                                                {{ $olt->name }} ({{ $olt->ip_address }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <i class="bx bx-search me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('outages.index') }}" class="btn btn-outline-secondary btn-xs">
                                            <i class="bx bx-x me-1"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outages Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">
                        <i class="bx bx-list-ul me-2"></i>Outages List
                    </h5>
                    <span class="badge bg-primary">{{ $outages->total() }} Total</span>
                </div>
                <div class="card-body">
                    <div class="responsive-table-wrapper">
                        <table class="table table-hover responsive-table mobile-card-table" id="outagesTable">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th class="d-none-mobile">Type</th>
                                    <th>Title</th>
                                    <th class="d-none-tablet">OLT/Slot/Port</th>
                                    <th>Status</th>
                                    <th class="d-none-mobile">Priority</th>
                                    <th class="d-none-laptop">Impact/Urgency</th>
                                    <th class="d-none-tablet">Assigned To</th>
                                    <th class="d-none-mobile">Start Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($outages as $outage)
                                    <tr>
                                        <td>
                                            <a href="{{ route('outages.show', $outage) }}" class="fw-semibold text-primary">
                                                {{ $outage->ticket_number }}
                                            </a>
                                        </td>
                                        <td>
                                            @php
                                                $typeClass = match($outage->ticket_type) {
                                                    'emergency' => 'bg-danger',
                                                    'planned_maintenance' => 'bg-info',
                                                    'regular' => 'bg-primary',
                                                    default => 'bg-secondary'
                                                };
                                                $typeLabel = match($outage->ticket_type) {
                                                    'emergency' => 'Emergency',
                                                    'planned_maintenance' => 'Planned',
                                                    'regular' => 'Regular',
                                                    default => 'Unknown'
                                                };
                                            @endphp
                                            <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $outage->title }}</span>
                                                <small class="text-muted">{{ Str::limit($outage->description, 40) }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">OLT: <span class="fw-semibold">{{ $outage->olt_id ?? 'N/A' }}</span></small>
                                                <small class="text-muted">Slot: <span class="fw-semibold">{{ $outage->slot_id ?? 'N/A' }}</span></small>
                                                <small class="text-muted">Port: <span class="fw-semibold">{{ $outage->port_id ?? 'N/A' }}</span></small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($outage->status) {
                                                    'support-unconfirmed-outage' => 'bg-warning',
                                                    'noc-confirmed-outage' => 'bg-info',
                                                    'noc-rejected' => 'bg-danger',
                                                    'infra-dispatched' => 'bg-primary',
                                                    'infra-confirmed-outage' => 'bg-info',
                                                    'infra-resolved' => 'bg-success',
                                                    'noc-restore-confirmed' => 'bg-success',
                                                    'noc-incident-outage' => 'bg-danger',
                                                    'support-follow-up' => 'bg-warning',
                                                    'support-closed' => 'bg-secondary',
                                                    'auto-monitor-detected' => 'bg-info',
                                                    'awaiting-customer-confirmation' => 'bg-warning',
                                                    'partial-restore' => 'bg-warning',
                                                    'awaiting-field-access' => 'bg-warning',
                                                    'scheduled-maintenance' => 'bg-dark',
                                                    'vendor-escalated' => 'bg-danger',
                                                    default => 'bg-primary'
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $outage->status_display }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $priorityClass = match($outage->priority) {
                                                    'Critical' => 'text-danger',
                                                    'High' => 'text-warning',
                                                    'Medium' => 'text-info',
                                                    'Low' => 'text-success',
                                                    default => 'text-muted'
                                                };
                                            @endphp
                                            <span class="fw-semibold {{ $priorityClass }}">{{ $outage->priority }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">Impact: <span class="fw-semibold">{{ $outage->impact ?? 'N/A' }}</span></small>
                                                <small class="text-muted">Urgency: <span class="fw-semibold">{{ $outage->urgency ?? 'N/A' }}</span></small>
                                                @if($outage->total_customers_affected)
                                                    <small class="text-primary fw-semibold">Total: {{ number_format($outage->total_customers_affected) }} customers</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($outage->assignee)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($outage->assignee->name, 0, 1) }}</span>
                                                    </div>
                                                    <span>{{ $outage->assignee->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $outage->start_time->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    @can('view-view-outage')
                                                    <a class="dropdown-item" href="{{ route('outages.show', $outage) }}">
                                                        <i class="bx bx-show me-2"></i> View
                                                    </a>
                                                    @endcan
                                                    @can('view-edit-outage')
                                                    <a class="dropdown-item" href="{{ route('outages.edit', $outage) }}">
                                                        <i class="bx bx-edit me-2"></i> Edit
                                                    </a>
                                                    @endcan
                                                    @can('view-history-outage')
                                                    <a class="dropdown-item" href="{{ route('outages.activity', $outage) }}">
                                                        <i class="bx bx-history me-2"></i> Activity History
                                                    </a>
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bx bx-info-circle display-4 mb-3"></i>
                                                <h6 class="mb-2">No outages found matching your criteria.</h6>
                                                <p class="mb-3">Try adjusting your filters or create a new outage.</p>
                                                <a href="{{ route('outages.create') }}" class="btn btn-primary btn-xs">
                                                    <i class="bx bx-plus me-1"></i> Create First Outage
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Mobile Card View -->
                    <div class="mobile-card-view d-none">
                        @forelse($outages as $outage)
                            <div class="mobile-card">
                                <div class="mobile-card-header">
                                    <a href="{{ route('outages.show', $outage) }}" class="fw-semibold text-primary">
                                        {{ $outage->ticket_number }}
                                    </a>
                                </div>
                                
                                <div class="mobile-card-row">
                                    <div class="mobile-card-label">Type</div>
                                    <div class="mobile-card-value">
                                        @php
                                            $typeClass = match($outage->ticket_type) {
                                                'emergency' => 'bg-danger',
                                                'planned_maintenance' => 'bg-info',
                                                'regular' => 'bg-primary',
                                                default => 'bg-secondary'
                                            };
                                            $typeLabel = match($outage->ticket_type) {
                                                'emergency' => 'Emergency',
                                                'planned_maintenance' => 'Planned',
                                                'regular' => 'Regular',
                                                default => 'Unknown'
                                            };
                                        @endphp
                                        <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                    </div>
                                </div>
                                
                                <div class="mobile-card-row">
                                    <div class="mobile-card-label">Title</div>
                                    <div class="mobile-card-value">
                                        <div class="fw-semibold">{{ $outage->title }}</div>
                                        <small class="text-muted">{{ Str::limit($outage->description, 60) }}</small>
                                    </div>
                                </div>
                                
                                <div class="mobile-card-row">
                                    <div class="mobile-card-label">Status</div>
                                    <div class="mobile-card-value">
                                        @php
                                            $statusClass = match($outage->status) {
                                                'support-unconfirmed-outage' => 'bg-warning',
                                                'noc-confirmed-outage' => 'bg-info',
                                                'noc-rejected' => 'bg-danger',
                                                'infra-dispatched' => 'bg-primary',
                                                'infra-confirmed-outage' => 'bg-info',
                                                'infra-resolved' => 'bg-success',
                                                'noc-restore-confirmed' => 'bg-success',
                                                'noc-incident-outage' => 'bg-danger',
                                                'support-follow-up' => 'bg-warning',
                                                'support-closed' => 'bg-secondary',
                                                'auto-monitor-detected' => 'bg-info',
                                                'awaiting-customer-confirmation' => 'bg-warning',
                                                'partial-restore' => 'bg-warning',
                                                'awaiting-field-access' => 'bg-warning',
                                                'scheduled-maintenance' => 'bg-dark',
                                                'vendor-escalated' => 'bg-danger',
                                                default => 'bg-primary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $outage->status_display }}</span>
                                    </div>
                                </div>
                                
                                <div class="mobile-card-row">
                                    <div class="mobile-card-label">Priority</div>
                                    <div class="mobile-card-value">
                                        @php
                                            $priorityClass = match($outage->priority) {
                                                'Critical' => 'text-danger',
                                                'High' => 'text-warning',
                                                'Medium' => 'text-info',
                                                'Low' => 'text-success',
                                                default => 'text-muted'
                                            };
                                        @endphp
                                        <span class="fw-semibold {{ $priorityClass }}">{{ $outage->priority }}</span>
                                    </div>
                                </div>
                                
                                <div class="mobile-card-row">
                                    <div class="mobile-card-label">Start Time</div>
                                    <div class="mobile-card-value">
                                        <small class="text-muted">{{ $outage->start_time->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                                
                                <div class="mobile-card-row">
                                    <div class="mobile-card-label">Actions</div>
                                    <div class="mobile-card-value">
                                        <div class="btn-group-responsive">
                                            @can('view-view-outage')
                                            <a href="{{ route('outages.show', $outage) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-show me-1"></i> View
                                            </a>
                                            @endcan
                                            @can('view-edit-outage')
                                            <a href="{{ route('outages.edit', $outage) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bx-edit me-1"></i> Edit
                                            </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="table-empty-state">
                                <i class="bx bx-info-circle"></i>
                                <h5>No outages found</h5>
                                <p>Try adjusting your filters or create a new outage.</p>
                                <a href="{{ route('outages.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Create First Outage
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if($outages->hasPages())
                    <div class="card-footer">
                        {{ $outages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bx bx-trash me-2 text-danger"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="bx bx-error-circle text-danger display-4 mb-3"></i>
                    <h6 class="mb-2">Are you sure you want to delete this outage?</h6>
                    <p class="text-muted mb-0">This action cannot be undone and will permanently remove all associated data.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-xs" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-xs">
                        <i class="bx bx-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
function confirmDelete(url) {
    $('#deleteForm').attr('action', url);
    $('#deleteModal').modal('show');
}
</script>
@endpush

