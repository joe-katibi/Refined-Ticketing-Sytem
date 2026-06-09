@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Users')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection


@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Users</h3>
                    @can('view-create-user')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUser">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                    @endcan
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
                        <table class="table table-bordered" id="users-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $key => $user)
                                <tr>
                                    <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->department)
                                            <span class="badge badge-xs bg-info">{{ $user->department->department_name }}</span>
                                        @else
                                            <span class="badge badge-xs bg-secondary">No Department</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->user_status == 1)
                                            <span class="badge badge-xs bg-success">Active</span>
                                        @else
                                            <span class="badge badge-xs bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</td>
                                    <td class="action-buttons">
                                        @can('view-view-user')
                                        <button type="button" class="btn btn-outline-info view-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewUser"
                                                data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-username="{{ $user->username ?? '' }}"
                                                data-email="{{ $user->email }}"
                                                data-phone="{{ $user->phone ?? '' }}"
                                                data-department_id="{{ $user->department_id }}"
                                                data-sub_department_id="{{ $user->sub_department_id ?? '' }}"
                                                data-user_status="{{ $user->user_status }}"
                                                data-role_id="{{ $user->roles->first()->id ?? '' }}"
                                                data-team_type_id="{{ $user->team_type_id ?? '' }}"
                                                data-sub_team_type_id="{{ $user->sub_team_type_id ?? '' }}"
                                                data-supervisor-id="{{ $user->supervisor_id ?? '' }}"
                                                title="View User Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @endcan
                                        @can('view-edit-user')
                                        <button type="button" class="btn btn-outline-primary  edit-user-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUser"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-username="{{ $user->username ?? '' }}"
                                                data-user-email="{{ $user->email }}"
                                                data-phone="{{ $user->phone ?? '' }}"
                                                data-department_id="{{ $user->department_id }}"
                                                data-sub_department_id="{{ $user->sub_department_id ?? '' }}"
                                                data-user_status="{{ $user->user_status }}"
                                                data-role_id="{{ $user->roles->first()->id ?? '' }}"
                                                data-team_type_id="{{ $user->team_type_id ?? '' }}"
                                                data-sub_team_type_id="{{ $user->sub_team_type_id ?? '' }}"
                                                title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endcan
                                        @can('view-edit-user-status')
                                        @if($user->user_status == 1)
                                            <button type="button" class="btn btn-outline-warning toggle-user-status-btn"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-current-status="{{ $user->user_status }}"
                                                    title="Deactivate User">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-success toggle-user-status-btn"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-current-status="{{ $user->user_status }}"
                                                    title="Activate User">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="pagination-container mt-4">
                            <div class="pagination-info">
                                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries
                            </div>
                            <div class="d-flex justify-content-center">
                                {{ $users->appends(['per_page' => request('per_page')])->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('settings.users.modal_add_user', ['teamTypes' => $teamTypes, 'departments' => $department, 'roles' => $roles])
@include('settings.users.modal_edit_user', ['department' => $department, 'roles' => $roles, 'teamTypes' => $teamTypes])
@include('settings.users.modal_view_user', ['department' => $department, 'roles' => $roles, 'teamTypes' => $teamTypes])
@endsection
@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTables with consistent features across all tables
    $('#users-table').DataTable({
        responsive: true,
        paging: false,      // Disable DataTables pagination as we're using Laravel's
        info: false,       // Hide "Showing X of Y entries" as we use Laravel's
        searching: true,   // Keep the search functionality
        ordering: true,    // Keep the sorting functionality
        dom: 'lrtip',      // Remove DataTables pagination ('p')
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] } // Actions column
        ],
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            emptyTable: 'No users found',
            zeroRecords: 'No matching users found'
        },
        initComplete: function() {
            // Add Bootstrap classes to the search input
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_filter label').addClass('mb-0');

            // Move search to a better position
            $('.dataTables_filter').addClass('mb-3');
        }
    });



    // Form validation for add user form
    $('#addUserForm').on('submit', function(e) {
        let isValid = true;
        const requiredFields = [
            { id: 'modalAddName', name: 'Name' },
            { id: 'modalAddUserName', name: 'Username' },
            { id: 'modalAddUserEmail', name: 'Email' },
            { id: 'modalAddUserPassword', name: 'Password' },
            { id: 'modalAddUserDepartment', name: 'Department' },
            { id: 'modalAddUserStatus', name: 'Status' },
            { id: 'modalAddUserRoles', name: 'Role' }
        ];

        // Clear previous error messages
        $('.invalid-feedback').remove();
        $('.is-invalid').removeClass('is-invalid');

        // Check each required field
        requiredFields.forEach(field => {
            const input = $('#' + field.id);
            if (!input.val()) {
                isValid = false;
                input.addClass('is-invalid');
                input.after(`<div class="invalid-feedback">${field.name} is required</div>`);
            }
        });

        // Email validation
        const emailInput = $('#modalAddUserEmail');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailInput.val() && !emailRegex.test(emailInput.val())) {
            isValid = false;
            emailInput.addClass('is-invalid');
            emailInput.after('<div class="invalid-feedback">Please enter a valid email address</div>');
        }

        // Password validation (min 8 characters)
        const passwordInput = $('#modalAddUserPassword');
        if (passwordInput.val() && passwordInput.val().length < 8) {
            isValid = false;
            passwordInput.addClass('is-invalid');
            passwordInput.after('<div class="invalid-feedback">Password must be at least 8 characters long</div>');
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Show the modal if there are validation errors
    $(document).ready(function() {
        if ($('.alert-danger').length > 0) {
            $('#addUser').modal('show');
        }
    });

    // Edit user button click handler
    $('.edit-user-btn').on('click', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        var userEmail = $(this).data('user-email');
        var departmentId = $(this).data('department-id');
        var userStatus = $(this).data('user-status');
        var roleId = $(this).data('role-id');

        $('#editUser').find('#editUserId').val(userId);
        $('#editUser').find('#editUserName').val(userName);
        $('#editUser').find('#editUserEmail').val(userEmail);
        $('#editUser').find('#editUserDepartment').val(departmentId).trigger('change');
        $('#editUser').find('#editUserStatus').val(userStatus);
        $('#editUser').find('#editUserRole').val(roleId).trigger('change');
        $('#editUser').find('form').attr('action', '/settings/users/' + userId + '/update');
    });

    // Toggle user status button click handler
    $('.toggle-user-status-btn').on('click', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        var currentStatus = $(this).data('current-status');
        var action = currentStatus == 1 ? 'deactivate' : 'activate';
        var actionText = currentStatus == 1 ? 'deactivate' : 'activate';

        if (confirm('Are you sure you want to ' + actionText + ' user "' + userName + '"?')) {
            $.ajax({
                url: '/settings/users/' + userId + '/toggle-status',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: currentStatus == 1 ? 0 : 1
                },
                success: function(response) {
                    if (response.success) {
                        // Update the button and status without full page reload
                        var button = $('.toggle-user-status-btn[data-user-id="' + userId + '"]');
                        var newStatus = currentStatus == 1 ? 0 : 1;
                        var newActionText = newStatus == 1 ? 'deactivate' : 'activate';

                        // Update button data attributes
                        button.data('current-status', newStatus);
                        button.attr('data-current-status', newStatus);

                        // Update button text and styling
                        if (newStatus == 1) {
                            button.removeClass('btn-success').addClass('btn-danger');
                            button.html('<i class="fas fa-ban"></i> Deactivate');
                        } else {
                            button.removeClass('btn-danger').addClass('btn-success');
                            button.html('<i class="fas fa-check"></i> Activate');
                        }

                        // Show success message
                        alert('User ' + actionText + 'd successfully');
                    } else {
                        alert('Error ' + actionText + 'ing user: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error ' + actionText + 'ing user. Please try again.');
                }
            });
        }
    });

    // Initialize Select2 when modals are shown
    $('#addUser, #editUser').on('shown.bs.modal', function () {
        // Initialize regular select2 elements
        $(this).find('.select2').not('#modalAddUserRoles').select2({
            placeholder: "Select option",
            allowClear: true,
            dropdownParent: $(this)
        });

        // Initialize roles multi-select with special configuration
        $(this).find('#modalAddUserRoles').select2({
            placeholder: "Select roles",
            allowClear: true,
            multiple: true,
            dropdownParent: $(this)
        });
    });

    // Destroy Select2 when modals are hidden
    $('#addUser, #editUser').on('hidden.bs.modal', function () {
        $(this).find('.select2').select2('destroy');
    });
});
</script>
@endpush
