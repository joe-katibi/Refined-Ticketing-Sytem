@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Roles')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection
@section('content_header')

<!-- /.container-fluid -->
@stop
@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Roles</h5>
            <div>
                <button type="button" class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#addRole">
                    <i class="fas fa-plus me-1"></i> Add New Role
                </button>
            </div>
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

            @if (Session::has('message'))
                <div class="alert alert-{{ Session::has('message_type') ? Session::get('message_type') : 'success' }} alert-dismissible fade show" role="alert">
                    {{ Session::get('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="roles-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Guard</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $key => $role)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                </td>
                                <td>{{ $role->description ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-xs bg-label-success">{{ $role->guard_name }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-xs bg-label-primary">{{ $role->permissions->count() }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @can('view-user-edit-role')
                                            <a href="{{ url('settings/roles/' . $role->id . '/view') }}" class="btn btn-icon btn-info" data-bs-toggle="tooltip" title="View Permissions">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ url('settings/roles/' . $role->id . '/permissions') }}" class="btn btn-icon btn-warning" data-bs-toggle="tooltip" title="Manage Permissions">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                            <button type="button" class="btn btn-icon btn-primary edit-role-btn"
                                                   data-bs-toggle="tooltip" title="Edit Role"
                                                   data-role-id="{{ $role->id }}"
                                                   data-role-name="{{ $role->name }}"
                                                   data-role-description="{{ $role->description }}">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                        @endcan
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
 @include('settings.roles.modal_add_role')
@include('settings.roles.modal_add_edit')
{{-- @include('settings.roles.modal_assign_permission') --}}
@endsection


@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .btn-group .btn {
        margin-right: 0.25rem;
    }
</style>
@endpush

@section('page-script')
<script>
$(document).ready(function() {
    $('#roles-table').DataTable({
        dom: 'lfrtip',
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [5] } // Disable sorting on Actions column
        ]
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle edit role modal
    $('.edit-role-btn').on('click', function() {
        var roleId = $(this).data('role-id');
        var roleName = $(this).data('role-name');
        var roleDescription = $(this).data('role-description');
        
        // Populate the edit modal with correct IDs
        $('#editRole').find('#modalEditRoleName').val(roleName);
        $('#editRole').find('#modalEditRoleDescription').val(roleDescription);
        $('#editRole').find('form').attr('action', '/settings/roles/' + roleId + '/update');
        
        // Show the modal
        $('#editRole').modal('show');
    });
});

function editRole(roleId, roleName, roleDescription) {
    console.log('Edit role called:', roleId, roleName, roleDescription);
    
    // Set form action
    $('#editRole form').attr('action', '/settings/roles/' + roleId + '/update');
    
    // Populate form fields with correct IDs
    $('#editRole #modalEditRoleName').val(roleName);
    $('#editRole #modalEditRoleDescription').val(roleDescription);
    
    console.log('Form action set to:', $('#editRole form').attr('action'));
    console.log('Role name field value:', $('#editRole #modalEditRoleName').val());
    console.log('Role description field value:', $('#editRole #modalEditRoleDescription').val());
}
</script>
@endsection

