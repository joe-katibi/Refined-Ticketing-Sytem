@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'FDT-' . $fdt->fdt_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">FDT-{{ $fdt->fdt_number }}</h1>
                    <p class="text-muted mb-0">{{ $fdt->ponPort->identifier }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('fdts.edit', $fdt) }}" class="btn btn-outline-primary btn-xs">
                        <i class="bx bx-edit me-1"></i>Edit FDT
                    </a>
                    <a href="{{ route('fdts.fats.create', $fdt) }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add FAT
                    </a>
                    <a href="{{ route('olts.show', $fdt->ponPort->oltSlot->olt) }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to OLT
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        <!-- FDT Information -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-git-branch me-1"></i>FDT Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Status</label>
                            <div><span class="badge {{ $fdt->status_badge_class }} fs-6">{{ ucfirst($fdt->status) }}</span></div>
                        </div>
                        @if($fdt->fdt_type)
                        <div class="col-12">
                            <label class="form-label text-muted">Type</label>
                            <div><span class="badge {{ $fdt->fdt_type_badge_class }}">{{ $fdt->fdt_type }}</span></div>
                        </div>
                        @endif
                        @if($fdt->location)
                        <div class="col-12">
                            <label class="form-label text-muted">Location</label>
                            <div>{{ $fdt->location }}</div>
                        </div>
                        @endif
                        @if($fdt->capacity)
                        <div class="col-12">
                            <label class="form-label text-muted">Capacity</label>
                            <div class="fw-bold">{{ $fdt->capacity }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mt-4">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- FATs -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bx bx-grid-alt me-1"></i>FATs</h5>
                    <a href="{{ route('fdts.fats.create', $fdt) }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add FAT
                    </a>
                </div>
                <div class="card-body">
                    @if($fdt->fats->count() > 0)
                        <div class="row g-2">
                            @foreach($fdt->fats->sortBy('fat_number') as $fat)
                                <div class="col-md-6">
                                    <div class="card card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <a href="{{ route('fats.show', $fat) }}" class="fw-bold text-decoration-none">FAT-{{ $fat->fat_number }}</a>
                                                @if($fat->fat_type)
                                                    <br><span class="badge {{ $fat->fat_type_badge_class }} badge-sm">{{ $fat->fat_type }}</span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge {{ $fat->status_badge_class }}">{{ ucfirst($fat->status) }}</span>
                                                <a href="{{ route('fats.edit', $fat) }}" class="btn btn-outline-secondary btn-xs">
                                                    <i class="bx bx-edit" style="font-size: 12px;"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bx bx-grid-alt text-muted display-6"></i>
                            <p class="text-muted mb-2">No FATs configured</p>
                            <a href="{{ route('fdts.fats.create', $fdt) }}" class="btn btn-primary btn-xs">
                                <i class="bx bx-plus me-1"></i>Add First FAT
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
