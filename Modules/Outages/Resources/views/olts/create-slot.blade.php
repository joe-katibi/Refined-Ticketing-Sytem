@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Add Slot to ' . $olt->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Add Slot to {{ $olt->name }}</h1>
                    <p class="text-muted mb-0">Configure a new slot for this OLT device</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('olts.show', $olt) }}" class="btn btn-outline-secondary btn-xs">
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
                    <form action="{{ route('olts.slots.store', $olt) }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="slot_number" class="form-label">Slot Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('slot_number') is-invalid @enderror"
                                       id="slot_number" name="slot_number" value="{{ old('slot_number') }}"
                                       min="0" max="31" placeholder="0" required>
                                @error('slot_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Slot numbers typically range from 0 to {{ ($olt->total_slots ?? 16) - 1 }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="slot_type" class="form-label">Slot Type</label>
                                <select class="form-select @error('slot_type') is-invalid @enderror" id="slot_type" name="slot_type">
                                    <option value="">Select Slot Type</option>
                                    <option value="GPON" {{ old('slot_type') == 'GPON' ? 'selected' : '' }}>GPON</option>
                                    <option value="XGSPON" {{ old('slot_type') == 'XGSPON' ? 'selected' : '' }}>XGS-PON</option>
                                    <option value="EPON" {{ old('slot_type') == 'EPON' ? 'selected' : '' }}>EPON</option>
                                    <option value="10G-EPON" {{ old('slot_type') == '10G-EPON' ? 'selected' : '' }}>10G-EPON</option>
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
                            <a href="{{ route('olts.show', $olt) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Create Slot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- OLT Information & Help -->
        <div class="col-lg-4">
            <!-- OLT Info Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-server me-1"></i>OLT Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">OLT Name</label>
                            <div class="fw-bold">{{ $olt->name }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted">IP Address</label>
                            <div><code>{{ $olt->ip_address }}</code></div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Total Slots</label>
                            <div class="fw-bold">{{ $olt->total_slots ?? 'N/A' }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Used Slots</label>
                            <div class="fw-bold">{{ $olt->slots->count() }}</div>
                        </div>

                        @if($olt->vendor)
                        <div class="col-12">
                            <label class="form-label text-muted">Vendor</label>
                            <div>
                                <span class="badge {{ $olt->vendor_badge_class }}">{{ $olt->vendor }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Existing Slots -->
            @if($olt->slots->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-grid me-1"></i>Existing Slots
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($olt->slots->sortBy('slot_number') as $slot)
                            <div class="col-6">
                                <div class="card card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Slot {{ $slot->slot_number }}</strong>
                                            @if($slot->slot_type)
                                                <br><span class="badge {{ $slot->slot_type_badge_class }} badge-sm">{{ $slot->slot_type }}</span>
                                            @endif
                                        </div>
                                        <span class="badge {{ $slot->status_badge_class }}">{{ ucfirst($slot->status) }}</span>
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
                        <i class="bx bx-help-circle me-1"></i>Slot Configuration Guide
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Slot Numbering</h6>
                    <p class="text-muted small mb-3">
                        Slots are typically numbered starting from 0. Check your OLT documentation for the correct numbering scheme.
                    </p>

                    <h6>Slot Types</h6>
                    <ul class="list-unstyled text-muted small mb-3">
                        <li><span class="badge badge-xs bg-primary me-1">GPON</span> - Gigabit PON</li>
                        <li><span class="badge badge-xs bg-success me-1">XGS-PON</span> - 10G Symmetric PON</li>
                        <li><span class="badge badge-xs bg-info me-1">EPON</span> - Ethernet PON</li>
                        <li><span class="badge badge-xs bg-warning me-1">10G-EPON</span> - 10G Ethernet PON</li>
                    </ul>

                    <h6>Status Options</h6>
                    <ul class="list-unstyled text-muted small">
                        <li><span class="badge badge-xs bg-success me-1">Active</span> - In service</li>
                        <li><span class="badge badge-xs bg-secondary me-1">Inactive</span> - Not in use</li>
                        <li><span class="badge badge-xs bg-danger me-1">Faulty</span> - Needs repair</li>
                        <li><span class="badge badge-xs bg-warning me-1">Spare</span> - Backup slot</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

