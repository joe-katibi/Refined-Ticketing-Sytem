@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Create New Ticket')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create New Ticket</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('outages.index') }}">Outages</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('outage-tickets.index') }}">Tickets</a></li>
                        <li class="breadcrumb-item active">Create</li>
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
                                <i class="fas fa-plus mr-1"></i>
                                Ticket Information
                            </h3>
                        </div>
                        <form action="{{ route('outage-tickets.store') }}" method="POST">
                            @csrf
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
                                                            {{ (old('outage_id', $outage->id ?? '') == $outageOption->id) ? 'selected' : '' }}>
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
                                                   id="title" name="title" value="{{ old('title') }}"
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
                                                      placeholder="Describe the ticket details...">{{ old('description') }}</textarea>
                                            @error('description')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="priority">Priority <span class="text-danger">*</span></label>
                                            <select class="form-control @error('priority') is-invalid @enderror"
                                                    id="priority" name="priority" required>
                                                <option value="">Select Priority</option>
                                                @foreach($priorities as $priority)
                                                    <option value="{{ $priority }}" {{ old('priority') == $priority ? 'selected' : '' }}>
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
                                                    <option value="{{ $impact }}" {{ old('impact') == $impact ? 'selected' : '' }}>
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
                                                    <option value="{{ $urgency }}" {{ old('urgency') == $urgency ? 'selected' : '' }}>
                                                        {{ $urgency }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('urgency')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror"
                                                   id="start_time" name="start_time" value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}" required>
                                            @error('start_time')
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
                                                    <option value="{{ $team->id }}" {{ old('assigned_team_id') == $team->id ? 'selected' : '' }}>
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
                                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
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
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-success btn-xs">
                                    <i class="fas fa-save"></i> Create Ticket
                                </button>
                                <a href="{{ route('outage-tickets.index') }}" class="btn btn-secondary btn-xs">
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

    // Filter users based on selected team
    $('#assigned_team_id').change(function() {
        var teamId = $(this).val();
        var userSelect = $('#assigned_to');

        if (teamId) {
            // You can implement AJAX call here to filter users by team
            // For now, we'll show all users
        }
    });
});
</script>
@endpush

