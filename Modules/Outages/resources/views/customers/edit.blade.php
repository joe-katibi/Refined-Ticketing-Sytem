@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit ' . $customer->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Edit {{ $customer->name }}</h1>
                    <p class="text-muted mb-0">Account {{ $customer->account_number }}</p>
                </div>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary btn-xs">
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
                    <form action="{{ route('customers.update', $customer) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                       id="account_number" name="account_number" value="{{ old('account_number', $customer->account_number) }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="mobile_number" class="form-label">Mobile Number</label>
                                <input type="text" class="form-control @error('mobile_number') is-invalid @enderror"
                                       id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $customer->mobile_number) }}">
                                @error('mobile_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="alternative_number" class="form-label">Alternative Number</label>
                                <input type="text" class="form-control @error('alternative_number') is-invalid @enderror"
                                       id="alternative_number" name="alternative_number" value="{{ old('alternative_number', $customer->alternative_number) }}">
                                @error('alternative_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                       id="address" name="address" value="{{ old('address', $customer->address) }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="onu_type" class="form-label">ONU Type</label>
                                <input type="text" class="form-control @error('onu_type') is-invalid @enderror"
                                       id="onu_type" name="onu_type" value="{{ old('onu_type', $customer->onu_type) }}">
                                @error('onu_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="bandwidth_profile" class="form-label">Bandwidth / Package / Plan</label>
                                <input type="text" class="form-control @error('bandwidth_profile') is-invalid @enderror"
                                       id="bandwidth_profile" name="bandwidth_profile" value="{{ old('bandwidth_profile', $customer->bandwidth_profile) }}">
                                @error('bandwidth_profile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="onu_physical_address" class="form-label">ONU Physical Address</label>
                                <input type="text" class="form-control @error('onu_physical_address') is-invalid @enderror"
                                       id="onu_physical_address" name="onu_physical_address" value="{{ old('onu_physical_address', $customer->onu_physical_address) }}">
                                @error('onu_physical_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="fat_id" class="form-label">FAT (network attachment point)</label>
                                <select class="form-select @error('fat_id') is-invalid @enderror" id="fat_id" name="fat_id">
                                    <option value="">Not connected</option>
                                    @foreach($allFats as $option)
                                        <option value="{{ $option->id }}" {{ old('fat_id', $customer->fat_id) == $option->id ? 'selected' : '' }}>
                                            {{ $option->fdt->ponPort->oltSlot->olt->name }} / FDT-{{ $option->fdt->fdt_number }} / FAT-{{ $option->fat_number }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fat_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    @foreach(['Active', 'Inactive', 'Suspended'] as $status)
                                        <option value="{{ $status }}" {{ old('status', $customer->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Update Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
