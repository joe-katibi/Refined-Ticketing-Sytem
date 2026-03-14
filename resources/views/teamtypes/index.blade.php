@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Team Types')

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Team Types</h3>
                    <a href="{{ route('teamtypes.create') }}" class="btn btn-primary btn-xs">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered" id="team-types-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type Name</th>
                                    <th>Department</th>
                                    <th>Sub Department</th>
                                    <th>Status</th>
                                    <th>Sub-Types</th>
                                    <th>Created By</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teamTypes as $index => $teamType)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('teamtypes.show', $teamType) }}" class="text-primary">
                                                {{ $teamType->type_name }}
                                            </a>
                                        </td>
                                        <td>{{ $teamType->department->department_name ?? 'N/A' }}</td>
                                        <td>{{ $teamType->subDepartment->sub_department_name ?? 'N/A' }}</td>
                                        <td>{!! $teamType->status_badge !!}</td>
                                        <td class="text-center">
                                            <span class="badge badge-xs bg-label-primary">{{ $teamType->subTypes->count() }}</span>
                                        </td>
                                        <td>{{ $teamType->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $teamType->updated_at->diffForHumans() }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('teamtypes.show', $teamType) }}" class="btn btn-icon btn-info btn-sm" data-bs-toggle="tooltip" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('teamtypes.edit', $teamType) }}" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-icon btn-sm toggle-status-btn {{ $teamType->status === 'Active' ? 'btn-success' : 'btn-secondary' }}"
                                                    data-id="{{ $teamType->id }}"
                                                    data-current-status="{{ $teamType->status }}"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $teamType->status === 'Active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas {{ $teamType->status === 'Active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                </button>
                                            </div>
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
  $('#team-types-table').DataTable({
    responsive: true,
    dom: 'lfrtip',
  });
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle toggle status button clicks
    $('.toggle-status-btn').on('click', function() {
        const button = $(this);
        const teamTypeId = button.data('id');
        const currentStatus = button.data('current-status');
        const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';

        // Disable button during request
        button.prop('disabled', true);

        $.ajax({
            url: `/teamtypes/${teamTypeId}/toggle-status`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update button appearance
                    button.removeClass('btn-success btn-secondary');
                    button.addClass(response.new_status === 'Active' ? 'btn-success' : 'btn-secondary');

                    // Update icon
                    const icon = button.find('i');
                    icon.removeClass('fa-toggle-on fa-toggle-off');
                    icon.addClass(response.new_status === 'Active' ? 'fa-toggle-on' : 'fa-toggle-off');

                    // Update data attributes
                    button.data('current-status', response.new_status);

                    // Update tooltip
                    const tooltip = bootstrap.Tooltip.getInstance(button[0]);
                    if (tooltip) {
                        tooltip.dispose();
                    }
                    button.attr('title', response.new_status === 'Active' ? 'Deactivate' : 'Activate');
                    new bootstrap.Tooltip(button[0]);

                    // Update status badge in the table
                    const row = button.closest('tr');
                    const statusCell = row.find('td').eq(5); // Status column (6th column, 0-indexed)
                    statusCell.html(response.status_badge);

                    // Show success message
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Failed to update status');
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred while updating status';
                toastr.error(errorMessage);
            },
            complete: function() {
                // Re-enable button
                button.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
