@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Edit Assigned Appointment')

@section('vendor-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('page-script')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('body')
        });

        // Toggle fields based on status
        function toggleFields() {
            const status = $('#status').val();
            
            // Show/hide closing fields
            if (status === 'Scheduled-Closed') {
                $('#closing-fields').show();
            } else {
                $('#closing-fields').hide();
            }
            
            // Show/hide sub department field
            const escalationStatuses = [
                'Escalated-Open', 
                'Escalated-Infrastructure', 
                'Escalated-Noc'
            ];
            
            if (escalationStatuses.includes(status)) {
                $('#sub-department-field').show();
                $('#final_reason').closest('.mb-3').show();
            } else {
                $('#sub-department-field').hide();
            }
            
            // Show/hide rescheduled fields
            if (status === 'Rescheduled') {
                $('#reschedule-fields').show();
                $('#final_reason').closest('.mb-3').hide();
                $('#sub-department-field').hide();
            } else {
                $('#reschedule-fields').hide();
            }
            
            // Show/hide cancelled reason field
            if (status === 'Cancelled') {
                $('#cancelled-reason-field').show();
                $('#final_reason').closest('.mb-3').hide();
                $('#sub-department-field').hide();
            } else {
                $('#cancelled-reason-field').hide();
            }
        }

        // Initial toggle
        toggleFields();

        // Toggle on status change
        $('#status').on('change', function() {
            toggleFields();
        });
    });
    </script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Assigned Appointment #{{ $appointment->appointment_ticket_id }}</h3>
                <div class="card-tools">
                    <a href="{{ route('appointment.appointments.assigned') }}" class="btn btn-secondary btn-xs">
                        <i class="fas fa-arrow-left me-1"></i> Back to Assigned Appointments
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('appointment.appointments.update_assigned', $appointment->id) }}" class="needs-validation" novalidate id="appointment-form">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Appointment Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Appointment ID</label>
                                        <p class="form-control-static">{{ $appointment->appointment_ticket_id }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Account Number</label>
                                        <p class="form-control-static">{{ $appointment->account_number }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Type</label>
                                        <p class="form-control-static">
                                            {{ $appointment->type->type_name ?? 'N/A' }}
                                            @if($appointment->subType)
                                                ({{ $appointment->subType->sub_type_name }})
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Scheduled</label>
                                        <p class="form-control-static">
                                            {{ $appointment->scheduled_date ? \Carbon\Carbon::parse($appointment->scheduled_date)->format('M d, Y') : 'N/A' }}
                                            at {{ $appointment->scheduled_time ?? 'N/A' }}
                                        </p>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Team Type</label>
                                        <p class="form-control-static">{{ $appointment->teamType->type_name ?? 'N/A' }}</p>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Sub Team Type</label>
                                        <p class="form-control-static">{{ $appointment->subTeamType->sub_type_name ?? 'N/A' }}</p>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Assigned Team</label>
                                        <p class="form-control-static">{{ $appointment->assignedTeam->team_name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status and Actions -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Status & Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror"
                                                id="status" name="status" required>
                                            @php
                                                // Other flows (store(), the admin edit() page, the
                                                // escalation-to-appointment conversion) write status
                                                // as lowercase-hyphenated ("scheduled-assigned-team"),
                                                // while this page's options are Title-Case-With-Hyphens
                                                // to match updateAssigned()'s own string checks (e.g.
                                                // 'Scheduled-Closed'). A plain === comparison against
                                                // $appointment->status therefore never matched, so this
                                                // dropdown always silently defaulted to its first option
                                                // regardless of the ticket's real status — normalize both
                                                // sides before comparing.
                                                $normalize = fn($v) => strtolower(str_replace(' ', '-', $v ?? ''));
                                                $currentStatusNormalized = $normalize($appointment->status);
                                            @endphp
                                            @foreach(['Scheduled-Open','Scheduled-Closed','Escalated-Open','Escalated-Closed','In-Progress','Rescheduled','Escalated-Infrastructure','Support-Post-Install',
'Escalated-Noc','Escalated-Design','Scheduled-Assigned Team','Cancelled'] as $status)
                                                <option value="{{ $status }}" {{ $normalize($status) === $currentStatusNormalized ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Optical Level -->
                                    <div class="mb-3">
                                        <label for="optical_level" class="form-label">Optical Level (dBm)</label>
                                        <input type="number" step="0.01" class="form-control @error('optical_level') is-invalid @enderror"
                                               id="optical_level" name="optical_level" value="{{ old('optical_level', $appointment->optical_level) }}">
                                        @error('optical_level')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Final Reason -->
                                    <div class="mb-3">
                                        <label for="final_reason" class="form-label">Final Reason</label>
                                        <select class="form-select select2 @error('final_reason') is-invalid @enderror"
                                                id="final_reason" name="final_reason">
                                            <option value="">Select Final Reason</option>
                                            @foreach($finalReasons as $reason)
                                                <option value="{{ $reason->id }}" {{ old('final_reason', $appointment->final_reason_id) == $reason->id ? 'selected' : '' }}>
                                                    {{ $reason->final_reason_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('final_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Comment -->
                                    <div class="mb-3">
                                        <label for="comment" class="form-label">Comment</label>
                                        <textarea class="form-control @error('comment') is-invalid @enderror"
                                                  id="comment" name="comment" rows="3">{{ old('comment', $appointment->comment) }}</textarea>
                                        @error('comment')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Sub Department (Conditional) -->
                                    <div class="mb-3" id="sub-department-field" style="display: none;">
                                        <label for="sub_department_id" class="form-label">Sub Department</label>
                                        <select class="form-select @error('sub_department_id') is-invalid @enderror"
                                                id="sub_department_id" name="sub_department_id">
                                            <option value="">Select Sub Department</option>
                                            @foreach($subDepartments ?? [] as $subDept)
                                                <option value="{{ $subDept->id }}" {{ old('sub_department_id', $appointment->sub_department_id) == $subDept->id ? 'selected' : '' }}>
                                                    {{ $subDept->sub_department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('sub_department_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Rescheduled Fields (Conditional) -->
                                    <div id="reschedule-fields" style="display: none;">
                                        <div class="mb-3">
                                            <label for="rescheduled_date" class="form-label">Rescheduled Date</label>
                                            <input type="date" class="form-control @error('rescheduled_date') is-invalid @enderror"
                                                   id="rescheduled_date" name="rescheduled_date" value="{{ old('rescheduled_date', $appointment->rescheduled_date) }}">
                                            @error('rescheduled_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="rescheduled_time" class="form-label">Rescheduled Time</label>
                                            <input type="time" class="form-control @error('rescheduled_time') is-invalid @enderror"
                                                   id="rescheduled_time" name="rescheduled_time" value="{{ old('rescheduled_time', $appointment->rescheduled_time) }}">
                                            @error('rescheduled_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="reschedule_reason" class="form-label">Reschedule Reason</label>
                                            <textarea class="form-control @error('reschedule_reason') is-invalid @enderror"
                                                      id="reschedule_reason" name="reschedule_reason" rows="3">{{ old('reschedule_reason', $appointment->reschedule_reason) }}</textarea>
                                            @error('reschedule_reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Cancelled Fields (Conditional) -->
                                    <div class="mb-3" id="cancelled-reason-field" style="display: none;">
                                        <label for="cancelled_reason" class="form-label">Cancelled Reason</label>
                                        <textarea class="form-control @error('cancelled_reason') is-invalid @enderror"
                                                  id="cancelled_reason" name="cancelled_reason" rows="3">{{ old('cancelled_reason', $appointment->cancelled_reason) }}</textarea>
                                        @error('cancelled_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Custom Confirmation -->
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="custom_confirmation"
                                                   name="custom_confirmation" value="1" {{ old('custom_confirmation', $appointment->custom_confirmation) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="custom_confirmation">
                                                Custom Confirmation Received
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Closing Fields (Conditional) -->
                        <div class="col-12" id="closing-fields" style="display: none;">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Closing Information</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Closing Reason -->
                                    <div class="mb-3">
                                        <label for="closing_reason" class="form-label">Closing Reason</label>
                                        <textarea class="form-control @error('closing_reason') is-invalid @enderror"
                                                  id="closing_reason" name="closing_reason" rows="3">{{ old('closing_reason', $appointment->closing_reason) }}</textarea>
                                        @error('closing_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Notes -->
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Additional Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                                  id="notes" name="notes" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                                <a href="{{ route('appointment.appointments.assigned') }}" class="btn btn-secondary btn-xs">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

