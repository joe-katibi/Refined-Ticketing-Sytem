@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Escalation History')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Escalation History: #{{ $escalation->ticket_id }} (ID: {{ $escalation->id }})</h5>
                    <a href="{{ route('escalations.show', $escalation->id) }}" class="btn btn-outline-secondary btn-xs">
                        <i class="fas fa-arrow-left"></i> Back to Escalation
                    </a>
                </div>

                <div class="card-body">
                    <div class="escalation-details mb-4">
                        <h6>Escalation Details</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Title:</strong> {{ $escalation->subcategory->sub_category_name ?? 'N/A' }}</p>
                                <p class="mb-1">
                                  <strong>Status:</strong>
                                  <span class="badge
                                      {{ $escalation->status === 'resolved' ? 'bg-label-success' :
                                         ($escalation->status === 'closed' ? 'bg-label-secondary' : 'bg-label-primary') }}">
                                      {{ ucfirst($escalation->status) }}
                                  </span>
                              </p>

                            </div>
                            <div class="col-md-4">
                              <p class="mb-1">
                                <strong>Priority:</strong>
                                <span class="badge
                                    {{ $escalation->priority === 'high' ? 'bg-label-danger' :
                                       ($escalation->priority === 'medium' ? 'bg-label-warning' : 'bg-label-info') }}">
                                    {{ ucfirst($escalation->priority) }}
                                </span>
                            </p>

                                <p class="mb-1"><strong>Department:</strong> {{ $escalation->subDepartment->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Assigned To:</strong> {{ $escalation->assignedTo->name ?? 'Unassigned' }}</p>
                                <p class="mb-1"><strong>Created:</strong> {{ $escalation->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3">History Timeline</h6>
                    <div class="timeline">
                        @forelse($history as $record)
                            <div class="timeline-item mb-4">
                                <div class="timeline-badge {{ $record->getTimelineBadgeClass() }}">
                                    <i class="fas {{ $record->getTimelineIcon() }}"></i>
                                </div>
                                <div class="timeline-content card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-title mb-1">{{ $record->action_description ?? 'Action Performed' }}</h6>
                                            <small class="text-muted">{{ $record->created_at->diffForHumans() }}</small>
                                        </div>

                                        <div class="mb-2">
                                            @if($record->action_by)
                                                <span class="badge badge-xs bg-light text-dark">
                                                    <i class="fas fa-user"></i> {{ $record->actionBy->name }}
                                                </span>
                                            @endif

                                            @if($record->assigned_to)
                                                <span class="badge badge-xs bg-light text-dark ms-1">
                                                    <i class="fas fa-user-tag"></i> {{ $record->assignedTo->name }}
                                                </span>
                                            @endif

                                            @if($record->status)
                                                <span class="badge badge-xs bg-{{ $record->getStatusBadgeClass() }} ms-1">
                                                    {{ ucfirst($record->status) }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($record->description)
                                            <p class="card-text">{{ $record->description }}</p>
                                        @endif

                                        {{-- Dynamic Escalation Details --}}
                                        {{-- Use the history record data which contains the appointment details --}}

                                        {{-- OLT and Slot Information --}}
                                        @if($record->olt_id || $record->slot_id)
                                            <div class="alert alert-info p-2 mt-2">
                                                <small class="text-muted"><i class="fas fa-network-wired"></i> Network Information:</small>
                                                <div class="row mt-1">
                                                    @if($record->olt_id)
                                                        @php
                                                            $olt = \Modules\Outages\Models\Olt::find($record->olt_id);
                                                        @endphp
                                                        <div class="col-md-6">
                                                            <strong>OLT:</strong> 
                                                            @if($olt)
                                                                {{ $olt->name }} - {{ $olt->vendor }}
                                                                @if($olt->location)
                                                                    <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ $olt->location }}</small>
                                                                @endif
                                                            @else
                                                                ID: {{ $record->olt_id }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($record->slot_id)
                                                        @php
                                                            $slot = \Modules\Outages\Models\OltSlot::find($record->slot_id);
                                                        @endphp
                                                        <div class="col-md-6">
                                                            <strong>Slot:</strong> 
                                                            @if($slot)
                                                                Slot {{ $slot->slot_number }} ({{ $slot->slot_type }})
                                                            @else
                                                                ID: {{ $record->slot_id }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Escalation Type --}}
                                        @if($record->appointment_type_id)
                                            @php
                                                $appointmentTypes = [
                                                    1 => 'Support',
                                                    2 => 'Shifting', 
                                                    3 => 'Installation',
                                                    4 => 'WiFi Extender'
                                                ];
                                                $typeName = $appointmentTypes[$record->appointment_type_id] ?? 'Unknown';
                                            @endphp
                                            <div class="mb-2">
                                                <span class="badge badge-xs bg-label-primary">
                                                    <i class="fas fa-tag"></i> {{ $typeName }} Appointment
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Support Appointment Details --}}
                                        @if($record->support_date || $record->support_time || $record->support_address || $record->support_notes)
                                            <div class="alert alert-success p-2 mt-2">
                                                <small class="text-muted"><i class="fas fa-headset"></i> Support Appointment:</small>
                                                <div class="row mt-1">
                                                    @if($record->support_date || $record->support_time)
                                                        <div class="col-md-6">
                                                            <strong>Date & Time:</strong> 
                                                            @if($record->support_date)
                                                                {{ \Carbon\Carbon::parse($record->support_date)->format('M d, Y') }}
                                                            @endif
                                                            @if($record->support_time)
                                                                at {{ \Carbon\Carbon::parse($record->support_time)->format('H:i') }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($record->support_address)
                                                        <div class="col-md-6">
                                                            <strong>Address:</strong> {{ $record->support_address }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($record->support_notes)
                                                    <div class="mt-2">
                                                        <strong>Notes:</strong> {{ $record->support_notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Shifting Appointment Details --}}
                                        @if($record->shifting_date || $record->shifting_time || $record->shifting_address || $record->shifting_notes)
                                            <div class="alert alert-warning p-2 mt-2">
                                                <small class="text-muted"><i class="fas fa-exchange-alt"></i> Shifting Appointment:</small>
                                                <div class="row mt-1">
                                                    @if($record->shifting_date || $record->shifting_time)
                                                        <div class="col-md-6">
                                                            <strong>Date & Time:</strong> 
                                                            @if($record->shifting_date)
                                                                {{ \Carbon\Carbon::parse($record->shifting_date)->format('M d, Y') }}
                                                            @endif
                                                            @if($record->shifting_time)
                                                                at {{ \Carbon\Carbon::parse($record->shifting_time)->format('H:i') }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($record->shifting_address)
                                                        <div class="col-md-6">
                                                            <strong>New Address:</strong> {{ $record->shifting_address }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($record->shifting_notes)
                                                    <div class="mt-2">
                                                        <strong>Notes:</strong> {{ $record->shifting_notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Installation Appointment Details --}}
                                        @if($record->installation_date || $record->installation_time || $record->installation_address || $record->installation_notes)
                                            <div class="alert alert-primary p-2 mt-2">
                                                <small class="text-muted"><i class="fas fa-tools"></i> Installation Appointment:</small>
                                                <div class="row mt-1">
                                                    @if($record->installation_date || $record->installation_time)
                                                        <div class="col-md-6">
                                                            <strong>Date & Time:</strong> 
                                                            @if($record->installation_date)
                                                                {{ \Carbon\Carbon::parse($record->installation_date)->format('M d, Y') }}
                                                            @endif
                                                            @if($record->installation_time)
                                                                at {{ \Carbon\Carbon::parse($record->installation_time)->format('H:i') }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($record->installation_address)
                                                        <div class="col-md-6">
                                                            <strong>Address:</strong> {{ $record->installation_address }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($record->installation_notes)
                                                    <div class="mt-2">
                                                        <strong>Notes:</strong> {{ $record->installation_notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- WiFi Extender Appointment Details --}}
                                        @if($record->wifi_extender_date || $record->wifi_extender_time || $record->wifi_extender_address || $record->wifi_extender_notes)
                                            <div class="alert alert-info p-2 mt-2">
                                                <small class="text-muted"><i class="fas fa-wifi"></i> WiFi Extender Appointment:</small>
                                                <div class="row mt-1">
                                                    @if($record->wifi_extender_date || $record->wifi_extender_time)
                                                        <div class="col-md-6">
                                                            <strong>Date & Time:</strong> 
                                                            @if($record->wifi_extender_date)
                                                                {{ \Carbon\Carbon::parse($record->wifi_extender_date)->format('M d, Y') }}
                                                            @endif
                                                            @if($record->wifi_extender_time)
                                                                at {{ \Carbon\Carbon::parse($record->wifi_extender_time)->format('H:i') }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($record->wifi_extender_address)
                                                        <div class="col-md-6">
                                                            <strong>Address:</strong> {{ $record->wifi_extender_address }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($record->wifi_extender_notes)
                                                    <div class="mt-2">
                                                        <strong>Notes:</strong> {{ $record->wifi_extender_notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if($record->internal_notes)
                                            <div class="alert alert-light p-2 mt-2 mb-0">
                                                <small class="text-muted">Internal Notes:</small>
                                                <p class="mb-0">{{ $record->internal_notes }}</p>
                                            </div>
                                        @endif

                                        @if($record->time_spent_minutes)
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="far fa-clock"></i> Time spent: {{ $record->getFormattedTimeSpent() }}
                                                </small>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt"></i> Created on: {{ $record->created_at->format('Y-m-d H:i:s') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No history records found for this escalation.</p>
                            </div>
                        @endforelse
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
    padding-left: 30px;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 15px;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-badge {
    position: absolute;
    left: -30px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    z-index: 1;
}

.timeline-content {
    position: relative;
    margin-left: 30px;
    border-left: 3px solid #e9ecef;
}

.timeline-content:before {
    content: '';
    position: absolute;
    top: 16px;
    left: -3px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #e9ecef;
}

/* Status specific colors */
.badge-status-assigned { background-color: #3498db; }
.badge-status-in_progress { background-color: #f39c12; }
.badge-status-resolved { background-color: #2ecc71; }
.badge-status-closed { background-color: #95a5a6; }
.badge-status-reopened { background-color: #9b59b6; }

/* Timeline badge colors */
.timeline-badge.primary { background-color: #3498db; }
.timeline-badge.success { background-color: #2ecc71; }
.timeline-badge.warning { background-color: #f39c12; }
.timeline-badge.danger { background-color: #e74c3c; }
.timeline-badge.info { background-color: #3498db; }
.timeline-badge.secondary { background-color: #95a5a6; }
</style>
@endpush

