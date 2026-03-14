@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit Slot - ' . $slot->identifier)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Edit Slot - {{ $slot->identifier }}</h1>
                    <p class="text-muted mb-0">Update slot configuration and status</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('olts.show', $slot->olt) }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to OLT
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Slot Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('slots.update', $slot) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="slot_number" class="form-label">Slot Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('slot_number') is-invalid @enderror"
                                       id="slot_number" name="slot_number" value="{{ old('slot_number', $slot->slot_number) }}"
                                       min="0" max="31" placeholder="0" required>
                                @error('slot_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Slot numbers typically range from 0 to {{ ($slot->olt->total_slots ?? 16) - 1 }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="slot_type" class="form-label">Slot Type</label>
                                <select class="form-select @error('slot_type') is-invalid @enderror" id="slot_type" name="slot_type">
                                    <option value="">Select Slot Type</option>
                                    <option value="GPON" {{ old('slot_type', $slot->slot_type) == 'GPON' ? 'selected' : '' }}>GPON</option>
                                    <option value="XGSPON" {{ old('slot_type', $slot->slot_type) == 'XGSPON' ? 'selected' : '' }}>XGS-PON</option>
                                    <option value="EPON" {{ old('slot_type', $slot->slot_type) == 'EPON' ? 'selected' : '' }}>EPON</option>
                                    <option value="10G-EPON" {{ old('slot_type', $slot->slot_type) == '10G-EPON' ? 'selected' : '' }}>10G-EPON</option>
                                </select>
                                @error('slot_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status', $slot->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $slot->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="faulty" {{ old('status', $slot->status) == 'faulty' ? 'selected' : '' }}>Faulty</option>
                                    <option value="spare" {{ old('status', $slot->status) == 'spare' ? 'selected' : '' }}>Spare</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('olts.show', $slot->olt) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Update Slot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Slot Information & Help -->
        <div class="col-lg-4">
            <!-- Current Slot Info Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-grid me-1"></i>Current Slot Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Slot Identifier</label>
                            <div class="fw-bold">{{ $slot->identifier }}</div>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label text-muted">Current Number</label>
                            <div class="fw-bold">{{ $slot->slot_number }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Current Status</label>
                            <div>
                                <span class="badge {{ $slot->status_badge_class }}">{{ ucfirst($slot->status) }}</span>
                            </div>
                        </div>

                        @if($slot->slot_type)
                        <div class="col-12">
                            <label class="form-label text-muted">Current Type</label>
                            <div>
                                <span class="badge {{ $slot->slot_type_badge_class }}">{{ $slot->slot_type }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label text-muted">PON Ports</label>
                            <div class="fw-bold">{{ $slot->ponPorts->count() ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OLT Info Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-server me-1"></i>OLT Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">OLT Name</label>
                            <div class="fw-bold">{{ $slot->olt->name }}</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted">IP Address</label>
                            <div><code>{{ $slot->olt->ip_address }}</code></div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Total Slots</label>
                            <div class="fw-bold">{{ $slot->olt->total_slots ?? 'N/A' }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Used Slots</label>
                            <div class="fw-bold">{{ $slot->olt->slots->count() }}</div>
                        </div>

                        @if($slot->olt->vendor)
                        <div class="col-12">
                            <label class="form-label text-muted">Vendor</label>
                            <div>
                                <span class="badge {{ $slot->olt->vendor_badge_class }}">{{ $slot->olt->vendor }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Warning Card -->
            @if($slot->ponPorts->count() > 0)
            <div class="card mt-4 border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="card-title mb-0 text-warning">
                        <i class="bx bx-warning me-1"></i>Important Notice
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">
                        This slot has <strong>{{ $slot->ponPorts->count() }} configured PON ports</strong>.
                    </p>
                    <p class="text-muted mb-0">
                        Changing the slot status to "inactive" or "faulty" may affect all associated PON ports and connected services.
                    </p>
                </div>
            </div>
            @endif

            <!-- Help Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-help-circle me-1"></i>Slot Configuration Guide
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Status Options</h6>
                    <ul class="list-unstyled text-muted small mb-3">
                        <li><span class="badge badge-xs bg-success me-1">Active</span> - In service and operational</li>
                        <li><span class="badge badge-xs bg-secondary me-1">Inactive</span> - Not in use or disabled</li>
                        <li><span class="badge badge-xs bg-danger me-1">Faulty</span> - Needs repair or replacement</li>
                        <li><span class="badge badge-xs bg-warning me-1">Spare</span> - Backup slot ready for use</li>
                    </ul>

                    <h6>Slot Types</h6>
                    <ul class="list-unstyled text-muted small mb-3">
                        <li><span class="badge badge-xs bg-primary me-1">GPON</span> - Gigabit PON</li>
                        <li><span class="badge badge-xs bg-success me-1">XGS-PON</span> - 10G Symmetric PON</li>
                        <li><span class="badge badge-xs bg-info me-1">EPON</span> - Ethernet PON</li>
                        <li><span class="badge badge-xs bg-warning me-1">10G-EPON</span> - 10G Ethernet PON</li>
                    </ul>

                    <h6>Best Practices</h6>
                    <p class="text-muted small mb-0">
                        • Coordinate status changes with network operations<br>
                        • Verify no active services before deactivating<br>
                        • Document reason for status changes
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

