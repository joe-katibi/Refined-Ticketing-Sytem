@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Ticket Details')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Ticket Details - {{ $outageTicket->ticket_number }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('outages.index') }}">Outages</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('outage-tickets.index') }}">Tickets</a></li>
                        <li class="breadcrumb-item active">{{ $outageTicket->ticket_number }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-12">
                    <a href="{{ route('outage-tickets.edit', $outageTicket) }}" class="btn btn-primary btn-xs">
                        <i class="fas fa-edit"></i> Edit Ticket
                    </a>
                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#progressModal">
                        <i class="fas fa-plus"></i> Add Progress
                    </button>
                    <a href="{{ route('outages.show', $outageTicket->outage) }}" class="btn btn-warning btn-xs">
                        <i class="fas fa-exclamation-triangle"></i> View Outage
                    </a>
                    <a href="{{ route('outage-tickets.index') }}" class="btn btn-secondary btn-xs">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Ticket Information -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-ticket-alt mr-1"></i>
                                Ticket Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Title:</strong>
                                    <p class="text-muted">{{ $outageTicket->title }}</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong>
                                    <p>
                                        <span class="badge badge-{{ $outageTicket->status == 'Resolved' ? 'success' : ($outageTicket->status == 'In Progress' ? 'warning' : 'secondary') }} badge-lg">
                                            {{ $outageTicket->status }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Priority:</strong>
                                    <p>
                                        <span class="badge badge-{{ $outageTicket->priority == 'Critical' ? 'danger' : ($outageTicket->priority == 'High' ? 'warning' : 'info') }} badge-lg">
                                            {{ $outageTicket->priority }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Impact:</strong>
                                    <p>
                                        <span class="badge badge-{{ $outageTicket->impact == 'Critical' ? 'danger' : ($outageTicket->impact == 'High' ? 'warning' : 'info') }}">
                                            {{ $outageTicket->impact }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Urgency:</strong>
                                    <p>
                                        <span class="badge badge-{{ $outageTicket->urgency == 'Critical' ? 'danger' : ($outageTicket->urgency == 'High' ? 'warning' : 'info') }}">
                                            {{ $outageTicket->urgency }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Calculated Priority:</strong>
                                    <p>
                                        <span class="badge badge-{{ $outageTicket->getCalculatedPriority() == 'Critical' ? 'danger' : 'warning' }}">
                                            {{ $outageTicket->getCalculatedPriority() }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <strong>Description:</strong>
                                    <p class="text-muted">{{ $outageTicket->description ?: 'No description provided' }}</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Start Time:</strong>
                                    <p class="text-muted">{{ $outageTicket->start_time->format('M d, Y H:i A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>End Time:</strong>
                                    <p class="text-muted">{{ $outageTicket->end_time ? $outageTicket->end_time->format('M d, Y H:i A') : 'Not resolved yet' }}</p>
                                </div>
                            </div>

                            @if($outageTicket->resolution || $outageTicket->resolution_notes)
                            <div class="row">
                                @if($outageTicket->resolution)
                                <div class="col-md-6">
                                    <strong>Resolution:</strong>
                                    <p class="text-muted">{{ $outageTicket->resolution }}</p>
                                </div>
                                @endif
                                @if($outageTicket->resolution_notes)
                                <div class="col-md-6">
                                    <strong>Resolution Notes:</strong>
                                    <p class="text-muted">{{ $outageTicket->resolution_notes }}</p>
                                </div>
                                @endif
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-md-12">
                                    <strong>Related Outage:</strong>
                                    <p>
                                        <a href="{{ route('outages.show', $outageTicket->outage) }}" class="btn btn-outline-primary btn-xs">
                                            {{ $outageTicket->outage->ticket_number }} - {{ $outageTicket->outage->title }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Updates -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Progress Updates ({{ $outageTicket->progress->count() }})
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($outageTicket->progress->count() > 0)
                                <div class="timeline">
                                    @foreach($outageTicket->progress as $progress)
                                        <div class="time-label">
                                            <span class="bg-{{ $progress->is_major_update ? 'success' : 'info' }}">
                                                {{ $progress->created_at->format('M d, Y') }}
                                            </span>
                                        </div>
                                        <div>
                                            <i class="fas fa-{{ $progress->is_major_update ? 'star' : 'comment' }} bg-{{ $progress->is_major_update ? 'yellow' : 'blue' }}"></i>
                                            <div class="timeline-item">
                                                <span class="time">
                                                    <i class="fas fa-clock"></i> {{ $progress->created_at->format('H:i A') }}
                                                </span>
                                                <h3 class="timeline-header">
                                                    <strong>{{ $progress->user->name }}</strong>
                                                    @if($progress->is_major_update)
                                                        <span class="badge badge-warning">Major Update</span>
                                                    @endif
                                                </h3>
                                                <div class="timeline-body">
                                                    <p><strong>Status:</strong> {{ $progress->status }}</p>
                                                    <p><strong>Notes:</strong> {{ $progress->notes }}</p>
                                                    @if($progress->action_taken)
                                                        <p><strong>Action Taken:</strong> {{ $progress->action_taken }}</p>
                                                    @endif
                                                    @if($progress->next_steps)
                                                        <p><strong>Next Steps:</strong> {{ $progress->next_steps }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div>
                                        <i class="fas fa-clock bg-gray"></i>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted text-center">No progress updates yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Assignment Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-1"></i>
                                Assignment
                            </h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Assigned Team:</strong><br>
                            {{ $outageTicket->assignedTeam->name ?? 'Not assigned' }}</p>

                            <p><strong>Assigned To:</strong><br>
                            {{ $outageTicket->assignee->name ?? 'Not assigned' }}</p>

                            <p><strong>Reported By:</strong><br>
                            {{ $outageTicket->reporter->name }}</p>
                        </div>
                    </div>

                    <!-- SLA Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-1"></i>
                                SLA Status
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($outageTicket->sla_breached)
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>SLA Breached!</strong><br>
                                    Breach Time: {{ $outageTicket->sla_breach_time->format('M d, Y H:i A') }}
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check"></i>
                                    <strong>Within SLA</strong>
                                </div>
                            @endif

                            <p><strong>Duration:</strong><br>
                            @if($outageTicket->end_time)
                                {{ $outageTicket->start_time->diffForHumans($outageTicket->end_time, true) }}
                            @else
                                {{ $outageTicket->start_time->diffForHumans() }} (ongoing)
                            @endif
                            </p>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-paperclip mr-1"></i>
                                Attachments ({{ $outageTicket->attachments->count() }})
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($outageTicket->attachments->count() > 0)
                                @foreach($outageTicket->attachments as $attachment)
                                    <div class="attachment-item mb-2">
                                        <i class="fas {{ $attachment->getFileIcon() }}"></i>
                                        <a href="{{ $attachment->file_url }}" target="_blank">
                                            {{ $attachment->file_name }}
                                        </a>
                                        <small class="text-muted d-block">
                                            {{ $attachment->getHumanReadableSize() }} -
                                            {{ $attachment->uploader->name }}
                                        </small>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">No attachments</p>
                            @endif
                        </div>
                    </div>

                    <!-- Related Reasons -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-question-circle mr-1"></i>
                                Reasons ({{ $outageTicket->reasons->count() }})
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($outageTicket->reasons->count() > 0)
                                @foreach($outageTicket->reasons as $reason)
                                    <div class="reason-item mb-2 p-2 border rounded">
                                        <strong>{{ $reason->reason_title }}</strong>
                                        <p class="text-muted mb-1">{{ $reason->reason_description }}</p>
                                        @if($reason->is_resolved)
                                            <span class="badge badge-success">Resolved</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">No reasons identified yet</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Progress Update Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Progress Update</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="progressForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Keep Current Status</option>
                            <option value="Open" {{ $outageTicket->status == 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="In Progress" {{ $outageTicket->status == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="On Hold" {{ $outageTicket->status == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="Resolved" {{ $outageTicket->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="Closed" {{ $outageTicket->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="progress_notes">Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="progress_notes" name="notes" rows="4"
                                  placeholder="Describe the current progress..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="action_taken">Action Taken</label>
                        <textarea class="form-control" id="action_taken" name="action_taken" rows="3"
                                  placeholder="What actions were taken?"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="next_steps">Next Steps</label>
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
                    <button type="button" class="btn btn-secondary btn-xs" data-dismiss="modal">Cancel</button>
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
.reason-item {
    background-color: #f8f9fa;
}
.dark-style .attachment-item {
    border-color: #444564;
}
.dark-style .reason-item {
    background-color: #2b2c40;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle progress update
    $('#progressForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("outage-tickets.updateProgress", $outageTicket) }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while adding progress');
            }
        });
    });
});
</script>
@endpush

