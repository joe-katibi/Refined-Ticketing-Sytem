@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Add Customer')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Add Customer</h1>
                    <p class="text-muted mb-0">
                        @if($fat ?? null)
                            Attaching to {{ $fat->fdt->ponPort->oltSlot->olt->name }} / FAT-{{ $fat->fat_number }}
                        @else
                            Register a new subscriber
                        @endif
                    </p>
                </div>
                <a href="{{ ($fat ?? null) ? route('fats.show', $fat) : route('customers.index') }}" class="btn btn-outline-secondary btn-xs">
                    <i class="bx bx-arrow-back me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ ($fat ?? null) ? route('fats.customers.store', $fat) : route('customers.store') }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                       id="account_number" name="account_number" value="{{ old('account_number') }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                <input type="text" class="form-control @error('mobile_number') is-invalid @enderror"
                                       id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}" placeholder="e.g., 0700000000">
                                @error('mobile_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="alternative_number" class="form-label">Alternative Number</label>
                                <input type="text" class="form-control @error('alternative_number') is-invalid @enderror"
                                       id="alternative_number" name="alternative_number" value="{{ old('alternative_number') }}">
                                @error('alternative_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                       id="address" name="address" value="{{ old('address') }}" placeholder="Customer's contact address">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="onu_type" class="form-label">ONU Type</label>
                                <input type="text" class="form-control @error('onu_type') is-invalid @enderror"
                                       id="onu_type" name="onu_type" value="{{ old('onu_type') }}" placeholder="e.g., HG8145V5">
                                @error('onu_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="bandwidth_profile" class="form-label">Bandwidth / Package / Plan</label>
                                <input type="text" class="form-control @error('bandwidth_profile') is-invalid @enderror"
                                       id="bandwidth_profile" name="bandwidth_profile" value="{{ old('bandwidth_profile') }}" placeholder="e.g., 20Mbps Home">
                                @error('bandwidth_profile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="onu_physical_address" class="form-label">ONU Physical Address</label>
                                <input type="text" class="form-control @error('onu_physical_address') is-invalid @enderror"
                                       id="onu_physical_address" name="onu_physical_address" value="{{ old('onu_physical_address') }}"
                                       placeholder="Where the ONU is physically installed">
                                @error('onu_physical_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if($fat ?? null)
                            <input type="hidden" name="fat_id" value="{{ $fat->id }}">
                        @else
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="fat_id" class="form-label">FAT (network attachment point)</label>
                                    <select class="form-select @error('fat_id') is-invalid @enderror" id="fat_id" name="fat_id">
                                        <option value="">Not connected yet</option>
                                        @foreach($allFats ?? [] as $option)
                                            <option value="{{ $option->id }}" {{ old('fat_id') == $option->id ? 'selected' : '' }}>
                                                {{ $option->fdt->ponPort->oltSlot->olt->name }} / FDT-{{ $option->fdt->fdt_number }} / FAT-{{ $option->fat_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('fat_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="Suspended" {{ old('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ ($fat ?? null) ? route('fats.show', $fat) : route('customers.index') }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Create Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
