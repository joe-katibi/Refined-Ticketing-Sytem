@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Appointments')

@push('style')
<style>
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    .status-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Appointments</h3>
                <a href="{{ route('appointment.appointments.create') }}" class="btn btn-primary btn-xs">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="mb-3">
                    <form method="GET" action="{{ route('appointment.appointments.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Filter by Status</label>
                            <select name="status" id="status" class="form-select status-select">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->name }}" 
                                        {{ request('status') == $status->name ? 'selected' : '' }}
                                        data-color="{{ $status->color }}"
                                        data-badge-class="{{ $status->badge_class }}"
                                    >
                                        {{ $status->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('appointment.appointments.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="responsive-table-wrapper">
                    <table class="table table-bordered responsive-table mobile-card-table" id="appointments-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ticket ID</th>
                                <th class="d-none-mobile">Account</th>
                                <th class="d-none-tablet">Type</th>
                                <th class="d-none-laptop">Sub Type</th>
                                <th class="d-none-tablet">OLT</th>
                                <th class="d-none-mobile">Scheduled</th>
                                <th>Status</th>
                                <th class="d-none-mobile">Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $appointment->appointment_ticket_id }}</td>
                                    <td>{{ $appointment->account_number }}</td>
                                    <td>{{ $appointment->type->type_name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->subType->sub_type_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($appointment->olt)
                                            <span class="badge badge-xs bg-info">{{ $appointment->olt->name }}</span>
                                            <br><small class="text-muted">{{ $appointment->olt->location ?? 'No location' }}</small>
                                            @if($appointment->slot_id)
                                                <br><small class="text-muted">Slot: {{ $appointment->slot_id }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-xs bg-secondary">No OLT</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($appointment->scheduled_date)
                                            {{ $appointment->scheduled_date->format('M d, Y') }}<br>
                                            <small class="text-muted">{{ $appointment->scheduled_time ?? '' }}</small>
                                        @else
                                            Not Scheduled
                                        @endif
                                    </td>
                                    <td>{!! $appointment->status_badge !!}</td>
                                    <td>{!! $appointment->priority_badge !!}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('appointment.appointments.show', $appointment->id) }}"
                                              class="btn btn-icon btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                              <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('appointment.appointments.edit', $appointment->id) }}"
                                              class="btn btn-icon btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('appointment.appointments.histories.show', $appointment->id) }}"
                                              class="btn btn-icon btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View History">
                                                <i class="fas fa-history"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No appointments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Card View -->
                <div class="mobile-card-view d-none">
                    @forelse($appointments as $appointment)
                        <div class="mobile-card">
                            <div class="mobile-card-header">
                                {{ $appointment->appointment_ticket_id }}
                            </div>
                            
                            <div class="mobile-card-row">
                                <div class="mobile-card-label">Account</div>
                                <div class="mobile-card-value">{{ $appointment->account_number }}</div>
                            </div>
                            
                            <div class="mobile-card-row">
                                <div class="mobile-card-label">Type</div>
                                <div class="mobile-card-value">{{ $appointment->type->type_name ?? 'N/A' }}</div>
                            </div>
                            
                            <div class="mobile-card-row">
                                <div class="mobile-card-label">Status</div>
                                <div class="mobile-card-value">{!! $appointment->status_badge !!}</div>
                            </div>
                            
                            <div class="mobile-card-row">
                                <div class="mobile-card-label">Priority</div>
                                <div class="mobile-card-value">{!! $appointment->priority_badge !!}</div>
                            </div>
                            
                            <div class="mobile-card-row">
                                <div class="mobile-card-label">Scheduled</div>
                                <div class="mobile-card-value">
                                    @if($appointment->scheduled_date)
                                        {{ $appointment->scheduled_date->format('M d, Y') }}
                                        <br><small class="text-muted">{{ $appointment->scheduled_time ?? '' }}</small>
                                    @else
                                        Not Scheduled
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mobile-card-row">
                                <div class="mobile-card-label">Actions</div>
                                <div class="mobile-card-value">
                                    <div class="btn-group-responsive">
                                        <a href="{{ route('appointment.appointments.show', $appointment->id) }}" class="btn btn-outline-info btn-sm">
                                            <i class="bx bx-show me-1"></i> View
                                        </a>
                                        <a href="{{ route('appointment.appointments.edit', $appointment->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bx bx-edit me-1"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="table-empty-state">
                            <i class="bx bx-info-circle"></i>
                            <h5>No appointments found</h5>
                            <p>No appointments have been created yet.</p>
                            @can('view-create-appointment')
                            <a href="{{ route('appointment.appointments.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Create First Appointment
                            </a>
                            @endcan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Initialize Select2 for status dropdown with color indicators
        $('.status-select').select2({
            templateResult: formatStatusOption,
            templateSelection: formatStatusOption,
            escapeMarkup: function(m) { return m; }
        });
        
        // Format status options with color dots
        function formatStatusOption(state) {
            if (!state.id) return state.text;
            
            var $option = $(state.element);
            var color = $option.data('color') || '#3498db';
            
            return $('<span><span class="status-dot" style="background-color: ' + color + ';"></span>' + state.text + '</span>');
        }
    });
</script>
@endpush

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection
@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
$('#appointments-table').DataTable({
  responsive: true
});
});
</script>
@endsection

