@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Categories')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
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
                    <h3 class="card-title">Categories</h3>
                    <a href="{{ route('escalation-category.create') }}" class="btn btn-primary btn-xs">
                        <i class="fas fa-plus"></i> Add New
                    </a>
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

                    <div class="table-responsive">
                        <table class="table table-bordered" id="categories-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Sub-Categories</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->category_name }}</td>
                                        <td>
                                            @if($category->status === 'Active')
                                                <span class="badge badge-xs bg-success">Active</span>
                                            @else
                                                <span class="badge badge-xs bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-xs bg-primary">{{ $category->subcategories->count() ?? 0 }}</span>
                                        </td>
                                        <td>{{ $category->createdBy->name ?? $category->created_by ?? 'System' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('escalation-category.show', $category) }}" class="btn btn-icon btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('escalation-category.edit', $category) }}" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('escalation-category.subcategories.index', $category) }}" class="btn btn-icon btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Subcategories">
                                                    <i class="fas fa-list"></i>
                                                </a>
                                                @if($category->status === 'Active')
                                                    <form action="{{ route('escalation-category.inactive', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to mark this category as inactive?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-icon btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark Inactive">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('escalation-category.active', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to mark this category as active?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-icon btn-success btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark Active">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No categories found.</td>
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

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
$('#categories-table').DataTable({
  responsive: true,
  dom: 'lfrtip',
});
});
</script>
@endsection

