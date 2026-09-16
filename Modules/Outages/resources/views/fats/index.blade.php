@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'FAT Management')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">FAT Management</h1>
                    <p class="text-muted mb-0">Fiber Access Terminals across all OLTs</p>
                </div>
                <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary btn-xs">
                    <i class="bx bx-arrow-back me-1"></i>Back to OLTs
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('fats.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Search by FAT number, type, location, OLT...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="fat_type" class="form-label">Type</label>
                    <select class="form-select" id="fat_type" name="fat_type">
                        <option value="">All Types</option>
                        @foreach($fatTypes as $type)
                            <option value="{{ $type }}" {{ request('fat_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bx bx-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('fats.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- FAT List -->
    <div class="card">
        <div class="card-body">
            @if($fats->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>FAT</th>
                                <th>OLT</th>
                                <th>FDT</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Customers</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fats as $fat)
                                <tr>
                                    <td class="fw-bold">FAT-{{ $fat->fat_number }}</td>
                                    <td>{{ $fat->fdt->ponPort->oltSlot->olt->name ?? 'N/A' }}</td>
                                    <td>FDT-{{ $fat->fdt->fdt_number ?? 'N/A' }}</td>
                                    <td>
                                        @if($fat->fat_type)
                                            <span class="badge {{ $fat->fat_type_badge_class }}">{{ $fat->fat_type }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $fat->location ?? 'N/A' }}</td>
                                    <td>{{ $fat->customers->count() }}</td>
                                    <td><span class="badge {{ $fat->status_badge_class }}">{{ ucfirst($fat->status) }}</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('fats.show', $fat) }}"><i class="bx bx-show me-1"></i>View Details</a></li>
                                                <li><a class="dropdown-item" href="{{ route('fats.edit', $fat) }}"><i class="bx bx-edit me-1"></i>Edit</a></li>
                                                <li><a class="dropdown-item" href="{{ route('fats.customers.create', $fat) }}"><i class="bx bx-plus me-1"></i>Add Customer</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">Showing {{ $fats->firstItem() }} to {{ $fats->lastItem() }} of {{ $fats->total() }} results</div>
                    {{ $fats->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-grid-alt display-1 text-muted"></i>
                    <h4 class="mt-3">No FATs Found</h4>
                    <p class="text-muted mb-4">Add FATs from an FDT's page, or via the bulk upload.</p>
                    <a href="{{ route('fdts.index') }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-git-branch me-1"></i>Go to FDTs
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
