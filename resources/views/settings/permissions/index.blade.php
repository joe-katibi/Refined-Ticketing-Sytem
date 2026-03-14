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
@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Permissions</h5>

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
                <table class="table table-bordered" id="permissions-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Slug</th>
                            <th>Module</th>
                            <th>Sub-Module</th>
                            <th>Guard</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $key => $permission)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $permission->description }}</td>
                                <td><code>{{ $permission->name }}</code></td>
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
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle delete confirmation
    $('form[data-confirm]').on('submit', function(e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });
});
</script>
@endsection

