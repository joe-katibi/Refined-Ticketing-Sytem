@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Add FDT to ' . $ponPort->identifier)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Add FDT to {{ $ponPort->identifier }}</h1>
                    <p class="text-muted mb-0">Configure a new Fiber Distribution Terminal for this PON port</p>
                </div>
                <a href="{{ route('olts.show', $ponPort->oltSlot->olt) }}" class="btn btn-outline-secondary btn-xs">
                    <i class="bx bx-arrow-back me-1"></i>Back to OLT
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">FDT Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('ports.fdts.store', $ponPort) }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="fdt_number" class="form-label">FDT Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('fdt_number') is-invalid @enderror"
                                       id="fdt_number" name="fdt_number" value="{{ old('fdt_number') }}"
                                       min="1" placeholder="1" required>
                                @error('fdt_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="fdt_type" class="form-label">FDT Type</label>
                                <select class="form-select @error('fdt_type') is-invalid @enderror" id="fdt_type" name="fdt_type">
                                    <option value="">Select FDT Type</option>
                                    <option value="8-port" {{ old('fdt_type') == '8-port' ? 'selected' : '' }}>8-port</option>
                                    <option value="16-port" {{ old('fdt_type') == '16-port' ? 'selected' : '' }}>16-port</option>
                                    <option value="24-port" {{ old('fdt_type') == '24-port' ? 'selected' : '' }}>24-port</option>
                                    <option value="32-port" {{ old('fdt_type') == '32-port' ? 'selected' : '' }}>32-port</option>
                                </select>
                                @error('fdt_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                       id="location" name="location" value="{{ old('location') }}"
                                       placeholder="e.g., Pole 14, Estate Junction">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                       id="capacity" name="capacity" value="{{ old('capacity') }}"
                                       min="1" max="32" placeholder="e.g., 16">
                                @error('capacity')
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
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('olts.show', $ponPort->oltSlot->olt) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Create FDT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-plug me-1"></i>PON Port</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Identifier</label>
                            <div class="fw-bold">{{ $ponPort->identifier }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">Existing FDTs</label>
                            <div class="fw-bold">{{ $ponPort->fdts->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($ponPort->fdts->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-git-branch me-1"></i>Existing FDTs</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($ponPort->fdts->sortBy('fdt_number') as $existingFdt)
                            <div class="col-6">
                                <div class="card card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>FDT-{{ $existingFdt->fdt_number }}</strong>
                                        <span class="badge {{ $existingFdt->status_badge_class }}">{{ ucfirst($existingFdt->status) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
