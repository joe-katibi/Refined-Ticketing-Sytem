@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Outage Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">
                    <span class="text-muted fw-light">Outages /</span> {{ $outage->ticket_number }}
                </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('outages.edit', $outage) }}" class="btn btn-primary btn-xs">
                        <i class="bx bx-edit me-1"></i> Edit Outage
                    </a>
                    <button type="button" class="btn btn-warning btn-xs" data-bs-toggle="modal" data-bs-target="#statusModal">
                        <i class="bx bx-refresh me-1"></i> Update Status
                    </button>
                    <button type="button" class="btn btn-info btn-xs" data-bs-toggle="modal" data-bs-target="#progressModal">
                        <i class="bx bx-plus me-1"></i> Add Progress
                    </button>
                    <a href="{{ route('outages.activity', $outage) }}" class="btn btn-success btn-xs">
                        <i class="bx bx-history me-1"></i> Activity History
                    </a>
                    <a href="{{ route('outages.index') }}" class="btn btn-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Left Column - Main Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-error-alt me-2"></i>Outage Information
                    </h5>
                    @php
                        $typeClass = match($outage->ticket_type) {
                            'emergency' => 'bg-danger',
                            'planned_maintenance' => 'bg-info',
                            'regular' => 'bg-primary',
                            default => 'bg-secondary'
                        };
                        $typeLabel = match($outage->ticket_type) {
                            'emergency' => 'Emergency',
                            'planned_maintenance' => 'Planned Maintenance',
                            'regular' => 'Regular Outage',
                            default => 'Unknown'
                        };
                    @endphp
                    <span class="badge {{ $typeClass }} badge-lg">{{ $typeLabel }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ticket Number</label>
                            <p class="text-muted mb-3">{{ $outage->ticket_number }}</p>

                            <label class="form-label fw-semibold">Start Date & Time</label>
                            <p class="text-muted mb-3">{{ \Carbon\Carbon::parse($outage->start_date . ' ' . $outage->start_time)->format('M d, Y H:i') }}</p>

                            <label class="form-label fw-semibold">Impact</label>
                            <p class="text-muted mb-3">{{ $outage->impact }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Title</label>
                            <p class="text-muted mb-3">{{ $outage->title }}</p>

                            <label class="form-label fw-semibold">Urgency</label>
                            <p class="text-muted mb-3">{{ $outage->urgency }}</p>

                            <label class="form-label fw-semibold">Priority</label>
                            @php
                                $priorityClass = match($outage->priority) {
                                    'Critical' => 'bg-danger',
                                    'High' => 'bg-warning',
                                    'Medium' => 'bg-info',
                                    'Low' => 'bg-success',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <p><span class="badge {{ $priorityClass }}">{{ $outage->priority }}</span></p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <p class="text-muted mb-3">{{ $outage->description }}</p>
                        </div>
                        @if($outage->resolution_notes)
                        <div class="col-12">
                            <label class="form-label fw-semibold">Resolution Notes</label>
                            <p class="text-muted mb-3">{{ $outage->resolution_notes }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Impacted Areas</label>
                            <div class="mb-3">
                                @if($outage->affectedAreas && $outage->affectedAreas->count() > 0)
                                    @foreach($outage->affectedAreas as $area)
                                        <span class="badge badge-xs bg-secondary me-1">{{ $area->area_name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Impacted Services</label>
                            <div class="mb-3">
                                @if($outage->affectedServices && $outage->affectedServices->count() > 0)
                                    @foreach($outage->affectedServices as $service)
                                        <span class="badge badge-xs bg-secondary me-1">{{ $service->service_name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Updates -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-history me-2"></i>Progress Updates ({{ $outage->progress->count() ?? 0 }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($outage->progress && $outage->progress->count() > 0)
                        <div class="timeline">
                            @foreach($outage->progress as $progress)
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-{{ $progress->is_major_update ? 'success' : 'info' }} rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bx bx-{{ $progress->is_major_update ? 'star' : 'comment' }} text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $progress->user->name ?? 'System' }}</h6>
                                                <small class="text-muted">{{ $progress->created_at->format('M d, Y H:i') }}</small>
                                                @if($progress->is_major_update)
                                                    <span class="badge badge-xs bg-success ms-2">Major Update</span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="mb-1">{{ $progress->notes }}</p>
                                        @if($progress->action_taken)
                                            <p class="mb-1"><strong>Action Taken:</strong> {{ $progress->action_taken }}</p>
                                        @endif
                                        @if($progress->next_steps)
                                            <p class="mb-0"><strong>Next Steps:</strong> {{ $progress->next_steps }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-history display-4 text-muted mb-3"></i>
                            <p class="text-muted mb-3">No progress updates yet.</p>
                            <button type="button" class="btn btn-success btn-xs" data-bs-toggle="modal" data-bs-target="#progressModal">
                                <i class="bx bx-plus me-1"></i> Add First Progress Update
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar Information -->
        <div class="col-lg-4">
            <!-- Status Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-info-circle me-2"></i>Status Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Current Status</label>
                            @php
                                $statusClass = match($outage->status) {
                                    'Reported' => 'bg-warning',
                                    'In Progress' => 'bg-info',
                                    'Resolved' => 'bg-success',
                                    'Closed' => 'bg-secondary',
                                    default => 'bg-primary'
                                };
                            @endphp
                            <p><span class="badge {{ $statusClass }}">{{ $outage->status }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-cog me-2"></i>Technical Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">OLT ID</label>
                            <p class="text-muted mb-2">{{ $outage->olt?->name ?? 'Not specified' }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Slot ID</label>
                            <p class="text-muted mb-2">{{ $outage->oltSlot ? 'Slot ' . $outage->oltSlot->slot_number . ' (' . $outage->oltSlot->slot_type . ')' : 'Not specified' }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Port ID</label>
                            <p class="text-muted mb-2">{{ $outage->ponPort ? 'Port ' . $outage->ponPort->pon_port_number . ' (' . $outage->ponPort->pon_port_type . ')' : 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-user-check me-2"></i>Assignment
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Assigned Team Type</label>
                            <p class="text-muted mb-2">
                                @if($outage->assignedTeam)
                                    {{ $outage->assignedTeam->type_name }}
                                @else
                                    Not assigned
                                @endif
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Assigned Sub Team Type</label>
                            <p class="text-muted mb-2">
                                @if($outage->assignedSubTeamType)
                                    {{ $outage->assignedSubTeamType->sub_type_name }}
                                @else
                                    Not assigned
                                @endif
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Assigned To</label>
                            <p class="text-muted mb-2">{{ $outage->assignee?->name ?? 'Not assigned' }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Reported By</label>
                            <p class="text-muted mb-2">{{ $outage->reporter?->name ?? 'System' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLA Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-time me-2"></i>SLA Status
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $startTime = \Carbon\Carbon::parse($outage->start_date . ' ' . $outage->start_time);
                        $now = \Carbon\Carbon::now();
                        $duration = $startTime->diffInMinutes($now);
                        $withinSLA = $duration <= 240; // 4 hours SLA
                    @endphp
                    <div class="alert alert-{{ $withinSLA ? 'success' : 'danger' }}" role="alert">
                        <i class="bx bx-{{ $withinSLA ? 'check-circle' : 'x-circle' }} me-2"></i>
                        {{ $withinSLA ? 'Within SLA' : 'SLA Breached' }}
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Duration</label>
                            <p class="text-muted mb-0">{{ $startTime->diffForHumans($now, true) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Attachments -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-paperclip me-2"></i>Attachments ({{ $outage->attachments->count() ?? 0 }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($outage->attachments && $outage->attachments->count() > 0)
                        <div class="row g-3">
                            @foreach($outage->attachments as $attachment)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-sm">
                                                <span class="avatar-initial rounded bg-label-secondary">
                                                    <i class="bx bx-file"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="{{ $attachment->file_url }}" target="_blank" class="text-decoration-none">
                                                    {{ $attachment->file_name }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                {{ $attachment->getHumanReadableSize() ?? 'Unknown size' }} •
                                                {{ $attachment->uploader->name ?? 'Unknown' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="bx bx-file fs-2"></i>
                                </span>
                            </div>
                            <h6 class="mb-1">No Attachments</h6>
                            <p class="text-muted mb-0">No files have been attached to this outage.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('outages.updateStatus', $outage) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">New Status</label>
                        <select class="form-select" id="status" name="status" required>
                            @php
                                $statusOptions = \Modules\Outages\Models\Outage::getStatusOptions();
                            @endphp
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $outage->status == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Add notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-xs">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Progress Update Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressModalLabel">Add Progress Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('outages.progress.store', $outage) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="progress_notes" class="form-label">Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="progress_notes" name="notes" rows="4"
                                  placeholder="Describe the current progress..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="action_taken" class="form-label">Action Taken</label>
                        <textarea class="form-control" id="action_taken" name="action_taken" rows="3"
                                  placeholder="What actions were taken?"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="next_steps" class="form-label">Next Steps</label>
                        <textarea class="form-control" id="next_steps" name="next_steps" rows="3"
                                  placeholder="What are the next steps?"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_major_update" name="is_major_update" value="1">
                        <label class="form-check-label" for="is_major_update">
                            Mark as major update
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-xs">Add Progress</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('styles')
<style>
.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}
.attachment-item {
    padding: 8px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // JavaScript functionality can be added here when needed
    console.log('Outage show page loaded');
});
</script>
@endpush
