@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Sub Departments')

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .action-buttons .btn {
        margin-right: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Sub Departments for {{ $tittle->department_name }}</h3>
                    <div>
                        <button type="button"
                                class="btn btn-primary btn-xs"
                                data-bs-toggle="modal"
                                data-bs-target="#addSubDepartment"
                                data-id="{{ request()->route('id') }}"
                                data-department_name="{{ $tittle->department_name }}">
                            <i class="fas fa-plus"></i> Add Sub Department
                        </button>
                        <a href="{{ url('/settings/departments') }}"
                           class="btn btn-secondary btn-xs">
                            <i class="fas fa-arrow-left"></i> Back to Departments
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (Session::has('message'))
                        <div class="alert alert-{{ Session::has('message_type') ? Session::get('message_type') : 'success' }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

@if($sub->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" id="sub-departments-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sub as $subDepartment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $subDepartment->sub_department_name }}</td>
                                            <td>
                                                @if ($subDepartment->sub_department_status == '1')
                                                    <span class="badge badge-xs bg-label-success">Active</span>
                                                @else
                                                    <span class="badge badge-xs bg-label-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $subDepartment->createdBy->name ?? 'System' }}</td>
                                            <td>{{ $subDepartment->created_at->format('M d, Y h:i A') }}</td>
                                            <td>
                                                <div class="action-buttons" role="group">
                                                    <button type="button"
                                                            class="btn btn-icon btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editSubDepartment"
                                                            data-id="{{ $subDepartment->id }}"
                                                            data-sub_department_name="{{ $subDepartment->sub_department_name }}"
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    @if ($subDepartment->sub_department_status == 1)
                                                        <button type="button"
                                                                class="btn btn-icon btn-danger"
                                                                onclick="confirmDeactivate({{ $subDepartment->id }})"
                                                                data-bs-toggle="tooltip"
                                                                title="Deactivate">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-icon btn-success"
                                                                onclick="confirmActivate({{ $subDepartment->id }})"
                                                                data-bs-toggle="tooltip"
                                                                title="Activate">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No sub departments found for this department.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('settings.departments.modal_add_sub_department')
@include('settings.departments.modal_edit_sub_department')
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
    console.log('Document ready, checking for table...');
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTable function available:', typeof $.fn.DataTable);

    // Add a small delay to ensure all scripts are loaded
    setTimeout(function() {
        // Check if table exists
        if ($('#sub-departments-table').length > 0) {
            console.log('Table found, initializing DataTables...');

            try {
                var table = $('#sub-departments-table').DataTable({
                    dom: 'lfrtip',
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    order: [[0, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: [5] }
                    ]
                });
                console.log('DataTables initialized successfully!', table);
            } catch (error) {
                console.error('DataTables initialization failed:', error);
            }
        } else {
            console.log('Table not found!');
        }
    }, 100);

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle Add Sub Department modal
    $('#addSubDepartment').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var departmentId = button.data('id');
        var departmentName = button.data('department_name');

        var modal = $(this);
        modal.find('.modal-title').text('Add Sub Department for ' + departmentName);
        modal.find('form').attr('action', '/settings/departments/' + departmentId + '/sub-department/store');
    });

    // Handle Edit Sub Department modal
    $('#editSubDepartment').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var subDepartmentId = button.data('id');
        var subDepartmentName = button.data('sub_department_name');

        console.log('Edit modal data:', {
            id: subDepartmentId,
            name: subDepartmentName
        });

        var modal = $(this);
        modal.find('h3').text('Edit Sub Department');
        modal.find('form').attr('action', '/settings/departments/sub-department/' + subDepartmentId + '/update');
        modal.find('#edit_sub_department_name').val(subDepartmentName);
    });
});

// Confirmation functions
function confirmActivate(id) {
    if (confirm('Are you sure you want to activate this sub department?')) {
        window.location.href = '/settings/departments/' + id + '/sub-department/activate';
    }
}

function confirmDeactivate(id) {
    if (confirm('Are you sure you want to deactivate this sub department?')) {
        window.location.href = '/settings/departments/' + id + '/sub-department/deactivate';
    }
}
</script>
@endsection

