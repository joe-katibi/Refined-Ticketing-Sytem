@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Outage Final Reasons')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-list-alt text-primary me-2"></i>
                        Outage Final Reasons
                    </h3>
                    <a href="{{ route('outages.final-reasons.create') }}" class="btn btn-primary btn-xs">
                        <i class="fas fa-plus me-1"></i> Add New Reason
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="final-reasons-table" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Edited By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reasons as $reason)
                                    <tr>
                                        <td>{{ $reason->id }}</td>
                                        <td>
                                            <strong>{{ $reason->final_reason_name }}</strong>
                                        </td>
                                        <td>
                                            @if($reason->final_reason_description)
                                                <span title="{{ $reason->final_reason_description }}">
                                                    {{ Str::limit($reason->final_reason_description, 50) }}
                                                </span>
                                            @else
                                                <span class="text-muted">No description</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($reason->final_reason_status == 'Active')
                                                <span class="badge badge-xs bg-success">
                                                    <i class="fas fa-check me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge badge-xs bg-danger">
                                                    <i class="fas fa-times me-1"></i>Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($reason->creator)
                                                <span class="text-primary">{{ $reason->creator->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($reason->editor)
                                                <span class="text-info">{{ $reason->editor->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $reason->created_at->format('M d, Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('outages.final-reasons.edit', $reason->id) }}"
                                                   class="btn btn-outline-primary btn-xs"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="Edit Reason">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                {{-- <button type="button"
                                                        class="btn btn-outline-danger btn-xs"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $reason->id }}"
                                                        title="Delete Reason">
                                                    <i class="fas fa-trash"></i>
                                                </button> --}}
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal{{ $reason->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Confirm Delete</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete the final reason:</p>
                                                            <strong>"{{ $reason->final_reason_name }}"</strong>?
                                                            <p class="text-danger mt-2">This action cannot be undone.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Cancel</button>
                                                            <form action="{{ route('outages.final-reasons.destroy', $reason->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No final reasons found. <a href="{{ route('outages.final-reasons.create') }}">Create one now</a>.
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
</div>

@endsection

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    $('#final-reasons-table').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            search: "Search reasons:",
            lengthMenu: "Show _MENU_ reasons per page",
            info: "Showing _START_ to _END_ of _TOTAL_ reasons",
            infoEmpty: "No reasons available",
            emptyTable: "No final reasons found"
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection

