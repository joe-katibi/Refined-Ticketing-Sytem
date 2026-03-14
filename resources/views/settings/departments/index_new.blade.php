@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Departments')

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
                    <h3 class="card-title">All Departments</h3>
                    <button type="button" class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#addDepartment">
                        <i class="fas fa-plus"></i> Add New Department
                    </button>
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

                    <div class="table-responsive">
                        <table class="table table-bordered" id="departments-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Sub-Departments</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departments as $department)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $department->department_name }}</td>
                                        <td>{{ $department->description ?: 'N/A' }}</td>
                                        <td>{!! $department->status_badge !!}</td>
                                        <td>
                                            <span class="badge badge-xs bg-primary">{{ $department->sub_departments_count ?? 0 }}</span>
                                        </td>
                                        <td>{{ $department->creator->name ?? 'System' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ url('settings/departments/'. $department->id .'/sub-department') }}"
                                                   class="btn btn-icon btn-info"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="View Sub-Departments">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('view-user-edit-department')
                                                    <button type="button"
                                                            class="btn btn-icon btn-primary edit-department-btn"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Edit"
                                                            data-department-id="{{ $department->id }}"
                                                            data-department-name="{{ $department->department_name }}"
                                                            data-department-description="{{ $department->description }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if($department->department_status == 1)
                                                        <a href="{{ url('settings/departments/'. $department->id .'/deactivate') }}"
                                                           class="btn btn-icon btn-danger"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="Deactivate"
                                                           onclick="return confirm('Are you sure you want to deactivate this department?')">
                                                            <i class="fas fa-ban"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ url('settings/departments/'. $department->id .'/activate') }}"
                                                           class="btn btn-icon btn-success"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="Activate">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No departments found.</td>
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

@include('settings.departments.modal_add_department')
@include('settings.departments.modal_edit_department')

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
    // Initialize DataTables
    $('#departments-table').DataTable({
        responsive: true,
        dom: 'lfrtip',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle edit department button clicks
    $(document).on('click', '.edit-department-btn', function() {
        console.log('Edit department button clicked'); // Debug log

        const departmentId = $(this).data('department-id');
        const departmentName = $(this).data('department-name');
        const departmentDescription = $(this).data('department-description');

        console.log('Department data:', { departmentId, departmentName, departmentDescription }); // Debug log

        editDepartment(departmentId, departmentName, departmentDescription);
    });
});

// Function to populate edit modal
function editDepartment(id, name, description) {
    console.log('editDepartment function called with:', { id, name, description }); // Debug log

    // Check if modal exists
    if ($('#editDepartment').length === 0) {
        console.error('Edit department modal not found!');
        return;
    }

    // Populate the modal form with department data
    $('#department_id').val(id);
    $('#modalEditDepartmentName').val(name || '');
    $('#modalEditDepartmentDescription').val(description || '');

    console.log('Modal fields populated:', {
        department_id: $('#department_id').val(),
        modalEditDepartmentName: $('#modalEditDepartmentName').val(),
        modalEditDepartmentDescription: $('#modalEditDepartmentDescription').val()
    }); // Debug log

    // Update the form action URL
    const updateUrl = '{{ url("settings/departments") }}/' + id;
    $('#editDepartmentForm').attr('action', updateUrl);

    console.log('Form action set to:', updateUrl); // Debug log

    // Show the modal
    $('#editDepartment').modal('show');
    console.log('Modal should be visible now'); // Debug log
}
</script>
@endsection

