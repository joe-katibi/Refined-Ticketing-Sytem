@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit PON Port - ' . $port->identifier)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Edit PON Port - {{ $port->identifier }}</h1>
                    <p class="text-muted mb-0">Update PON port configuration and status</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('olts.show', $port->oltSlot->olt) }}" class="btn btn-outline-secondary btn-xs">
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
                    <h5 class="card-title mb-0">PON Port Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('ports.update', $port) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="pon_port_number" class="form-label">PON Port Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('pon_port_number') is-invalid @enderror"
                                       id="pon_port_number" name="pon_port_number" value="{{ old('pon_port_number', $port->pon_port_number) }}"
                                       min="0" max="15" placeholder="0" required>
                                @error('pon_port_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    PON port numbers typically range from 0 to 15
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="pon_port_type" class="form-label">PON Port Type</label>
                                <select class="form-select @error('pon_port_type') is-invalid @enderror" id="pon_port_type" name="pon_port_type">
                                    <option value="">Select Port Type</option>
                                    <option value="GPON" {{ old('pon_port_type', $port->pon_port_type) == 'GPON' ? 'selected' : '' }}>GPON</option>
                                    <option value="XGSPON" {{ old('pon_port_type', $port->pon_port_type) == 'XGSPON' ? 'selected' : '' }}>XGS-PON</option>
                                    <option value="EPON" {{ old('pon_port_type', $port->pon_port_type) == 'EPON' ? 'selected' : '' }}>EPON</option>
                                    <option value="10G-EPON" {{ old('pon_port_type', $port->pon_port_type) == '10G-EPON' ? 'selected' : '' }}>10G-EPON</option>
                                </select>
                                @error('pon_port_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($port->oltSlot->slot_type)
                                    <div class="form-text">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Slot type is {{ $port->oltSlot->slot_type }}. Port type should typically match.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status', $port->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $port->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="faulty" {{ old('status', $port->status) == 'faulty' ? 'selected' : '' }}>Faulty</option>
                                    <option value="spare" {{ old('status', $port->status) == 'spare' ? 'selected' : '' }}>Spare</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('olts.show', $port->oltSlot->olt) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Update PON Port
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Port & Slot Information -->
        <div class="col-lg-4">
            <!-- Current Port Info Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-plug me-1"></i>Current Port Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Port Identifier</label>
                            <div class="fw-bold">{{ $port->identifier }}</div>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label text-muted">Current Number</label>
                            <div class="fw-bold">{{ $port->pon_port_number }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Current Status</label>
                            <div>
                                <span class="badge {{ $port->status_badge_class }}">{{ ucfirst($port->status) }}</span>
                            </div>
                        </div>

                        @if($port->pon_port_type)
                        <div class="col-12">
                            <label class="form-label text-muted">Current Type</label>
                            <div>
                                <span class="badge {{ $port->pon_port_type_badge_class }}">{{ $port->pon_port_type }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Slot Info Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-grid me-1"></i>Slot Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Slot Identifier</label>
                            <div class="fw-bold">{{ $port->oltSlot->identifier }}</div>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label text-muted">Slot Number</label>
                            <div class="fw-bold">{{ $port->oltSlot->slot_number }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Slot Status</label>
                            <div>
                                <span class="badge {{ $port->oltSlot->status_badge_class }}">{{ ucfirst($port->oltSlot->status) }}</span>
                            </div>
                        </div>

                        @if($port->oltSlot->slot_type)
                        <div class="col-12">
                            <label class="form-label text-muted">Slot Type</label>
                            <div>
                                <span class="badge {{ $port->oltSlot->slot_type_badge_class }}">{{ $port->oltSlot->slot_type }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label text-muted">Total Ports in Slot</label>
                            <div class="fw-bold">{{ $port->oltSlot->ponPorts->count() }}</div>
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
                            <div class="fw-bold">{{ $port->oltSlot->olt->name }}</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted">IP Address</label>
                            <div><code>{{ $port->oltSlot->olt->ip_address }}</code></div>
                        </div>

                        @if($port->oltSlot->olt->vendor)
                        <div class="col-12">
                            <label class="form-label text-muted">Vendor</label>
                            <div>
                                <span class="badge {{ $port->oltSlot->olt->vendor_badge_class }}">{{ $port->oltSlot->olt->vendor }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Warning Card -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info bg-opacity-10">
                    <h5 class="card-title mb-0 text-info">
                        <i class="bx bx-info-circle me-1"></i>Service Impact
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">
                        Changing this PON port status may affect connected customers and services.
                    </p>
                    <p class="text-muted mb-0">
                        • <strong>Inactive</strong>: Port will be disabled<br>
                        • <strong>Faulty</strong>: Port marked for maintenance<br>
                        • <strong>Spare</strong>: Port ready for backup use
                    </p>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-help-circle me-1"></i>PON Port Guide
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Status Options</h6>
                    <ul class="list-unstyled text-muted small mb-3">
                        <li><span class="badge badge-xs bg-success me-1">Active</span> - In service and operational</li>
                        <li><span class="badge badge-xs bg-secondary me-1">Inactive</span> - Disabled or not in use</li>
                        <li><span class="badge badge-xs bg-danger me-1">Faulty</span> - Needs repair or replacement</li>
                        <li><span class="badge badge-xs bg-warning me-1">Spare</span> - Backup port ready for use</li>
                    </ul>

                    <h6>Port Types</h6>
                    <ul class="list-unstyled text-muted small mb-3">
                        <li><span class="badge badge-xs bg-primary me-1">GPON</span> - 2.5G downstream</li>
                        <li><span class="badge badge-xs bg-success me-1">XGS-PON</span> - 10G symmetric</li>
                        <li><span class="badge badge-xs bg-info me-1">EPON</span> - Ethernet based</li>
                        <li><span class="badge badge-xs bg-warning me-1">10G-EPON</span> - 10G Ethernet</li>
                    </ul>

                    <h6>Best Practices</h6>
                    <p class="text-muted small mb-0">
                        • Check for active customers before deactivating<br>
                        • Coordinate with NOC for status changes<br>
                        • Document maintenance activities
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-select port type based on slot type
    const slotType = '{{ $port->oltSlot->slot_type }}';
    const portTypeSelect = document.getElementById('pon_port_type');
    
    // Only auto-select if current port type is empty and slot has a type
    if (slotType && !portTypeSelect.value) {
        portTypeSelect.value = slotType;
    }
});
</script>
@endsection

