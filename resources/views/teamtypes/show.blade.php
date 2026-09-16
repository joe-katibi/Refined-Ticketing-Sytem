@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'View Team Type')

@push('styles')
<style>
    .info-box {
        border-left: 4px solid #4e73df;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
    }
    .info-value {
        color: #212529;
    }
    .dark-style .info-box {
        background-color: #2b2c40;
    }
    .dark-style .info-label {
        color: #a3a4cc;
    }
    .dark-style .info-value {
        color: #cbcbe2;
    }
</style>
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Team Type Details</h3>
                    <div>
                        <a href="{{ route('teamtypes.edit', $teamtype->id) }}"
                           class="btn btn-primary btn-xs">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('teamtypes.index') }}"
                           class="btn btn-secondary btn-xs">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="mb-2">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value">{{ $teamtype->type_name }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Status:</span>
                                    <span class="info-value">{!! $teamtype->status_badge !!}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Description:</span>
                                    <span class="info-value">{{ $teamtype->description ?: 'N/A' }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Department:</span>
                                    <span class="info-value">{{ $teamtype->department->department_name ?? 'N/A' }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Sub Department:</span>
                                    <span class="info-value">{{ $teamtype->subDepartment->sub_department_name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="mb-2">
                                    <span class="info-label">Created By:</span>
                                    <span class="info-value">{{ $teamtype->creator->name ?? 'System' }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Last Updated:</span>
                                    <span class="info-value">{{ $teamtype->updated_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Created At:</span>
                                    <span class="info-value">{{ $teamtype->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Sub-Team Types</h3>
                            <a href="{{ route('teamtypes.sub-types.create', $teamtype->id) }}"
                               class="btn btn-primary btn-xs">
                                <i class="fas fa-plus"></i> Add Sub-Type
                            </a>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if($teamtype->subTypes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="sub-types-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Department</th>
                                                <th>Sub Department</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($teamtype->subTypes as $index => $subType)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $subType->sub_type_name }}</td>
                                                    <td>{{ $subType->sub_type_description ?: 'N/A' }}</td>
                                                    <td>{{ $subType->department->department_name ?? 'N/A' }}</td>
                                                    <td>{{ $subType->subDepartment->sub_department_name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($subType->sub_type_status == 'Active')
                                                            <span class="badge badge-xs bg-label-success">Active</span>
                                                        @else
                                                            <span class="badge badge-xs bg-label-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ route('teamtypes.sub-types.edit', [$teamtype->id, $subType->id]) }}"
                                                               class="btn btn-icon btn-primary btn-sm"
                                                               data-bs-toggle="tooltip" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" 
                                                                class="btn btn-icon btn-sm toggle-subtype-status-btn {{ $subType->sub_type_status === 'Active' ? 'btn-success' : 'btn-secondary' }}" 
                                                                data-teamtype-id="{{ $teamtype->id }}"
                                                                data-subtype-id="{{ $subType->id }}" 
                                                                data-current-status="{{ $subType->sub_type_status }}"
                                                                data-bs-toggle="tooltip" 
                                                                title="{{ $subType->sub_type_status === 'Active' ? 'Deactivate' : 'Activate' }}">
                                                                <i class="fas {{ $subType->sub_type_status === 'Active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No sub-team types found. Click the "Add Sub-Type" button to create one.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable for sub-types
        if ($('#sub-types-table').length) {
            $('#sub-types-table').DataTable({
                "responsive": true,
                dom: 'lfrtip',
                "autoWidth": false,
                "order": [[0, 'asc']],
                "columnDefs": [
                    { "orderable": false, "targets": [4] } // Disable sorting on Actions column
                ]
            });
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Handle toggle SubTeamType status button clicks
        $('.toggle-subtype-status-btn').on('click', function() {
            const button = $(this);
            const teamTypeId = button.data('teamtype-id');
            const subTypeId = button.data('subtype-id');
            const currentStatus = button.data('current-status');
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            
            // Disable button during request
            button.prop('disabled', true);
            
            $.ajax({
                url: `/teamtypes/${teamTypeId}/sub-types/${subTypeId}/toggle-status`,
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
@endpush

