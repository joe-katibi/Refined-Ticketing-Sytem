@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', $customer->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $customer->name }}</h1>
                    <p class="text-muted mb-0">Account {{ $customer->account_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary btn-xs">
                        <i class="bx bx-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to Customers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-user me-1"></i>Customer Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Status</label>
                            <div><span class="badge {{ $customer->status_badge_class }} fs-6">{{ $customer->status }}</span></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted">Mobile Number</label>
                            <div>{{ $customer->mobile_number ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted">Alternative Number</label>
                            <div>{{ $customer->alternative_number ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">Address</label>
                            <div>{{ $customer->address ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-wifi me-1"></i>Service &amp; ONU</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted">ONU Type</label>
                            <div>{{ $customer->onu_type ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted">Bandwidth / Plan</label>
                            <div>{{ $customer->bandwidth_profile ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">ONU Physical Address</label>
                            <div>{{ $customer->onu_physical_address ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-sitemap me-1"></i>Network Path</h5>
                </div>
                <div class="card-body">
                    @if($customer->fat)
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-muted">OLT</label>
                                <div class="fw-bold">{{ $customer->olt->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Slot / Port</label>
                                <div class="fw-bold">{{ $customer->pon_port->short_identifier ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">FDT</label>
                                <div class="fw-bold">FDT-{{ $customer->fdt->fdt_number ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">FAT</label>
                                <div class="fw-bold">
                                    <a href="{{ route('fats.show', $customer->fat) }}">FAT-{{ $customer->fat->fat_number }}</a>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">This customer is not yet connected to a FAT.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
