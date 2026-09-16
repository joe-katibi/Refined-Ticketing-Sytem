@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Appointment History')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Appointment History: #{{ $appointment->appointment_ticket_id ?? 'N/A' }}</h5>
                    <a href="{{ route('appointment.appointments.show', $appointment->id) }}" class="btn btn-outline-secondary btn-xs">
                        <i class="fas fa-arrow-left"></i> Back to Appointment
                    </a>
                </div>

                <div class="card-body">
                    <div class="appointment-details mb-4">
                        <h6>Appointment Details</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Account Number:</strong> {{ $appointment->account_number ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Type:</strong> {{ $appointment->type->type_name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Sub Type:</strong> {{ $appointment->subType->sub_type_name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1">
                                    <strong>Status:</strong>
                                    <span class="badge badge-xs bg-label-{{
                                        $appointment->status === 'completed' ? 'success' :
                                        ($appointment->status === 'in_progress' ? 'primary' : 'warning')
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                    </span>
                                </p>
                                <p class="mb-1">
                                    <strong>Priority:</strong>
                                    <span class="badge badge-xs bg-label-{{
                                        $appointment->priority === 'high' ? 'danger' :
                                        ($appointment->priority === 'medium' ? 'warning' : 'primary')
                                    }}">
                                        {{ ucfirst($appointment->priority) }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Scheduled Date:</strong> {{ $appointment->scheduled_date ? \Carbon\Carbon::parse($appointment->scheduled_date)->format('Y-m-d') : 'N/A' }}</p>
                                <p class="mb-1"><strong>Created By:</strong> {{ $appointment->createdBy->name ?? 'System' }}</p>
                            </div>
                        </div>
                    </div>

                    <h5 class="my-4">History Timeline</h5>

                    <div class="timeline">

                        @forelse($histories as $history)
                            <div class="timeline-item mb-4">
                                <div class="timeline-badge {{
                                    str_contains(strtolower($history->action_description ?? ''), 'created') ? 'bg-label-success' :
                                    (str_contains(strtolower($history->action_description ?? ''), 'updated') || str_contains(strtolower($history->action_description ?? ''), 'edit') ? 'bg-label-primary' : 'bg-label-warning')
                                }}">
                                    <i class="fas {{ $history->getTimelineIcon() }}"></i>
                                </div>
                                <div class="timeline-content card">
                                  <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="card-title mb-1">{{ $history->action_description ?? 'Action Performed' }}</h6>
                                        <small class="text-muted">{{ $history->created_at->diffForHumans() }}</small>
                                    </div>

                                    <div class="mb-2">
                                        @if($history->action_by)
                                            <span class="badge badge-xs bg-light text-dark">
                                                <i class="fas fa-user"></i> {{ $history->actionBy->name ?? 'System' }}
                                            </span>
                                        @endif

                                        @if($history->assigned_to)
                                            <span class="badge badge-xs bg-light text-dark ms-1">
                                                <i class="fas fa-user-tag"></i> {{ $history->assignedUser->name ?? 'N/A' }}
                                            </span>
                                        @endif

                                        @if($history->status)
                                            <span class="badge badge-xs bg-{{ $history->getStatusBadgeClass() }} ms-1">
                                                {{ ucfirst($history->status) }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(!empty($history->changes))
                                        @php $changes = json_decode($history->changes, true); @endphp
                                        @if(is_array($changes))
                                            <div class="changes-container">
                                                @foreach($changes as $field => $value)
                                                    <div class="change-item">
                                                        <span class="field-name">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                        @if(is_array($value))
                                                            @if(isset($value['from']) && isset($value['to']))
                                                                <span class="change-from">{{ is_array($value['from']) ? json_encode($value['from']) : $value['from'] }}</span>
                                                                <i class="fas fa-arrow-right mx-2"></i>
                                                                <span class="change-to">{{ is_array($value['to']) ? json_encode($value['to']) : $value['to'] }}</span>
                                                            @else
                                                                {{ json_encode($value) }}
                                                            @endif
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="card-text">{{ $history->changes }}</p>
                                        @endif
                                    @elseif($history->description)
                                        <p class="card-text">{{ $history->description }}</p>
                                    @else
                                        <p class="text-muted mb-0">No details available</p>
                                    @endif
                                    
                                    @if($history->internal_notes)
                                        <div class="alert alert-light p-2 mt-2 mb-0">
                                            <small class="text-muted">Internal Notes:</small>
                                            <p class="mb-0">{{ $history->internal_notes }}</p>
                                        </div>
                                    @endif
                                    
                                    @if($history->time_spent_minutes)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="far fa-clock"></i> Time spent: {{ $history->getFormattedTimeSpent() }}
                                            </small>
                                        </div>
                                    @endif
                                    
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt"></i> Created on: {{ $history->created_at->format('Y-m-d H:i:s') }}
                                        </small>
                                    </div>
                                  </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="mb-0">No history records found for this appointment.</p>
                            </div>
                        @endforelse
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 50px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
        border-left: 2px solid #e9ecef;
        padding-left: 30px;
    }

    .timeline-item:last-child {
        border-left-color: transparent;
    }

    .timeline-badge {
        position: absolute;
        left: -15px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }

    .timeline-content {
        background-color: #f8f9fa;
        border-radius: 6px;
        padding: 15px;
        position: relative;
    }

    .timeline-content:after {
        content: '';
        position: absolute;
        border-style: solid;
        border-width: 10px 10px 10px 0;
        border-color: transparent #f8f9fa transparent transparent;
        display: block;
        width: 0;
        z-index: 1;
        left: -10px;
        top: 15px;
    }

    .changes-container {
        background-color: #fff;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
        border: 1px solid #e9ecef;
    }

    .change-item {
        padding: 5px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .change-item:last-child {
        border-bottom: none;
    }

    .field-name {
        font-weight: 600;
        margin-right: 5px;
    }

    .change-from {
        text-decoration: line-through;
        color: #dc3545;
        margin-right: 5px;
    }

    .change-to {
        color: #28a745;
        margin-left: 5px;
    }

    .dark-style .timeline-item {
        border-left-color: #444564;
    }

    .dark-style .timeline-content {
        background-color: #323249;
    }

    .dark-style .timeline-content:after {
        border-color: transparent #323249 transparent transparent;
    }

    .dark-style .changes-container {
        background-color: #2b2c40;
        border-color: #444564;
    }

    .dark-style .change-item {
        border-bottom-color: #444564;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Enable tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush

