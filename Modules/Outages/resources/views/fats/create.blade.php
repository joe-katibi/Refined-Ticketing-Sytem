@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Add FAT to FDT-' . $fdt->fdt_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Add FAT to FDT-{{ $fdt->fdt_number }}</h1>
                    <p class="text-muted mb-0">Configure a new Fiber Access Terminal for this FDT</p>
                </div>
                <a href="{{ route('fdts.show', $fdt) }}" class="btn btn-outline-secondary btn-xs">
                    <i class="bx bx-arrow-back me-1"></i>Back to FDT
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">FAT Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('fdts.fats.store', $fdt) }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="fat_number" class="form-label">FAT Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('fat_number') is-invalid @enderror"
                                       id="fat_number" name="fat_number" value="{{ old('fat_number') }}"
                                       min="1" placeholder="1" required>
                                @error('fat_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="fat_type" class="form-label">FAT Type</label>
                                <select class="form-select @error('fat_type') is-invalid @enderror" id="fat_type" name="fat_type">
                                    <option value="">Select FAT Type</option>
                                    <option value="4-port" {{ old('fat_type') == '4-port' ? 'selected' : '' }}>4-port</option>
                                    <option value="8-port" {{ old('fat_type') == '8-port' ? 'selected' : '' }}>8-port</option>
                                    <option value="12-port" {{ old('fat_type') == '12-port' ? 'selected' : '' }}>12-port</option>
                                    <option value="16-port" {{ old('fat_type') == '16-port' ? 'selected' : '' }}>16-port</option>
                                </select>
                                @error('fat_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                       id="location" name="location" value="{{ old('location') }}"
                                       placeholder="e.g., Rooftop, Building A">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                       id="capacity" name="capacity" value="{{ old('capacity') }}"
                                       min="1" max="16" placeholder="e.g., 8">
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
                            <a href="{{ route('fdts.show', $fdt) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Create FAT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-sitemap me-1"></i>Network Path</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">OLT</label>
                            <div class="fw-bold">{{ $fdt->ponPort->oltSlot->olt->name }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">Slot / Port</label>
                            <div class="fw-bold">{{ $fdt->ponPort->short_identifier }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">FDT</label>
                            <div class="fw-bold">FDT-{{ $fdt->fdt_number }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">Existing FATs</label>
                            <div class="fw-bold">{{ $fdt->fats->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
