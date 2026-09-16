@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit FAT-' . $fat->fat_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Edit FAT-{{ $fat->fat_number }}</h1>
                    <p class="text-muted mb-0">FDT-{{ $fat->fdt->fdt_number }} / {{ $fat->fdt->ponPort->identifier }}</p>
                </div>
                <a href="{{ route('fats.show', $fat) }}" class="btn btn-outline-secondary btn-xs">
                    <i class="bx bx-arrow-back me-1"></i>Back to FAT
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
                    <form action="{{ route('fats.update', $fat) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="fat_number" class="form-label">FAT Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('fat_number') is-invalid @enderror"
                                       id="fat_number" name="fat_number" value="{{ old('fat_number', $fat->fat_number) }}"
                                       min="1" required>
                                @error('fat_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="fat_type" class="form-label">FAT Type</label>
                                <select class="form-select @error('fat_type') is-invalid @enderror" id="fat_type" name="fat_type">
                                    <option value="">Select FAT Type</option>
                                    @foreach(['4-port', '8-port', '12-port', '16-port'] as $type)
                                        <option value="{{ $type }}" {{ old('fat_type', $fat->fat_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
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
                                       id="location" name="location" value="{{ old('location', $fat->location) }}">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                       id="capacity" name="capacity" value="{{ old('capacity', $fat->capacity) }}"
                                       min="1" max="16">
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
                                        <option value="{{ $status }}" {{ old('status', $fat->status) == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('fats.show', $fat) }}" class="btn btn-secondary btn-xs">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save me-1"></i>Update FAT
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
                            <div class="fw-bold">{{ $fat->fdt->ponPort->oltSlot->olt->name }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">FDT</label>
                            <div class="fw-bold">FDT-{{ $fat->fdt->fdt_number }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">Customers attached</label>
                            <div class="fw-bold">{{ $fat->customers->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
