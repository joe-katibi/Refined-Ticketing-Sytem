@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Affected Services Management')

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
                            <i class="fas fa-cogs text-primary me-2"></i>
                            Affected Services Management
                        </h4>
                        <p class="text-muted mb-0">Manage services that can be affected during outages</p>
                    </div>
                    <a href="{{ route('outages.affected-services.create') }}" class="btn btn-primary btn-xs">
                        <i class="fas fa-plus me-1"></i>
                        Add New Service
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="affected-services-table" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Service Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Edited By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($affected_services as $service)
                                    <tr>
                                        <td>{{ $service->id }}</td>
                                        <td>
                                            <strong class="text-primary">{{ $service->service_name }}</strong>
                                        </td>
                                        <td>
                                            @if($service->service_description)
                                                <span class="text-muted">{{ Str::limit($service->service_description, 50) }}</span>
                                            @else
                                                <span class="text-muted fst-italic">No description</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->service_status == 'Active')
                                                <span class="badge badge-xs bg-success">Active</span>
                                            @else
                                                <span class="badge badge-xs bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->creator)
                                                <span class="text-info">{{ $service->creator->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->editor)
                                                <span class="text-info">{{ $service->editor->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $service->created_at->format('M d, Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('outages.affected-services.edit', $service->id) }}"
                                                   class="btn btn-outline-primary btn-xs"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="Edit Service">
                                                    <i class="fas fa-edit"></i>
                                                </a>
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
    $('#affected-services-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { targets: [7], orderable: false }
        ],
        language: {
            search: "Search services:",
            lengthMenu: "Show _MENU_ services per page",
            info: "Showing _START_ to _END_ of _TOTAL_ services",
            infoEmpty: "No services available",
            infoFiltered: "(filtered from _MAX_ total services)"
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endsection

