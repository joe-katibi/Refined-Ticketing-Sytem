@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Roles')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/typeahead-js/typeahead.css')}}" />
    <!-- Row Group CSS -->
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}">
    <!-- Form Validation -->
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />

@endsection

@section('vendor-script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Role: {{ $role->name }} - Permissions</h5>
            <div>
                <a href="{{ url('settings/roles') }}" class="btn btn-secondary btn-xs">
                    <i class="fas fa-arrow-left me-1"></i> Back to Roles
                </a>
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

            <!-- Role Information Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                    <strong>Role:</strong> {{ $role->name }}
                                </div>
                                <div class="col-sm-3">
                                    <strong>Guard:</strong> <span class="badge badge-xs bg-label-success">{{ $role->guard_name }}</span>
                                </div>
                                <div class="col-sm-3">
                                    <strong>Description:</strong> {{ $role->description ?? 'N/A' }}
                                </div>
                                <div class="col-sm-3">
                                    <strong>Created:</strong> {{ $role->created_at ? $role->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="permissions-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Permission Name</th>
                            <th>Description</th>
                            <th>Module</th>
                            <th>Sub-Module</th>
                            <th>Guard</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $key => $permission)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td><code>{{ $permission->name }}</code></td>
                                <td>{{ $permission->description ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-xs bg-label-primary">{{ $permission->module ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-xs bg-label-info">{{ $permission->sub_module ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-xs bg-label-success">{{ $permission->guard_name }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    $('#permissions-table').DataTable({
        responsive: true,
        dom: 'lfrtip',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [1] } // Disable sorting on Permission Name column if needed
        ]
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection

