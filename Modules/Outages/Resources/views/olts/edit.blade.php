@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit OLT - ' . $olt->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Edit OLT - {{ $olt->name }}</h1>
                    <p class="text-muted mb-0">Update OLT device information</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('olts.show', $olt) }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-show me-1"></i>View Details
                    </a>
                    <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to OLTs
                    </a>
                </div>
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
                    <form action="{{ route('olts.update', $olt) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">OLT Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $olt->name) }}"
                                       placeholder="e.g., OLT-Nyali-01" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('ip_address') is-invalid @enderror"
                                       id="ip_address" name="ip_address" value="{{ old('ip_address', $olt->ip_address) }}"
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
                                    <option value="Huawei" {{ old('vendor', $olt->vendor) == 'Huawei' ? 'selected' : '' }}>Huawei</option>
                                    <option value="ZTE" {{ old('vendor', $olt->vendor) == 'ZTE' ? 'selected' : '' }}>ZTE</option>
                                    <option value="FiberHome" {{ old('vendor', $olt->vendor) == 'FiberHome' ? 'selected' : '' }}>FiberHome</option>
                                    <option value="Nokia" {{ old('vendor', $olt->vendor) == 'Nokia' ? 'selected' : '' }}>Nokia</option>
                                    <option value="Other" {{ old('vendor', $olt->vendor) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('vendor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" class="form-control @error('model') is-invalid @enderror"
                                       id="model" name="model" value="{{ old('model', $olt->model) }}"
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
                                       id="location" name="location" value="{{ old('location', $olt->location) }}"
                                       placeholder="e.g., Cabinet 1, POP Site A">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="total_slots" class="form-label">Total Slots</label>
                                <input type="number" class="form-control @error('total_slots') is-invalid @enderror"
                                       id="total_slots" name="total_slots" value="{{ old('total_slots', $olt->total_slots) }}"
                                       min="1" max="32" placeholder="16">
                                @error('total_slots')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($olt->slots->count() > 0)
                                    <div class="form-text">
                                        <i class="bx bx-info-circle me-1"></i>
                                        This OLT currently has {{ $olt->slots->count() }} configured slots.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Software and Status -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="software_version" class="form-label">Software Version</label>
                                <input type="text" class="form-control @error('software_version') is-invalid @enderror"
                                       id="software_version" name="software_version" value="{{ old('software_version', $olt->software_version) }}"
                                       placeholder="e.g., V800R017C10">
                                @error('software_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('status', $olt->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status', $olt->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="Maintenance" {{ old('status', $olt->status) == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="Faulty" {{ old('status', $olt->status) == 'Faulty' ? 'selected' : '' }}>Faulty</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('olts.show', $olt) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Update OLT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Current Information Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-info-circle me-1"></i>Current Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Current Status</label>
                            <div>
                                <span class="badge {{ $olt->status_badge_class }} fs-6">{{ $olt->status }}</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted">Current IP</label>
                            <div><code>{{ $olt->ip_address }}</code></div>
                        </div>

                        @if($olt->vendor)
                        <div class="col-12">
                            <label class="form-label text-muted">Current Vendor</label>
                            <div>
                                <span class="badge {{ $olt->vendor_badge_class }}">{{ $olt->vendor }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="col-6">
                            <label class="form-label text-muted">Configured Slots</label>
                            <div class="fw-bold">{{ $olt->slots->count() }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Total Ports</label>
                            <div class="fw-bold">{{ $olt->ponPorts->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Card -->
            @if($olt->slots->count() > 0)
            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="card-title mb-0 text-warning">
                        <i class="bx bx-warning me-1"></i>Important Notice
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">
                        This OLT has <strong>{{ $olt->slots->count() }} configured slots</strong>
                        and <strong>{{ $olt->ponPorts->count() }} PON ports</strong>.
                    </p>
                    <p class="text-muted mb-0">
                        Changes to critical settings may affect existing configurations.
                        Please ensure all changes are coordinated with network operations.
                    </p>
                </div>
            </div>
            @endif

            <!-- Help Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-help-circle me-1"></i>Help
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Status Changes</h6>
                    <p class="text-muted small mb-3">
                        Setting status to "Maintenance" or "Faulty" may trigger alerts in monitoring systems.
                    </p>

                    <h6>IP Address Changes</h6>
                    <p class="text-muted small mb-3">
                        Changing the IP address will require updating monitoring and management systems.
                    </p>

                    <h6>Slot Configuration</h6>
                    <p class="text-muted small mb-0">
                        The total slots value should match the physical capacity of the OLT device.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

