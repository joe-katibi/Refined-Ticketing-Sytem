@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'My Appointments')

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .appointment-card {
        transition: transform 0.2s;
    }
    .appointment-card:hover {
        transform: translateY(-2px);
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
                    <div>
                        <h3 class="card-title mb-0">
                            <i class="fas fa-user-calendar me-2 text-primary"></i>
                            My Appointments
                        </h3>
                        <p class="text-muted mb-0">Appointments assigned to you through Sub Teams or Assigned Teams</p>
                    </div>
                    <div>
                        <span class="badge badge-xs bg-primary fs-6">{{ $myAppointments->count() }} Total</span>
                    </div>
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

                    @if($myAppointments->count() > 0)
                        <div class="row">
                            @foreach($myAppointments as $appointment)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card appointment-card border-left-primary h-100">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    {{ $appointment->type->type_name ?? 'N/A' }}
                                                </h6>
                                                @if($appointment->status === 'Rescheduled')
                                                    <span class="badge badge-xs bg-warning">{{ $appointment->status }}</span>
                                                @elseif($appointment->status === 'Scheduled-Assigned Team')
                                                    <span class="badge badge-xs bg-info">{{ $appointment->status }}</span>
                                                @elseif($appointment->status === 'Support-Post-Install')
                                                    <span class="badge badge-xs bg-success">{{ $appointment->status }}</span>
                                                @else
                                                    <span class="badge badge-xs bg-secondary">{{ $appointment->status }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <small class="text-muted">Sub Type:</small>
                                                <br>
                                                <strong>{{ $appointment->subType->sub_type_name ?? 'N/A' }}</strong>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">Account Number:</small>
                                                <br>
                                                <strong>{{ $appointment->account_number ?? 'N/A' }}</strong>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted">Team Assignment:</small>
                                                <br>
                                                @if($appointment->subTeamType)
                                                    <span class="badge badge-xs bg-primary">Sub Team: {{ $appointment->subTeamType->sub_type_name }}</span>
                                                @endif
                                                @if($appointment->assignedTeam)
                                                    <span class="badge badge-xs bg-success">Assigned: {{ $appointment->assignedTeam->team_name }}</span>
                                                @endif
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted">Created By:</small>
                                                <br>
                                                <span>{{ $appointment->creator->name ?? 'System' }}</span>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted">Created:</small>
                                                <br>
                                                <span>{{ $appointment->created_at->format('M d, Y H:i') }}</span>
                                            </div>

                                            @if($appointment->appointment_date)
                                            <div class="mb-2">
                                                <small class="text-muted">Appointment Date:</small>
                                                <br>
                                                <span class="text-primary">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                                                    @if($appointment->appointment_time)
                                                        {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                                                    @endif
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('appointment.appointments.show', $appointment->id) }}" 
                                                   class="btn btn-outline-info btn-xs" 
                                                   data-bs-toggle="tooltip" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('appointment.appointments.edit_assigned', $appointment->id) }}" 
                                                   class="btn btn-primary btn-xs" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit Appointment">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-calendar-times fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No Appointments Found</h5>
                            <p class="text-muted">You don't have any appointments assigned to you at the moment.</p>
                            <p class="text-muted">
                                <small>
                                    Appointments are assigned through Sub Teams or Assigned Teams.<br>
                                    Contact your administrator if you believe this is incorrect.
                                </small>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

