@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Create New OLT')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Create New OLT</h1>
                    <p class="text-muted mb-0">Add a new OLT device to the system</p>
                </div>
                <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary btn-xs">
                    <i class="bx bx-arrow-back me-1"></i>Back to OLTs
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">OLT Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('olts.store') }}" method="POST">
                        @csrf

                        <!-- Basic Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">OLT Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="e.g., OLT-Nyali-01" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('ip_address') is-invalid @enderror"
                                       id="ip_address" name="ip_address" value="{{ old('ip_address') }}"
                                       placeholder="192.168.1.100" required>
                                @error('ip_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Vendor Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="vendor" class="form-label">Vendor</label>
                                <select class="form-select @error('vendor') is-invalid @enderror" id="vendor" name="vendor">
                                    <option value="">Select Vendor</option>
                                    <option value="Huawei" {{ old('vendor') == 'Huawei' ? 'selected' : '' }}>Huawei</option>
                                    <option value="ZTE" {{ old('vendor') == 'ZTE' ? 'selected' : '' }}>ZTE</option>
                                    <option value="FiberHome" {{ old('vendor') == 'FiberHome' ? 'selected' : '' }}>FiberHome</option>
                                    <option value="Nokia" {{ old('vendor') == 'Nokia' ? 'selected' : '' }}>Nokia</option>
                                    <option value="Other" {{ old('vendor') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('vendor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" class="form-control @error('model') is-invalid @enderror"
                                       id="model" name="model" value="{{ old('model') }}"
                                       placeholder="e.g., MA5800-X17">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Location and Capacity -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                       id="location" name="location" value="{{ old('location') }}"
                                       placeholder="e.g., Cabinet 1, POP Site A">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="total_slots" class="form-label">Total Slots</label>
                                <input type="number" class="form-control @error('total_slots') is-invalid @enderror"
                                       id="total_slots" name="total_slots" value="{{ old('total_slots') }}"
                                       min="1" max="32" placeholder="16">
                                @error('total_slots')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Software and Status -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="software_version" class="form-label">Software Version</label>
                                <input type="text" class="form-control @error('software_version') is-invalid @enderror"
                                       id="software_version" name="software_version" value="{{ old('software_version') }}"
                                       placeholder="e.g., V800R017C10">
                                @error('software_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="Maintenance" {{ old('status') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="Faulty" {{ old('status') == 'Faulty' ? 'selected' : '' }}>Faulty</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('olts.index') }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Create OLT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-info-circle me-1"></i>Information
                    </h5>
                </div>
                <div class="card-body">
                    <h6>OLT Naming Convention</h6>
                    <p class="text-muted small mb-3">
                        Use a consistent naming pattern like: <code>OLT-[Location]-[Number]</code><br>
                        Example: <code>OLT-Nyali-01</code>, <code>OLT-Mombasa-02</code>
                    </p>

                    <h6>IP Address</h6>
                    <p class="text-muted small mb-3">
                        Management IP address for SNMP monitoring and remote access.
                    </p>

                    <h6>Slot Configuration</h6>
                    <p class="text-muted small mb-3">
                        After creating the OLT, you can add individual slots and configure PON ports for each slot.
                    </p>

                    <h6>Status Options</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><span class="badge badge-xs bg-success me-1">Active</span> - Operational</li>
                        <li><span class="badge badge-xs bg-secondary me-1">Inactive</span> - Not in use</li>
                        <li><span class="badge badge-xs bg-warning me-1">Maintenance</span> - Under maintenance</li>
                        <li><span class="badge badge-xs bg-danger me-1">Faulty</span> - Needs repair</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

