@extends('layouts/contentNavbarLayout')

@section('title', 'Site Visit Final Reasons')

@section('page-script')
<script src="{{asset('assets/js/pages-account-settings-account.js')}}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Site Visit Final Reasons</h5>
                <div>
                    <a href="{{ route('site-visit.final-reasons.create') }}" class="btn btn-primary btn-xs">
                        <i class="ti ti-plus me-1"></i>Add Final Reason
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($reasons->count() > 0)
                    <div class="table-responsive">
                        <table id="final-reasons-table" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reasons as $index => $reason)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        <i class="ti ti-list-check"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $reason->final_reason_name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ Str::limit($reason->final_reason_description ?? 'No description', 50) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-xs bg-{{ $reason->final_reason_status === 'Active' ? 'success' : 'secondary' }}">
                                                {{ $reason->final_reason_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-info">
                                                        {{ substr($reason->creator->name ?? 'N/A', 0, 2) }}
                                                    </span>
                                                </div>
                                                <span>{{ $reason->creator->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $reason->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('site-visit.final-reasons.show', $reason->id) }}">
                                                        <i class="ti ti-eye me-1"></i>View
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('site-visit.final-reasons.edit', $reason->id) }}">
                                                        <i class="ti ti-pencil me-1"></i>Edit
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('site-visit.final-reasons.destroy', $reason->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this final reason?')">
                                                            <i class="ti ti-trash me-1"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="ti ti-list-check ti-lg"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">No Site Visit Final Reasons Found</h5>
                        <p class="text-muted mb-4">
                            No site visit final reasons found. <a href="{{ route('site-visit.final-reasons.create') }}">Create one now</a>.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($reasons->count() > 0)
<script>
$(document).ready(function() {
    $('#final-reasons-table').DataTable({
        responsive: true,
        order: [[5, 'desc']], // Sort by created date descending
        pageLength: 25,
        language: {
            search: "Search final reasons:",
            lengthMenu: "Show _MENU_ final reasons per page",
            info: "Showing _START_ to _END_ of _TOTAL_ final reasons",
            infoEmpty: "No final reasons available",
            infoFiltered: "(filtered from _MAX_ total final reasons)"
        }
    });
});
</script>
@endif
@endsection

