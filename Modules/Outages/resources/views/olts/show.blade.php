@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'OLT Details - ' . $olt->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $olt->name }}</h1>
                    <p class="text-muted mb-0">OLT Device Details</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('olts.edit', $olt) }}" class="btn btn-outline-primary btn-xs">
                        <i class="bx bx-edit me-1"></i>Edit OLT
                    </a>
                    <a href="{{ route('olts.slots.create', $olt) }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add Slot
                    </a>
                    <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to OLTs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- OLT Information -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-server me-1"></i>OLT Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                <span class="badge {{ $olt->status_badge_class }} fs-6">{{ $olt->status }}</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted">IP Address</label>
                            <div><code>{{ $olt->ip_address }}</code></div>
                        </div>

                        @if($olt->vendor)
                        <div class="col-6">
                            <label class="form-label text-muted">Vendor</label>
                            <div>
                                <span class="badge {{ $olt->vendor_badge_class }}">{{ $olt->vendor }}</span>
                            </div>
                        </div>
                        @endif

                        @if($olt->model)
                        <div class="col-6">
                            <label class="form-label text-muted">Model</label>
                            <div>{{ $olt->model }}</div>
                        </div>
                        @endif

                        @if($olt->location)
                        <div class="col-12">
                            <label class="form-label text-muted">Location</label>
                            <div>{{ $olt->location }}</div>
                        </div>
                        @endif

                        @if($olt->software_version)
                        <div class="col-12">
                            <label class="form-label text-muted">Software Version</label>
                            <div><code>{{ $olt->software_version }}</code></div>
                        </div>
                        @endif

                        <div class="col-6">
                            <label class="form-label text-muted">Total Slots</label>
                            <div class="fw-bold">{{ $olt->total_slots ?? 'N/A' }}</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label text-muted">Available Slots</label>
                            <div class="fw-bold text-success">{{ $olt->available_slots }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-bar-chart me-1"></i>Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1 text-primary">{{ $olt->slots->count() }}</div>
                                <small class="text-muted">Total Slots</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1 text-success">{{ $olt->active_slots }}</div>
                                <small class="text-muted">Active Slots</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1 text-info">{{ $olt->total_pon_ports }}</div>
                                <small class="text-muted">Total Ports</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <div class="h4 mb-1 text-warning">{{ $olt->active_pon_ports }}</div>
                                <small class="text-muted">Active Ports</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-info-circle me-1"></i>Metadata
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Created</label>
                            <div>{{ $olt->created_at->format('M d, Y \a\t g:i A') }}</div>
                            @if($olt->creator)
                                <small class="text-muted">by {{ $olt->creator->name }}</small>
                            @endif
                        </div>

                        @if($olt->updated_at != $olt->created_at)
                        <div class="col-12">
                            <label class="form-label text-muted">Last Updated</label>
                            <div>{{ $olt->updated_at->format('M d, Y \a\t g:i A') }}</div>
                            @if($olt->editor)
                                <small class="text-muted">by {{ $olt->editor->name }}</small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Slots and Ports -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-grid me-1"></i>Slots & PON Ports
                    </h5>
                    <a href="{{ route('olts.slots.create', $olt) }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-plus me-1"></i>Add Slot
                    </a>
                </div>
                <div class="card-body">
                    @if($olt->slots->count() > 0)
                        <div class="accordion" id="slotsAccordion">
                            @foreach($olt->slots->sortBy('slot_number') as $slot)
                                <div class="accordion-item">
                                    <h2 class="accordion-header d-flex" id="slot{{ $slot->id }}Header">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} flex-grow-1"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#slot{{ $slot->id }}"
                                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                                aria-controls="slot{{ $slot->id }}">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <div>
                                                    <strong>Slot {{ $slot->slot_number }}</strong>
                                                    @if($slot->slot_type)
                                                        <span class="badge {{ $slot->slot_type_badge_class }} ms-2">{{ $slot->slot_type }}</span>
                                                    @endif
                                                    <span class="badge {{ $slot->status_badge_class }} ms-1">{{ ucfirst($slot->status) }}</span>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted">{{ $slot->ponPorts->count() }} ports</small>
                                                </div>
                                            </div>
                                        </button>
                                        <div class="d-flex align-items-center px-3">
                                            <a href="{{ route('slots.edit', $slot) }}" class="btn btn-outline-primary btn-xs" onclick="event.stopPropagation();">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        </div>
                                    </h2>
                                    <div id="slot{{ $slot->id }}"
                                         class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                         aria-labelledby="slot{{ $slot->id }}Header"
                                         data-bs-parent="#slotsAccordion">
                                        <div class="accordion-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">PON Ports</h6>
                                                <a href="{{ route('slots.ports.create', $slot) }}" class="btn btn-outline-primary btn-xs">
                                                    <i class="bx bx-plus me-1"></i>Add Port
                                                </a>
                                            </div>

                                            @if($slot->ponPorts->count() > 0)
                                                <div class="row g-2">
                                                    @foreach($slot->ponPorts->sortBy('pon_port_number') as $port)
                                                        <div class="col-md-4">
                                                            <div class="card card-body p-2">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <strong>Port {{ $port->pon_port_number }}</strong>
                                                                        @if($port->pon_port_type)
                                                                            <br><span class="badge {{ $port->pon_port_type_badge_class }} badge-sm">{{ $port->pon_port_type }}</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="d-flex align-items-center gap-1">
                                                                        <span class="badge {{ $port->status_badge_class }}">{{ ucfirst($port->status) }}</span>
                                                                        <a href="{{ route('ports.edit', $port) }}" class="btn btn-outline-secondary btn-xs">
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
                                                    <i class="bx bx-plug text-muted display-6"></i>
                                                    <p class="text-muted mb-2">No PON ports configured</p>
                                                    <a href="{{ route('slots.ports.create', $slot) }}" class="btn btn-primary btn-xs">
                                                        <i class="bx bx-plus me-1"></i>Add First Port
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-grid text-muted display-1"></i>
                            <h4 class="mt-3">No Slots Configured</h4>
                            <p class="text-muted mb-4">Start by adding slots to this OLT device.</p>
                            <a href="{{ route('olts.slots.create', $olt) }}" class="btn btn-primary btn-xs">
                                <i class="bx bx-plus me-1"></i>Add First Slot
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

