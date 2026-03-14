@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Assigned Outages')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-2">
                    <span class="text-muted fw-light">Outages /</span> Assigned Outages
                </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('outages.create') }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i> Create Outage
                    </a>
                    <a href="{{ route('outage-dashboard.index') }}" class="btn btn-info btn-xs">
                        <i class="bx bx-bar-chart-alt-2 me-1"></i> Dashboard
                    </a>
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
                        <form method="GET" action="{{ route('outages.assigned') }}">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="Search by title, ticket number, or description">
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Status</option>
                                        @foreach($statusOptions as $key => $label)
                                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
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
                                <div class="col-md-2">
                                    <label for="assigned_team_id" class="form-label">Assigned Team</label>
                                    <select class="form-select" id="assigned_team_id" name="assigned_team_id">
                                        <option value="">All Teams</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" {{ request('assigned_team_id') == $team->id ? 'selected' : '' }}>
                                                {{ $team->team_name }}
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
                                        <a href="{{ route('outages.assigned') }}" class="btn btn-outline-secondary btn-xs">
                                            <i class="bx bx-refresh me-1"></i> Clear
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

    <!-- Outages List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">
                        <i class="bx bx-list-ul me-2"></i>Assigned Outages ({{ $outages->total() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($outages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Assigned Team</th>
                                        <th>Assignee</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outages as $outage)
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-primary">{{ $outage->ticket_number }}</span>
                                                <br>
                                                <small class="text-muted">{{ $outage->ticket_type_display }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ Str::limit($outage->title, 50) }}</div>
                                                @if($outage->description)
                                                    <small class="text-muted">{{ Str::limit($outage->description, 80) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-xs bg-{{ 
                                                    $outage->status == 'support-closed' ? 'success' : 
                                                    ($outage->status == 'infra-resolved' ? 'info' : 
                                                    ($outage->status == 'noc-rejected' ? 'danger' : 'warning')) 
                                                }}">
                                                    {{ $outage->status_display }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-xs bg-{{ 
                                                    $outage->priority == 'Critical' ? 'danger' : 
                                                    ($outage->priority == 'High' ? 'warning' : 
                                                    ($outage->priority == 'Medium' ? 'info' : 'secondary')) 
                                                }}">
                                                    {{ $outage->priority }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($outage->assignedTeam)
                                                    <span class="fw-medium">{{ $outage->assignedTeam->team_name }}</span>
                                                @else
                                                    <span class="text-muted">Not assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($outage->assignee)
                                                    <span class="fw-medium">{{ $outage->assignee->name }}</span>
                                                @else
                                                    <span class="text-muted">Not assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-medium">{{ $outage->created_at->format('M d, Y') }}</span>
                                                <br>
                                                <small class="text-muted">{{ $outage->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('outages.show', $outage->id) }}">
                                                                <i class="bx bx-show me-2"></i> View
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('outages.edit', $outage->id) }}">
                                                                <i class="bx bx-edit me-2"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('outages.activity', $outage->id) }}">
                                                                <i class="bx bx-history me-2"></i> History
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $outages->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-search bx-lg text-muted mb-3"></i>
                            <h5 class="text-muted">No assigned outages found</h5>
                            <p class="text-muted">Try adjusting your filters or create a new outage.</p>
                            <a href="{{ route('outages.create') }}" class="btn btn-primary btn-xs">
                                <i class="bx bx-plus me-1"></i> Create Outage
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#status, #priority, #assigned_team_id').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection

