@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'FDT Management')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">FDT Management</h1>
                    <p class="text-muted mb-0">Fiber Distribution Terminals across all OLTs</p>
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
            <form method="GET" action="{{ route('fdts.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Search by FDT number, type, location, OLT...">
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
                    <label for="fdt_type" class="form-label">Type</label>
                    <select class="form-select" id="fdt_type" name="fdt_type">
                        <option value="">All Types</option>
                        @foreach($fdtTypes as $type)
                            <option value="{{ $type }}" {{ request('fdt_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bx bx-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('fdts.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- FDT List -->
    <div class="card">
        <div class="card-body">
            @if($fdts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>FDT</th>
                                <th>OLT</th>
                                <th>Slot / Port</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>FATs</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fdts as $fdt)
                                <tr>
                                    <td class="fw-bold">FDT-{{ $fdt->fdt_number }}</td>
                                    <td>{{ $fdt->ponPort->oltSlot->olt->name ?? 'N/A' }}</td>
                                    <td>{{ $fdt->ponPort->short_identifier ?? 'N/A' }}</td>
                                    <td>
                                        @if($fdt->fdt_type)
                                            <span class="badge {{ $fdt->fdt_type_badge_class }}">{{ $fdt->fdt_type }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $fdt->location ?? 'N/A' }}</td>
                                    <td>{{ $fdt->fats->count() }}</td>
                                    <td><span class="badge {{ $fdt->status_badge_class }}">{{ ucfirst($fdt->status) }}</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('fdts.show', $fdt) }}"><i class="bx bx-show me-1"></i>View Details</a></li>
                                                <li><a class="dropdown-item" href="{{ route('fdts.edit', $fdt) }}"><i class="bx bx-edit me-1"></i>Edit</a></li>
                                                <li><a class="dropdown-item" href="{{ route('fdts.fats.create', $fdt) }}"><i class="bx bx-plus me-1"></i>Add FAT</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">Showing {{ $fdts->firstItem() }} to {{ $fdts->lastItem() }} of {{ $fdts->total() }} results</div>
                    {{ $fdts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-git-branch display-1 text-muted"></i>
                    <h4 class="mt-3">No FDTs Found</h4>
                    <p class="text-muted mb-4">Add FDTs from a PON port's page under an OLT, or via the bulk upload.</p>
                    <a href="{{ route('olts.index') }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-server me-1"></i>Go to OLTs
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
