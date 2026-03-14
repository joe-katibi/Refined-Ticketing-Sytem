@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'View Appointment #' . $appointment->appointment_ticket_id)



@push('style')
<style>
    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }
    .timeline:before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
        padding-left: 1.5rem;
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -0.5rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background-color: #4361ee;
    }
    .timeline-item.completed:before {
        background-color: #198754;
    }
    .timeline-item.cancelled:before {
        background-color: #dc3545;
    }
    .timeline-item.rescheduled:before {
        background-color: #ffc107;
    }
    .card-header .btn {
        margin-left: 0.5rem;
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
                <h3 class="card-title">
                    Appointment #{{ $appointment->appointment_ticket_id }}
                    {!! $appointment->status_badge !!}
                    {!! $appointment->priority_badge !!}
                </h3>
                <div class="card-tools">
                    <a href="{{ route('appointment.appointments.edit', $appointment->id) }}" class="btn btn-primary btn-xs">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('appointment.appointments.index') }}" class="btn btn-secondary btn-xs">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4 h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Appointment Details</h5>
                            </div>
                            <div class="card-body" style="min-height: 550px;">
                                <dl class="row mb-0">
                                    <dt class="col-sm-3">Account Number</dt>
                                    <dd class="col-sm-9 mb-3">{{ $appointment->account_number }}</dd>

                                    <dt class="col-sm-3">Type</dt>
                                    <dd class="col-sm-9 mb-3" style="white-space: pre-line;">
                                        {{ $appointment->type->type_name ?? 'N/A' }}
                                        @if($appointment->subType)
                                            <span class="text-muted">({{ $appointment->subType->sub_type_name }})</span>
                                        @endif
                                    </dd>

                                    <dt class="col-sm-3">Scheduled</dt>
                                    <dd class="col-sm-9 mb-3">
                                        @if($appointment->scheduled_date)
                                            {{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('M d, Y') }} at {{ $appointment->scheduled_time ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </dd>

                                    <dt class="col-sm-3">Location</dt>
                                    <dd class="col-sm-9 mb-3">
                                        {{ $appointment->appointment_location }}
                                        @if($appointment->appointment_venue)
                                            <div class="text-muted small mt-1">{{ $appointment->appointment_venue }}</div>
                                        @endif
                                    </dd>

                                    @if($appointment->status === 'Scheduled-Closed')
                                    <dt class="col-sm-3">Completed</dt>
                                    <dd class="col-sm-9">
                                        {{ $appointment->completed_date->format('M d, Y') }} at {{ $appointment->completed_time }}
                                        @if($appointment->closer)
                                            <div class="text-muted small">by {{ $appointment->closer->name }}</div>
                                        @endif
                                    </dd>
                                @endif

                                @if($appointment->escalation_ticket_id)
                                    <dt class="col-sm-3">Escalation Ticket</dt>
                                    <dd class="col-sm-9">{{ $appointment->escalation_ticket_id }}</dd>
                                @endif

                                @if($appointment->outage_ticket_id)
                                    <dt class="col-sm-3">Outage Ticket</dt>
                                    <dd class="col-sm-9">{{ $appointment->outage_ticket_id }}</dd>
                                @endif

                                    <dt class="col-sm-3">Description</dt>
                                    <dd class="col-sm-9">
                                        @if($appointment->description_notes)
                                            <div class="description-content" style="white-space: pre-line; line-height: 1.6;">
                                                {!! nl2br(e($appointment->description_notes)) !!}
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </dd>


                                </dl>
                            </div>
                        </div>

                        @if($appointment->escalation_notes || $appointment->closing_reason)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Additional Information</h5>
                                </div>
                                <div class="card-body">
                                    @if($appointment->escalation_notes)
                                        <h6>Escalation Notes</h6>
                                        <p class="mb-4">{{ $appointment->escalation_notes }}</p>
                                    @endif

                                    @if($appointment->closing_reason)
                                        <h6>Closing Reason</h6>
                                        <p class="mb-0">{{ $appointment->closing_reason }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <!-- Status History Card -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Status History</h5>
                            </div>
                            <div class="card-body">
                                @if($statusHistory && $statusHistory->count() > 0)
                                    <div class="timeline">
                                        @foreach($statusHistory as $history)
                                            <div class="timeline-item">
                                                <div class="d-flex align-items-center mb-2">
                                                    @if($history->newStatusRecord)
                                                        <span class="badge {{ $history->newStatusRecord->badge_class ?? 'bg-info' }}" 
                                                              style="background-color: {{ $history->newStatusRecord->color ?? '#3498db' }} !important;">
                                                            {{ $history->newStatusRecord->display_name ?? $history->new_status }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info">{{ $history->new_status }}</span>
                                                    @endif
                                                </div>
                                                
                                                @if($history->previous_status)
                                                    <p class="small mb-1">
                                                        Changed from 
                                                        @if($history->previousStatusRecord)
                                                            <span class="badge {{ $history->previousStatusRecord->badge_class ?? 'bg-secondary' }}" 
                                                                  style="background-color: {{ $history->previousStatusRecord->color ?? '#6c757d' }} !important;">
                                                                {{ $history->previousStatusRecord->display_name ?? $history->previous_status }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $history->previous_status }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                
                                                <p class="text-muted small mb-1">
                                                    {{ \Carbon\Carbon::parse($history->created_at)->format('M d, Y \a\t h:i A') }}
                                                </p>
                                                
                                                <p class="small mb-0">
                                                    By {{ $history->user->name ?? 'System' }}
                                                </p>
                                                
                                                @if($history->notes)
                                                    <p class="small text-muted mt-2 mb-0">
                                                        <i class="fas fa-comment-alt me-1"></i> {{ $history->notes }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No status history available</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Timeline</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <h6 class="mb-1">Appointment Created</h6>
                                        <p class="text-muted small mb-1">
                                            {{ $appointment->created_at->format('M d, Y \a\t h:i A') }}
                                        </p>
                                        <p class="small mb-0">
                                            Created by {{ $appointment->creator->name ?? 'System' }}
                                        </p>
                                    </div>

                                    @if($appointment->edited_by && $appointment->updated_at)
                                        <div class="timeline-item">
                                            <h6 class="mb-1">Appointment Updated</h6>
                                            <p class="text-muted small mb-1">
                                                {{ $appointment->updated_at ? \Carbon\Carbon::parse($appointment->updated_at)->format('M d, Y \a\t h:i A') : 'N/A' }}
                                            </p>
                                            <p class="small mb-0">
                                                Updated by {{ $appointment->editor->name ?? 'System' }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($appointment->status === 'Scheduled-Closed')
                                        <div class="timeline-item completed">
                                            <h6 class="mb-1">Appointment Completed</h6>
                                            <p class="text-muted small mb-1">
                                                {{ $appointment->closed_at ? \Carbon\Carbon::parse($appointment->closed_at)->format('M d, Y \a\t h:i A') : 'N/A' }}
                                            </p>
                                            <p class="small mb-0">
                                                Closed by {{ $appointment->closer->name ?? 'System' }}
                                            </p>
                                        </div>
                                    @elseif($appointment->status === 'Cancelled')
                                        <div class="timeline-item cancelled">
                                            <h6 class="mb-1">Appointment Cancelled</h6>
                                            <p class="text-muted small mb-1">
                                                {{ $appointment->closed_at ? \Carbon\Carbon::parse($appointment->closed_at)->format('M d, Y \a\t h:i A') : 'N/A' }}
                                            </p>
                                            <p class="small mb-0">
                                                Cancelled by {{ $appointment->closer->name ?? 'System' }}
                                            </p>
                                        </div>
                                    @elseif($appointment->status === 'Rescheduled')
                                        <div class="timeline-item rescheduled">
                                            <h6 class="mb-1">Appointment Rescheduled</h6>
                                            <p class="text-muted small mb-1">
                                                {{ $appointment->updated_at ? \Carbon\Carbon::parse($appointment->updated_at)->format('M d, Y \a\t h:i A') : 'N/A' }}
                                            </p>
                                            <p class="small mb-0">
                                                Rescheduled by {{ $appointment->editor->name ?? 'System' }}
                                            </p>
                                            @if($appointment->scheduled_date)
                                                <p class="small mt-2 mb-0">
                                                    New Date: {{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('M d, Y') }} at {{ $appointment->scheduled_time ?? 'N/A' }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Team Information</h5>
                            </div>
                            <div class="card-body">
                                <dl class="mb-0">
                                    <dt>Team Type</dt>
                                    <dd class="mb-3">
                                        {{ $appointment->teamType->type_name ?? 'Not Specified' }}
                                    </dd>

                                    <dt>Sub Team Type</dt>
                                    <dd class="mb-3">
                                        {{ $appointment->subTeamType->sub_type_name ?? 'Not Specified' }}
                                    </dd>

                                    <dt>Assigned Team</dt>
                                    <dd class="mb-3">
                                        {{ $appointment->assignedTeam->name ?? 'Not Assigned' }}
                                    </dd>

                                    @if($appointment->escalated_team_id)
                                        <dt>Escalated To</dt>
                                        <dd class="mb-3">
                                            {{ $appointment->escalatedTeam->name }}
                                            @if($appointment->escalation_reason)
                                                <div class="text-muted small">
                                                    Reason: {{ $appointment->escalation_reason }}
                                                </div>
                                            @endif
                                        </dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

