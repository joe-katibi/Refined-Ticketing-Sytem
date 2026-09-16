@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit FDT-' . $fdt->fdt_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Edit FDT-{{ $fdt->fdt_number }}</h1>
                    <p class="text-muted mb-0">{{ $fdt->ponPort->identifier }}</p>
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
                    <h5 class="card-title mb-0">FDT Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('fdts.update', $fdt) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="fdt_number" class="form-label">FDT Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('fdt_number') is-invalid @enderror"
                                       id="fdt_number" name="fdt_number" value="{{ old('fdt_number', $fdt->fdt_number) }}"
                                       min="1" required>
                                @error('fdt_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="fdt_type" class="form-label">FDT Type</label>
                                <select class="form-select @error('fdt_type') is-invalid @enderror" id="fdt_type" name="fdt_type">
                                    <option value="">Select FDT Type</option>
                                    @foreach(['8-port', '16-port', '24-port', '32-port'] as $type)
                                        <option value="{{ $type }}" {{ old('fdt_type', $fdt->fdt_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
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
                                       id="location" name="location" value="{{ old('location', $fdt->location) }}">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                       id="capacity" name="capacity" value="{{ old('capacity', $fdt->capacity) }}"
                                       min="1" max="32">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    @foreach(['active', 'inactive', 'faulty', 'maintenance'] as $status)
                                        <option value="{{ $status }}" {{ old('status', $fdt->status) == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('fdts.show', $fdt) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Update FDT
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
                            <label class="form-label text-muted">FATs attached</label>
                            <div class="fw-bold">{{ $fdt->fats->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
