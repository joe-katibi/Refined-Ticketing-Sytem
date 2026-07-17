@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Affected Areas Management')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            Affected Areas Management
                        </h4>
                        <p class="text-muted mb-0">Manage areas that can be affected during outages</p>
                    </div>
                    @can('view-affected-areas-create')
                    <a href="{{ route('outages.affected-areas.create') }}" class="btn btn-primary btn-xs">
                        <i class="fas fa-plus me-1"></i>
                        Add New Area
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="affected-areas-table" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Area Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Edited By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($affected_areas as $area)
                                    <tr>
                                        <td>{{ $area->id }}</td>
                                        <td>
                                            <strong class="text-primary">{{ $area->area_name }}</strong>
                                        </td>
                                        <td>
                                            @if($area->area_description)
                                                <span class="text-muted">{{ Str::limit($area->area_description, 50) }}</span>
                                            @else
                                                <span class="text-muted fst-italic">No description</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($area->area_status == 'Active')
                                                <span class="badge badge-xs bg-success">Active</span>
                                            @else
                                                <span class="badge badge-xs bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($area->creator)
                                                <span class="text-info">{{ $area->creator->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($area->editor)
                                                <span class="text-info">{{ $area->editor->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $area->created_at->format('M d, Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view-affected-areas-edit')
                                                <a href="{{ route('outages.affected-areas.edit', $area->id) }}"
                                                   class="btn btn-outline-primary btn-xs"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="Edit Area">
                                                    <i class="fas fa-edit"></i>
                                                </a>
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
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    $('#affected-areas-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { targets: [7], orderable: false }
        ],
        language: {
            search: "Search areas:",
            lengthMenu: "Show _MENU_ areas per page",
            info: "Showing _START_ to _END_ of _TOTAL_ areas",
            infoEmpty: "No areas available",
            infoFiltered: "(filtered from _MAX_ total areas)"
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endsection

