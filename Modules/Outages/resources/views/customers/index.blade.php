@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Customers')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Customers</h1>
                    <p class="text-muted mb-0">Subscribers connected to the fiber network</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('olt-upload.create') }}" class="btn btn-outline-primary btn-xs">
                        <i class="bx bx-upload me-1"></i>Bulk Upload
                    </a>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customers.index') }}" class="row g-3">
                <div class="col-md-8">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by account number, name, mobile, ONU type, OLT...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bx bx-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer List -->
    <div class="card">
        <div class="card-body">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Account #</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Alternative #</th>
                                <th>Address</th>
                                <th>ONU Type</th>
                                <th>ONU Physical Address</th>
                                <th>Plan / Bandwidth</th>
                                <th>OLT / FAT</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td class="fw-bold">{{ $customer->account_number }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->mobile_number ?? 'N/A' }}</td>
                                    <td>{{ $customer->alternative_number ?? 'N/A' }}</td>
                                    <td>{{ $customer->address ?? 'N/A' }}</td>
                                    <td>{{ $customer->onu_type ?? 'N/A' }}</td>
                                    <td>{{ $customer->onu_physical_address ?? 'N/A' }}</td>
                                    <td>{{ $customer->bandwidth_profile ?? 'N/A' }}</td>
                                    <td>
                                        @if($customer->fat)
                                            <small>{{ $customer->fat->fdt->ponPort->oltSlot->olt->name ?? 'N/A' }}</small><br>
                                            <small class="text-muted">FAT-{{ $customer->fat->fat_number }}</small>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $customer->status_badge_class }}">{{ $customer->status }}</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('customers.show', $customer) }}"><i class="bx bx-show me-1"></i>View</a></li>
                                                <li><a class="dropdown-item" href="{{ route('customers.edit', $customer) }}"><i class="bx bx-edit me-1"></i>Edit</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} results</div>
                    {{ $customers->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-user display-1 text-muted"></i>
                    <h4 class="mt-3">No Customers Found</h4>
                    <p class="text-muted mb-4">Add a customer by hand, or bulk-upload from a spreadsheet.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-xs">
                            <i class="bx bx-plus me-1"></i>Add Customer
                        </a>
                        <a href="{{ route('olt-upload.create') }}" class="btn btn-outline-primary btn-xs">
                            <i class="bx bx-upload me-1"></i>Bulk Upload
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
