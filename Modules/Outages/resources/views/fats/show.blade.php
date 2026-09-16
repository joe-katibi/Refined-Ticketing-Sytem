@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'FAT-' . $fat->fat_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">FAT-{{ $fat->fat_number }}</h1>
                    <p class="text-muted mb-0">FDT-{{ $fat->fdt->fdt_number }} / {{ $fat->fdt->ponPort->identifier }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('fats.edit', $fat) }}" class="btn btn-outline-primary btn-xs">
                        <i class="bx bx-edit me-1"></i>Edit FAT
                    </a>
                    <a href="{{ route('fats.customers.create', $fat) }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add Customer
                    </a>
                    <a href="{{ route('fdts.show', $fat->fdt) }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to FDT
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <!-- FAT Information -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-grid-alt me-1"></i>FAT Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Status</label>
                            <div><span class="badge {{ $fat->status_badge_class }} fs-6">{{ ucfirst($fat->status) }}</span></div>
                        </div>
                        @if($fat->fat_type)
                        <div class="col-12">
                            <label class="form-label text-muted">Type</label>
                            <div><span class="badge {{ $fat->fat_type_badge_class }}">{{ $fat->fat_type }}</span></div>
                        </div>
                        @endif
                        @if($fat->location)
                        <div class="col-12">
                            <label class="form-label text-muted">Location</label>
                            <div>{{ $fat->location }}</div>
                        </div>
                        @endif
                        @if($fat->capacity)
                        <div class="col-12">
                            <label class="form-label text-muted">Capacity</label>
                            <div class="fw-bold">{{ $fat->capacity }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-sitemap me-1"></i>Network Path</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">OLT</label>
                            <div class="fw-bold">{{ $fat->fdt->ponPort->oltSlot->olt->name }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">Slot / Port</label>
                            <div class="fw-bold">{{ $fat->fdt->ponPort->short_identifier }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">FDT</label>
                            <div class="fw-bold">FDT-{{ $fat->fdt->fdt_number }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bx bx-user me-1"></i>Customers</h5>
                    <a href="{{ route('fats.customers.create', $fat) }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add Customer
                    </a>
                </div>
                <div class="card-body">
                    @if($fat->customers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Account #</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>ONU Type</th>
                                        <th>Plan</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fat->customers as $customer)
                                        <tr>
                                            <td>{{ $customer->account_number }}</td>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->mobile_number ?? 'N/A' }}</td>
                                            <td>{{ $customer->onu_type ?? 'N/A' }}</td>
                                            <td>{{ $customer->bandwidth_profile ?? 'N/A' }}</td>
                                            <td><span class="badge {{ $customer->status_badge_class }}">{{ $customer->status }}</span></td>
                                            <td>
                                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary btn-xs">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bx bx-user text-muted display-6"></i>
                            <p class="text-muted mb-2">No customers attached to this FAT</p>
                            <a href="{{ route('fats.customers.create', $fat) }}" class="btn btn-primary btn-xs">
                                <i class="bx bx-plus me-1"></i>Add First Customer
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
