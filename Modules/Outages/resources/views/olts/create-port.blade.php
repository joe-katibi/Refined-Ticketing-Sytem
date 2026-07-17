@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Add PON Port to ' . $slot->identifier)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Add PON Port to {{ $slot->identifier }}</h1>
                    <p class="text-muted mb-0">Configure a new PON port for this slot</p>
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
                    <h5 class="card-title mb-0">PON Port Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('slots.ports.store', $slot) }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="pon_port_number" class="form-label">PON Port Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('pon_port_number') is-invalid @enderror"
                                       id="pon_port_number" name="pon_port_number" value="{{ old('pon_port_number') }}"
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
                                    <option value="GPON" {{ old('pon_port_type') == 'GPON' ? 'selected' : '' }}>GPON</option>
                                    <option value="XGSPON" {{ old('pon_port_type') == 'XGSPON' ? 'selected' : '' }}>XGS-PON</option>
                                    <option value="EPON" {{ old('pon_port_type') == 'EPON' ? 'selected' : '' }}>EPON</option>
                                    <option value="10G-EPON" {{ old('pon_port_type') == '10G-EPON' ? 'selected' : '' }}>10G-EPON</option>
                                </select>
                                @error('pon_port_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($slot->slot_type)
                                    <div class="form-text">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Slot type is {{ $slot->slot_type }}. Port type should typically match.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="faulty" {{ old('status') == 'faulty' ? 'selected' : '' }}>Faulty</option>
                                    <option value="spare" {{ old('status') == 'spare' ? 'selected' : '' }}>Spare</option>
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
                                <i class="bx bx-save me-1"></i>Create PON Port
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Slot & OLT Information -->
        <div class="col-lg-4">
            <!-- Slot Info Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-grid me-1"></i>Slot Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Slot Identifier</label>
                            <div class="fw-bold">{{ $slot->identifier }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Slot Number</label>
                            <div class="fw-bold">{{ $slot->slot_number }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Slot Status</label>
                            <div>
                                <span class="badge {{ $slot->status_badge_class }}">{{ ucfirst($slot->status) }}</span>
                            </div>
                        </div>

                        @if($slot->slot_type)
                        <div class="col-12">
                            <label class="form-label text-muted">Slot Type</label>
                            <div>
                                <span class="badge {{ $slot->slot_type_badge_class }}">{{ $slot->slot_type }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="col-12">
                            <label class="form-label text-muted">Existing Ports</label>
                            <div class="fw-bold">{{ $slot->ponPorts->count() }}</div>
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

            <!-- Existing Ports -->
            @if($slot->ponPorts->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-plug me-1"></i>Existing PON Ports
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($slot->ponPorts->sortBy('pon_port_number') as $port)
                            <div class="col-6">
                                <div class="card card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Port {{ $port->pon_port_number }}</strong>
                                            @if($port->pon_port_type)
                                                <br><span class="badge {{ $port->pon_port_type_badge_class }} badge-sm">{{ $port->pon_port_type }}</span>
                                            @endif
                                        </div>
                                        <span class="badge {{ $port->status_badge_class }}">{{ ucfirst($port->status) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Help Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-help-circle me-1"></i>PON Port Guide
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Port Numbering</h6>
                    <p class="text-muted small mb-3">
                        PON ports are typically numbered from 0 to 15, depending on the slot card capacity.
                    </p>

                    <h6>Port Types</h6>
                    <ul class="list-unstyled text-muted small mb-3">
                        <li><span class="badge badge-xs bg-primary me-1">GPON</span> - 2.5G downstream</li>
                        <li><span class="badge badge-xs bg-success me-1">XGS-PON</span> - 10G symmetric</li>
                        <li><span class="badge badge-xs bg-info me-1">EPON</span> - Ethernet based</li>
                        <li><span class="badge badge-xs bg-warning me-1">10G-EPON</span> - 10G Ethernet</li>
                    </ul>

                    <h6>Status Options</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><span class="badge badge-xs bg-success me-1">Active</span> - In service</li>
                        <li><span class="badge badge-xs bg-secondary me-1">Inactive</span> - Not in use</li>
                        <li><span class="badge badge-xs bg-danger me-1">Faulty</span> - Needs repair</li>
                        <li><span class="badge badge-xs bg-warning me-1">Spare</span> - Backup port</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-select port type based on slot type
    const slotType = '{{ $slot->slot_type }}';
    const portTypeSelect = document.getElementById('pon_port_type');

    if (slotType && !portTypeSelect.value) {
        portTypeSelect.value = slotType;
    }
});
</script>
@endsection

