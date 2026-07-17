@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit Ticket - {{ $outageTicket->ticket_number }}')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Ticket - {{ $outageTicket->ticket_number }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('outages.index') }}">Outages</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('outage-tickets.index') }}">Tickets</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('outage-tickets.show', $outageTicket) }}">{{ $outageTicket->ticket_number }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-edit mr-1"></i>
                                Edit Ticket Information
                            </h3>
                        </div>
                        <form action="{{ route('outage-tickets.update', $outageTicket) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="outage_id">Related Outage <span class="text-danger">*</span></label>
                                            <select class="form-control @error('outage_id') is-invalid @enderror"
                                                    id="outage_id" name="outage_id" required>
                                                <option value="">Select Outage</option>
                                                @foreach($outages as $outageOption)
                                                    <option value="{{ $outageOption->id }}"
                                                            {{ (old('outage_id', $outageTicket->outage_id) == $outageOption->id) ? 'selected' : '' }}>
                                                        {{ $outageOption->ticket_number }} - {{ $outageOption->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('outage_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title">Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                   id="title" name="title" value="{{ old('title', $outageTicket->title) }}"
                                                   placeholder="Enter ticket title" required>
                                            @error('title')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror"
                                                      id="description" name="description" rows="4"
                                                      placeholder="Describe the ticket details...">{{ old('description', $outageTicket->description) }}</textarea>
                                            @error('description')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="status">Status <span class="text-danger">*</span></label>
                                            <select class="form-control @error('status') is-invalid @enderror"
                                                    id="status" name="status" required>
                                                <option value="">Select Status</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}" {{ old('status', $outageTicket->status) == $status ? 'selected' : '' }}>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('status')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="priority">Priority <span class="text-danger">*</span></label>
                                            <select class="form-control @error('priority') is-invalid @enderror"
                                                    id="priority" name="priority" required>
                                                <option value="">Select Priority</option>
                                                @foreach($priorities as $priority)
                                                    <option value="{{ $priority }}" {{ old('priority', $outageTicket->priority) == $priority ? 'selected' : '' }}>
                                                        {{ $priority }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('priority')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="impact">Impact <span class="text-danger">*</span></label>
                                            <select class="form-control @error('impact') is-invalid @enderror"
                                                    id="impact" name="impact" required>
                                                <option value="">Select Impact</option>
                                                @foreach($impacts as $impact)
                                                    <option value="{{ $impact }}" {{ old('impact', $outageTicket->impact) == $impact ? 'selected' : '' }}>
                                                        {{ $impact }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('impact')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="urgency">Urgency <span class="text-danger">*</span></label>
                                            <select class="form-control @error('urgency') is-invalid @enderror"
                                                    id="urgency" name="urgency" required>
                                                <option value="">Select Urgency</option>
                                                @foreach($urgencies as $urgency)
                                                    <option value="{{ $urgency }}" {{ old('urgency', $outageTicket->urgency) == $urgency ? 'selected' : '' }}>
                                                        {{ $urgency }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('urgency')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror"
                                                   id="start_time" name="start_time"
                                                   value="{{ old('start_time', $outageTicket->start_time->format('Y-m-d\TH:i')) }}" required>
                                            @error('start_time')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_time">End Time</label>
                                            <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror"
                                                   id="end_time" name="end_time"
                                                   value="{{ old('end_time', $outageTicket->end_time ? $outageTicket->end_time->format('Y-m-d\TH:i') : '') }}">
                                            @error('end_time')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="assigned_team_id">Assigned Team</label>
                                            <select class="form-control @error('assigned_team_id') is-invalid @enderror"
                                                    id="assigned_team_id" name="assigned_team_id">
                                                <option value="">Select Team</option>
                                                @foreach($teams as $team)
                                                    <option value="{{ $team->id }}" {{ old('assigned_team_id', $outageTicket->assigned_team_id) == $team->id ? 'selected' : '' }}>
                                                        {{ $team->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('assigned_team_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="assigned_to">Assigned To</label>
                                            <select class="form-control @error('assigned_to') is-invalid @enderror"
                                                    id="assigned_to" name="assigned_to">
                                                <option value="">Select User</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('assigned_to', $outageTicket->assigned_to) == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('assigned_to')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Resolution Fields (show only if status is Resolved or Closed) -->
                                <div id="resolution-fields" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="resolution">Resolution</label>
                                                <select class="form-control @error('resolution') is-invalid @enderror"
                                                        id="resolution" name="resolution">
                                                    <option value="">Select Resolution</option>
                                                    <option value="Fixed" {{ old('resolution', $outageTicket->resolution) == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                                    <option value="Workaround Applied" {{ old('resolution', $outageTicket->resolution) == 'Workaround Applied' ? 'selected' : '' }}>Workaround Applied</option>
                                                    <option value="Cannot Reproduce" {{ old('resolution', $outageTicket->resolution) == 'Cannot Reproduce' ? 'selected' : '' }}>Cannot Reproduce</option>
                                                    <option value="Duplicate" {{ old('resolution', $outageTicket->resolution) == 'Duplicate' ? 'selected' : '' }}>Duplicate</option>
                                                    <option value="Not an Issue" {{ old('resolution', $outageTicket->resolution) == 'Not an Issue' ? 'selected' : '' }}>Not an Issue</option>
                                                    <option value="Third Party Issue" {{ old('resolution', $outageTicket->resolution) == 'Third Party Issue' ? 'selected' : '' }}>Third Party Issue</option>
                                                </select>
                                                @error('resolution')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="resolution_notes">Resolution Notes</label>
                                                <textarea class="form-control @error('resolution_notes') is-invalid @enderror"
                                                          id="resolution_notes" name="resolution_notes" rows="3"
                                                          placeholder="Describe the resolution...">{{ old('resolution_notes', $outageTicket->resolution_notes) }}</textarea>
                                                @error('resolution_notes')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-success btn-xs">
                                    <i class="fas fa-save"></i> Update Ticket
                                </button>
                                <a href="{{ route('outage-tickets.show', $outageTicket) }}" class="btn btn-secondary btn-xs">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Show/hide resolution fields based on status
    function toggleResolutionFields() {
        var status = $('#status').val();
        if (status === 'Resolved' || status === 'Closed') {
            $('#resolution-fields').show();
            $('#resolution').prop('required', true);
        } else {
            $('#resolution-fields').hide();
            $('#resolution').prop('required', false);
        }
    }

    // Initialize on page load
    toggleResolutionFields();

    // Toggle when status changes
    $('#status').change(function() {
        toggleResolutionFields();

        // Auto-set end time if status is Resolved or Closed and end time is empty
        if (($(this).val() === 'Resolved' || $(this).val() === 'Closed') && !$('#end_time').val()) {
            var now = new Date();
            var year = now.getFullYear();
            var month = String(now.getMonth() + 1).padStart(2, '0');
            var day = String(now.getDate()).padStart(2, '0');
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');

            $('#end_time').val(year + '-' + month + '-' + day + 'T' + hours + ':' + minutes);
        }
    });

    // Set priority color based on selection
    $('#priority, #impact, #urgency').change(function() {
        var value = $(this).val();
        var $this = $(this);

        $this.removeClass('text-info text-warning text-danger');

        switch(value) {
            case 'Critical':
                $this.addClass('text-danger');
                break;
            case 'High':
                $this.addClass('text-warning');
                break;
            case 'Medium':
                $this.addClass('text-info');
                break;
            default:
                break;
        }
    });

    // Set status color based on selection
    $('#status').change(function() {
        var value = $(this).val();
        var $this = $(this);

        $this.removeClass('text-success text-warning text-danger text-info');

        switch(value) {
            case 'Resolved':
            case 'Closed':
                $this.addClass('text-success');
                break;
            case 'In Progress':
                $this.addClass('text-warning');
                break;
            case 'On Hold':
                $this.addClass('text-danger');
                break;
            case 'Open':
                $this.addClass('text-info');
                break;
            default:
                break;
        }
    });

    // Filter users based on selected team
    $('#assigned_team_id').change(function() {
        var teamId = $(this).val();
        var userSelect = $('#assigned_to');

        if (teamId) {
            // You can implement AJAX call here to filter users by team
            // For now, we'll show all users
        }
    });

    // Trigger initial color setting
    $('#priority, #impact, #urgency, #status').trigger('change');
});
</script>
@endpush

