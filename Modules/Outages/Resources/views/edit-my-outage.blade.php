@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Update Outage Progress')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />
@endsection

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-2">
                    <span class="text-muted fw-light">My Outages /</span> Update Progress
                </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('outages.my-outages') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i> Back to My Outages
                    </a>
                    <a href="{{ route('outages.show', $outage->id) }}" class="btn btn-info btn-xs">
                        <i class="bx bx-show me-1"></i> View Details
                    </a>
                    <a href="{{ route('outages.activity', $outage->id) }}" class="btn btn-warning btn-xs">
                        <i class="bx bx-history me-1"></i> History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Outage Summary Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="bx bx-info-circle me-2"></i>Outage Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-medium">Ticket Number:</td>
                                    <td><span class="badge badge-xs bg-primary">{{ $outage->ticket_number }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">Title:</td>
                                    <td>{{ $outage->title }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">Current Status:</td>
                                    <td>
                                        <span class="badge badge-xs bg-{{ 
                                            $outage->status == 'support-closed' ? 'success' : 
                                            ($outage->status == 'infra-resolved' ? 'info' : 
                                            ($outage->status == 'noc-rejected' ? 'danger' : 'warning')) 
                                        }}">
                                            {{ $outage->status_display }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">Priority:</td>
                                    <td>
                                        <span class="badge badge-xs bg-{{ 
                                            $outage->priority == 'Critical' ? 'danger' : 
                                            ($outage->priority == 'High' ? 'warning' : 
                                            ($outage->priority == 'Medium' ? 'info' : 'secondary')) 
                                        }}">
                                            {{ $outage->priority }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-medium">Created:</td>
                                    <td>{{ $outage->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">Assigned Team:</td>
                                    <td>{{ $outage->assignedTeam->team_name ?? 'Not assigned' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">Assignee:</td>
                                    <td>{{ $outage->assignee->name ?? 'Not assigned' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">Reporter:</td>
                                    <td>{{ $outage->reporter->name ?? 'Unknown' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @if($outage->description)
                        <div class="mt-3">
                            <strong>Description:</strong>
                            <p class="text-muted mb-0">{{ $outage->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Update Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="bx bx-edit me-2"></i>Update Progress
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('outages.my-outages.update', $outage->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <!-- Status Update -->
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    @foreach($statusOptions as $key => $label)
                                        <option value="{{ $key }}" {{ $outage->status == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Final Reason (for closing) -->
                            <div class="col-md-6">
                                <label for="final_reason_id" class="form-label">Final Reason (if closing)</label>
                                <select class="form-select @error('final_reason_id') is-invalid @enderror" id="final_reason_id" name="final_reason_id">
                                    <option value="">Select reason (optional)</option>
                                    @if(isset($finalReasons))
                                        @foreach($finalReasons as $reason)
                                            <option value="{{ $reason->id }}" 
                                                    {{ old('final_reason_id', $outage->final_reason_id) == $reason->id ? 'selected' : '' }}
                                                    title="{{ $reason->final_reason_description }}">
                                                {{ $reason->final_reason_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('final_reason_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($outage->finalReason)
                                    <small class="form-text text-muted">Current: {{ $outage->finalReason->final_reason_name }}</small>
                                @endif
                            </div>

                            <!-- Progress Notes -->
                            <div class="col-12">
                                <label for="progress_notes" class="form-label">Progress Notes</label>
                                <textarea class="form-control @error('progress_notes') is-invalid @enderror" 
                                          id="progress_notes" name="progress_notes" rows="4" 
                                          placeholder="Describe what you've done, current findings, next steps, etc.">{{ old('progress_notes') }}</textarea>
                                @error('progress_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Document your troubleshooting steps, findings, and any actions taken.
                                </div>
                            </div>

                            <!-- Resolution Notes -->
                            <div class="col-12">
                                <label for="resolution_notes" class="form-label">Resolution Notes</label>
                                <textarea class="form-control @error('resolution_notes') is-invalid @enderror" 
                                          id="resolution_notes" name="resolution_notes" rows="3" 
                                          placeholder="Final resolution details (if resolved)">{{ old('resolution_notes', $outage->resolution_notes) }}</textarea>
                                @error('resolution_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Provide final resolution details if the outage is being resolved or closed.
                                </div>
                            </div>

                            <!-- File Attachments -->
                            <div class="col-12">
                                <label for="attachments" class="form-label">Attachments</label>
                                <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" 
                                       id="attachments" name="attachments[]" multiple 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Upload images of FDT, OLT, network diagrams, or other relevant documentation. 
                                    Supported formats: JPG, PNG, GIF, PDF, DOC, DOCX. Max size: 10MB per file.
                                </div>
                            </div>

                            <!-- Preview area for selected files -->
                            <div class="col-12">
                                <div id="file-preview" class="mt-2" style="display: none;">
                                    <h6>Selected Files:</h6>
                                    <div id="file-list" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12">
                                <hr class="my-4">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('outages.my-outages') }}" class="btn btn-outline-secondary btn-xs">
                                        <i class="bx bx-x me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-xs">
                                        <i class="bx bx-save me-1"></i> Update Progress
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Progress Updates -->
    @if($outage->progress && $outage->progress->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0">
                        <i class="bx bx-history me-2"></i>Recent Progress Updates
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($outage->progress->take(3) as $progress)
                        <div class="d-flex mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                            <div class="avatar avatar-sm me-3">
                                <div class="avatar-initial bg-primary rounded-circle">
                                    <i class="bx bx-user"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $progress->user->name ?? 'Unknown User' }}</h6>
                                        <small class="text-muted">{{ $progress->created_at->format('M d, Y h:i A') }}</small>
                                    </div>
                                    <span class="badge badge-xs bg-info">{{ $progress->status_at_time }}</span>
                                </div>
                                <p class="mb-0 mt-2">{{ $progress->progress_notes }}</p>
                            </div>
                        </div>
                    @endforeach
                    
                    @if($outage->progress->count() > 3)
                        <div class="text-center">
                            <a href="{{ route('outages.activity', $outage->id) }}" class="btn btn-outline-primary btn-xs">
                                <i class="bx bx-history me-1"></i> View All Updates
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // File preview functionality
    $('#attachments').on('change', function() {
        const files = this.files;
        const filePreview = $('#file-preview');
        const fileList = $('#file-list');
        
        if (files.length > 0) {
            fileList.empty();
            filePreview.show();
            
            Array.from(files).forEach(function(file, index) {
                const fileItem = $(`
                    <div class="border rounded p-2 bg-light">
                        <small class="d-block fw-medium">${file.name}</small>
                        <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    </div>
                `);
                fileList.append(fileItem);
            });
        } else {
            filePreview.hide();
        }
    });

    // Status change handling
    $('#status').on('change', function() {
        const status = $(this).val();
        const finalReasonField = $('#final_reason_id');
        
        // Always show final reason field, but make it required only for closing statuses
        finalReasonField.closest('.col-md-6').show();
        
        if (status === 'support-closed' || status === 'infra-resolved') {
            finalReasonField.prop('required', true);
            finalReasonField.closest('.col-md-6').find('label').html('Final Reason (required) <span class="text-danger">*</span>');
        } else {
            finalReasonField.prop('required', false);
            finalReasonField.closest('.col-md-6').find('label').html('Final Reason (optional)');
        }
    });

    // Trigger status change on page load
    $('#status').trigger('change');

    // Form validation
    $('form').on('submit', function(e) {
        const status = $('#status').val();
        const progressNotes = $('#progress_notes').val().trim();
        const resolutionNotes = $('#resolution_notes').val().trim();
        
        if (!progressNotes && !resolutionNotes) {
            e.preventDefault();
            alert('Please provide either progress notes or resolution notes.');
            return false;
        }
        
        if ((status === 'support-closed' || status === 'infra-resolved') && !resolutionNotes) {
            e.preventDefault();
            alert('Resolution notes are required when closing or resolving an outage.');
            return false;
        }
    });
});
</script>
@endsection

