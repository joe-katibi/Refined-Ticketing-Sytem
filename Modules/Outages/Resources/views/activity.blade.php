@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Outage Activity History - #' . $outage->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="fas fa-history text-primary me-2"></i>
                            Activity History - Outage #{{ $outage->id }}
                        </h4>
                        <p class="text-muted mb-0 mt-1">{{ $outage->title }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('outages.show', $outage) }}" class="btn btn-outline-primary btn-xs">
                            <i class="fas fa-eye me-1"></i>View Outage
                        </a>
                        <a href="{{ route('outages.index') }}" class="btn btn-secondary btn-xs">
                            <i class="fas fa-list me-1"></i>Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Current Status:</strong>
                            <span class="badge badge-xs bg-{{ $outage->status === 'support-closed' ? 'success' : ($outage->status === 'noc-confirmed-outage' ? 'danger' : 'warning') }} ms-2">
                                {{ $outage->status_display }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Priority:</strong>
                            <span class="badge badge-xs bg-{{ $outage->priority === 'High' ? 'danger' : ($outage->priority === 'Medium' ? 'warning' : 'info') }} ms-2">
                                {{ $outage->priority }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Assigned Team:</strong>
                            <span class="ms-2">{{ $outage->assignedTeam->name ?? 'Unassigned' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Created:</strong>
                            <span class="ms-2">{{ $outage->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-info me-2"></i>
                        Activity Timeline
                        <span class="badge badge-xs bg-secondary ms-2">{{ $outage->activities->count() }} Activities</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($outage->activities->count() > 0)
                        <div class="timeline">
                            @foreach($outage->activities as $activity)
                                <div class="timeline-item mb-4">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="timeline-icon bg-{{ $activity->activity_color }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="{{ $activity->activity_icon }}"></i>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card border-start border-{{ $activity->activity_color }} border-3">
                                                <div class="card-body py-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0 text-{{ $activity->getActivityColor() }}">
                                                            {{ $activity->getActivityTitle() }}
                                                        </h6>
                                                        <small class="text-muted">
                                                            {{ $activity->created_at->diffForHumans() }}
                                                            <br>
                                                            <span class="text-xs">{{ $activity->created_at->format('M j, Y g:i:s A') }}</span>
                                                        </small>
                                                    </div>

                                                    <p class="mb-2 text-dark">{{ $activity->description }}</p>

                                                    @if($activity->metadata)
                                                        <div class="mt-2">
                                                            @if(isset($activity->metadata['old_value']) && isset($activity->metadata['new_value']))
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">Previous:</small>
                                                                        <div class="bg-light p-2 rounded">
                                                                            <code>{{ $activity->metadata['old_value'] }}</code>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <small class="text-muted">Updated to:</small>
                                                                        <div class="bg-light p-2 rounded">
                                                                            <code>{{ $activity->metadata['new_value'] }}</code>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @elseif(isset($activity->metadata['progress_text']))
                                                                <div class="bg-light p-2 rounded">
                                                                    <small class="text-muted">Progress Note:</small>
                                                                    <div class="mt-1">{{ $activity->metadata['progress_text'] }}</div>
                                                                </div>
                                                            @else
                                                                <div class="bg-light p-2 rounded">
                                                                    <small class="text-muted">Details:</small>
                                                                    <pre class="mb-0 mt-1"><code>{{ json_encode($activity->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="mt-2 d-flex align-items-center">
                                                        <i class="fas fa-user text-muted me-1"></i>
                                                        <small class="text-muted">
                                                            {{ $activity->user->name ?? 'System' }}
                                                            @if($activity->user && $activity->user->email)
                                                                ({{ $activity->user->email }})
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Activity History</h5>
                            <p class="text-muted">No activities have been recorded for this outage yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child .timeline-icon::after {
    display: none;
}

.timeline-icon {
    position: relative;
    z-index: 2;
}

.timeline-icon::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -20px;
    width: 2px;
    height: 20px;
    background: #dee2e6;
    transform: translateX(-50%);
}

.border-3 {
    border-width: 3px !important;
}

.text-xs {
    font-size: 0.75rem;
}
</style>
@endsection

