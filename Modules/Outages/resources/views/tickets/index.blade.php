@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Outage Tickets')

@section('content')
<div class="container-xxXl flex-grow-1 container-p-y">
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-2">
                    <span class="text-muted fw-light">Outages /</span> Tickets
                </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('outage-tickets.create') }}" class="btn btn-success btn-xs">
                        <i class="bx bx-plus me-1"></i> Create Ticket
                    </a>
                    <a href="{{ route('outages.index') }}" class="btn btn-info btn-xs">
                        <i class="bx bx-list-ul me-1"></i> View Outages
                    </a>
                    <a href="{{ route('outage-dashboard.index') }}" class="btn btn-warning btn-xs">
                        <i class="bx bx-bar-chart-alt-2 me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-filter-alt me-2"></i>Filters
                        </h5>
                        <button class="btn btn-outline-secondary btn-xs" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="true" aria-controls="filtersCollapse">
                            <i class="bx bx-chevron-down"></i>
                        </button>
                    </div>
                </div>
                <div class="collapse show" id="filtersCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('outage-tickets.index') }}">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label" for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}" placeholder="Search tickets...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="status">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="priority">Priority</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="">All Priorities</option>
                                        @foreach($priorities as $priority)
                                            <option value="{{ $priority }}" {{ request('priority') == $priority ? 'selected' : '' }}>
                                                {{ $priority }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="outage_id">Outage</label>
                                    <select class="form-select" id="outage_id" name="outage_id">
                                        <option value="">All Outages</option>
                                        @foreach($outages as $outage)
                                            <option value="{{ $outage->id }}" {{ request('outage_id') == $outage->id ? 'selected' : '' }}>
                                                {{ $outage->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="assigned_team_id">Team</label>
                                    <select class="form-select" id="assigned_team_id" name="assigned_team_id">
                                        <option value="">All Teams</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" {{ request('assigned_team_id') == $team->id ? 'selected' : '' }}>
                                                {{ $team->type_name ?? $team->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <i class="bx bx-search me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('outage-tickets.index') }}" class="btn btn-outline-secondary btn-xs">
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

    <!-- Tickets Table Section -->
    <div class="row">
        <div class="col-12">
            <!-- Tickets Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-receipt me-2"></i>Tickets ({{ $tickets->total() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Outage</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Impact</th>
                                <th>Urgency</th>
                                <th>Assigned Team</th>
                                <th>Assigned To</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td>
                                        <a href="{{ route('outage-tickets.show', $ticket) }}" class="text-decoration-none">
                                            <strong>{{ $ticket->ticket_number }}</strong>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('outages.show', $ticket->outage) }}" class="text-decoration-none">
                                            {{ $ticket->outage->ticket_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $ticket->title }}">
                                            {{ $ticket->title }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-xs bg-{{ $ticket->status == 'Resolved' ? 'success' : ($ticket->status == 'In Progress' ? 'warning' : ($ticket->status == 'On Hold' ? 'secondary' : 'info')) }}">
                                            {{ $ticket->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-xs bg-{{ $ticket->priority == 'Critical' ? 'danger' : ($ticket->priority == 'High' ? 'warning' : ($ticket->priority == 'Medium' ? 'info' : 'secondary')) }}">
                                            {{ $ticket->priority }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-xs bg-{{ $ticket->impact == 'Critical' ? 'danger' : ($ticket->impact == 'High' ? 'warning' : 'info') }}">
                                            {{ $ticket->impact }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-xs bg-{{ $ticket->urgency == 'Critical' ? 'danger' : ($ticket->urgency == 'High' ? 'warning' : 'info') }}">
                                            {{ $ticket->urgency }}
                                        </span>
                                    </td>
                                    <td>{{ $ticket->assignedTeam->type_name ?? $ticket->assignedTeam->name ?? 'Unassigned' }}</td>
                                    <td>{{ $ticket->assignee->name ?? 'Unassigned' }}</td>
                                    <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('outage-tickets.show', $ticket) }}">
                                                    <i class="bx bx-show me-2"></i>View
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('outage-tickets.edit', $ticket) }}">
                                                    <i class="bx bx-edit me-2"></i>Edit
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete('{{ route('outage-tickets.destroy', $ticket) }}')">
                                                    <i class="bx bx-trash me-2"></i>Delete
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <div class="text-center">
                                            <i class="bx bx-info-circle display-4 mb-3"></i>
                                            <h6 class="mb-2">No tickets found matching your criteria.</h6>
                                            <p class="mb-3">Try adjusting your filters or create a new ticket.</p>
                                            <a href="{{ route('outage-tickets.create') }}" class="btn btn-success btn-xs">
                                                <i class="bx bx-plus me-1"></i> Create First Ticket
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
                @if($tickets->hasPages())
                    <div class="card-footer">
                        {{ $tickets->links() }}
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
                    <h6 class="mb-2">Are you sure you want to delete this ticket?</h6>
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
    document.getElementById('deleteForm').setAttribute('action', url);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush

