@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'OLT Management')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">OLT Management</h1>
                    <p class="text-muted mb-0">Manage OLT devices, slots, and PON ports</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-user me-1"></i>Customers
                    </a>
                    @can('view-olt-management-create')
                    <a href="{{ route('olt-upload.create') }}" class="btn btn-outline-primary btn-xs">
                        <i class="bx bx-upload me-1"></i>Bulk Upload
                    </a>
                    <a href="{{ route('olts.create') }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add New OLT
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('olts.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Search by name, vendor, model, IP...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="vendor" class="form-label">Vendor</label>
                    <select class="form-select" id="vendor" name="vendor">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor }}" {{ request('vendor') == $vendor ? 'selected' : '' }}>
                                {{ $vendor }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bx bx-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- OLT List -->
    <div class="card">
        <div class="card-body">
            @if($olts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Vendor/Model</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Slots</th>
                                <th>PON Ports</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($olts as $olt)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $olt->name }}</div>
                                        <small class="text-muted">
                                            Created {{ $olt->created_at->format('M d, Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($olt->vendor)
                                            <span class="badge {{ $olt->vendor_badge_class }} mb-1">{{ $olt->vendor }}</span><br>
                                        @endif
                                        <small class="text-muted">{{ $olt->model ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $olt->ip_address }}</code>
                                    </td>
                                    <td>{{ $olt->location ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-xs bg-info me-1">{{ $olt->active_slots }}</span>
                                            <small class="text-muted">/ {{ $olt->total_slots ?? 'N/A' }}</small>
                                        </div>
                                        <small class="text-muted">Active / Total</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-xs bg-success me-1">{{ $olt->active_pon_ports }}</span>
                                            <small class="text-muted">/ {{ $olt->total_pon_ports }}</small>
                                        </div>
                                        <small class="text-muted">Active / Total</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $olt->status_badge_class }}">{{ $olt->status }}</span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                @can('view-olt-management-menu')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('olts.show', $olt) }}">
                                                        <i class="bx bx-show me-1"></i>View Details
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('view-olt-management-edit')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('olts.edit', $olt) }}">
                                                        <i class="bx bx-edit me-1"></i>Edit
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('view-olt-management-create')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('olts.slots.create', $olt) }}">
                                                        <i class="bx bx-plus me-1"></i>Add Slot
                                                    </a>
                                                </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $olts->firstItem() }} to {{ $olts->lastItem() }} of {{ $olts->total() }} results
                    </div>
                    {{ $olts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-server display-1 text-muted"></i>
                    <h4 class="mt-3">No OLTs Found</h4>
                    <p class="text-muted mb-4">Start by adding your first OLT device to the system.</p>
                    @can('view-olt-management-create')
                    <a href="{{ route('olts.create') }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add First OLT
                    </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

