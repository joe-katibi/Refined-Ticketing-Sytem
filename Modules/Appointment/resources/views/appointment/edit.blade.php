@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Edit Appointment')


@section('vendor-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('page-script')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    // Add global error handler to catch and log JavaScript errors
    window.onerror = function(message, source, lineno, colno, error) {
        console.error('JavaScript Error:', message);
        console.error('Source:', source);
        console.error('Line:', lineno, 'Column:', colno);
        console.error('Error object:', error);
        return false;
    };

    $(document).ready(function() {
        console.log('Document ready - initializing team/sub-team selection');

        // Add form submission handler with direct form submission
        $('#appointment-form').on('submit', function(e) {
            console.log('Form submission started');
            
            // Show submission status
            $('#submission-status').show();
            
            // Prevent multiple submissions
            var submitBtn = $(this).find('button[type="submit"]');
            if (submitBtn.hasClass('submitting')) {
                console.log('Form already submitting, preventing duplicate submission');
                e.preventDefault();
                return false;
            }
            
            // Add submitting class and change button text
            submitBtn.addClass('submitting').html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');
            submitBtn.prop('disabled', true);
            console.log('Form submission in progress');
            
            // Log form data for debugging
            console.log('Form data:', $(this).serialize());
            
            // Continue with normal form submission (no AJAX)
            return true;
        });

        // Initialize Select2 for all select elements with select2 class
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('body')
        });
        
        // Store original status value for comparison
        var originalStatus = $('#status').val();
        console.log('Original status:', originalStatus);
        
        // Initialize status dropdown with color indicators
        $('#status').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('body'),
            templateResult: formatStatusOption,
            templateSelection: formatStatusOption
        }).on('change', function() {
            var currentStatus = $(this).val();
            console.log('Status changed from', originalStatus, 'to', currentStatus);
            
            // Show notes field if status has changed
            if (currentStatus && currentStatus !== originalStatus) {
                $('#status-change-notes-container').slideDown();
            } else {
                $('#status-change-notes-container').slideUp();
            }
        });
        
        // Function to format status options with color indicators
        function formatStatusOption(status) {
            if (!status.id) {
                return status.text;
            }
            
            var $option = $(status.element);
            var color = $option.data('color') || '#777';
            var badgeClass = $option.data('badge-class') || 'bg-secondary';
            
            var $status = $(
                '<span><span class="color-dot" style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:' + color + '; margin-right:8px;"></span>' +
                status.text + '</span>'
            );
            
            return $status;
        }
        
        console.log('Select2 initialized successfully');

        // Get initial values from the form
        var initialTeamTypeId = $('#team_type_id').val();
        var initialSubTeamId = "{{ $appointment->sub_team_type_id ?? '' }}";
        var initialAssignedTeamId = "{{ $appointment->assigned_team_id ?? '' }}";
        var initialAssignedToId = "{{ $appointment->assigned_to ?? '' }}";

        console.log('Initial team type ID:', initialTeamTypeId);
        console.log('Initial sub-team ID:', initialSubTeamId);
        console.log('Initial assigned team ID:', initialAssignedTeamId);

        // Load sub-teams on page load if team type is already selected
        if (initialTeamTypeId) {
            console.log('Loading initial sub-teams...');
            loadSubTeams(initialTeamTypeId, initialSubTeamId);

            // Load assigned teams if team type is Outsource partner
            if (initialTeamTypeId == 2) {
                console.log('Loading initial assigned teams...');
                loadAssignedTeams(initialAssignedTeamId);
            }
        }

        // Load technicians (Assigned To) on page load if a sub team is already selected
        if (initialSubTeamId) {
            loadTechnicians(initialSubTeamId, initialAssignedToId);
        }

        // Handle team type change
        $('#team_type_id').on('change', function() {
            var teamTypeId = $(this).val();
            var teamTypeName = $(this).find('option:selected').text().trim();
            console.log('Team type changed to:', teamTypeId, 'Name:', teamTypeName);

            // Show/hide assigned team based on team type ID
            toggleAssignedTeamField(teamTypeId);

            // Load sub-teams
            loadSubTeams(teamTypeId);

            // Load assigned teams if team type is Outsource partner
            if (teamTypeId == 2) {
                loadAssignedTeams();
            }

            // Changing the team invalidates the previously loaded technician list
            $('#assigned_to').empty().append('<option value="">Select Sub Team first</option>').prop('disabled', true);
        });

        // Handle sub team change: reload the technician ("Assigned To") list
        // scoped to the newly selected sub team.
        $('#sub_team_type_id').on('change', function() {
            var subTeamTypeId = $(this).val();
            loadTechnicians(subTeamTypeId);
        });

        /**
         * Load active technicians belonging to a sub team type, for the
         * "Assigned To" dropdown.
         */
        function loadTechnicians(subTeamTypeId, selectedUserId = null) {
            var assignedToSelect = $('#assigned_to');

            if (!subTeamTypeId) {
                assignedToSelect.empty().append('<option value="">Select Sub Team first</option>').prop('disabled', true);
                return;
            }

            assignedToSelect.empty().prop('disabled', true);
            assignedToSelect.append('<option value="">Loading technicians...</option>');

            $.ajax({
                url: '/appointments/sub-team-types/' + subTeamTypeId + '/users',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    assignedToSelect.empty().prop('disabled', false);
                    assignedToSelect.append('<option value="">Select Technician (optional)</option>');

                    var users = (response && response.users) ? response.users : [];
                    if (users.length > 0) {
                        $.each(users, function(index, user) {
                            var selected = (user.id == selectedUserId) ? 'selected' : '';
                            assignedToSelect.append('<option value="' + user.id + '" ' + selected + '>' + user.name + '</option>');
                        });
                        if (selectedUserId) {
                            assignedToSelect.val(selectedUserId);
                        }
                    } else {
                        assignedToSelect.append('<option value="">No technicians in this sub team</option>');
                    }

                    assignedToSelect.select2('destroy');
                    assignedToSelect.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $('body')
                    });
                },
                error: function() {
                    assignedToSelect.empty().prop('disabled', false);
                    assignedToSelect.append('<option value="">Error loading technicians</option>');
                }
            });
        }
        
        // Handle assigned team change
        $('#assigned_team_id').on('change', function() {
            var assignedTeamId = $(this).val();
            console.log('Assigned team changed to:', assignedTeamId);
            
            // If a team is assigned, update status to "Scheduled-Assigned Team"
            if (assignedTeamId) {
                $('#status').val('Scheduled-Assigned Team').trigger('change');
                console.log('Status updated to: Scheduled-Assigned Team');
            }
        });

        // Function to show/hide assigned team field based on team type ID
        function toggleAssignedTeamField(teamTypeId) {
            var assignedTeamContainer = $('#assigned_team_id').closest('.col-md-6');

            // Check if team type ID is 2 (Outsource partner)
            if (teamTypeId == 2) { // Using == instead of === for string/number comparison
                console.log('Showing assigned team field for Outsource partner (ID: 2)');
                assignedTeamContainer.show();
            } else {
                console.log('Hiding assigned team field for team type ID:', teamTypeId);
                assignedTeamContainer.hide();
                // Clear the assigned team selection
                $('#assigned_team_id').val('').trigger('change');
            }
        }
        
        /**
         * Load assigned teams for selection
         * 
         * @param {number|null} selectedTeamId - The ID of the team to select (if any)
         */
        function loadAssignedTeams(selectedTeamId = null) {
            console.log('loadAssignedTeams called with selectedTeamId:', selectedTeamId);
            
            var assignedTeamSelect = $('#assigned_team_id');
            
            // Clear and disable the assigned team select while loading
            assignedTeamSelect.empty().prop('disabled', true);
            assignedTeamSelect.append('<option value="">Loading teams...</option>');
            
            // Make AJAX request to get teams
            var url = '/teams';
            console.log('Making AJAX request to:', url);
            
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('AJAX success - Teams response:', data);
                    
                    // Enable the select and add default option
                    assignedTeamSelect.empty().prop('disabled', false);
                    assignedTeamSelect.append('<option value="">Select Team</option>');
                    
                    // Add options for each team
                    if (Array.isArray(data) && data.length > 0) {
                        console.log('Found', data.length, 'teams');
                        
                        $.each(data, function(index, team) {
                            var selected = (team.id == selectedTeamId) ? 'selected' : '';
                            console.log('Adding option:', team.id, team.team_name, 'Selected:', selected);
                            assignedTeamSelect.append('<option value="' + team.id + '" ' + selected + '>' + team.team_name + '</option>');
                        });
                        
                        // Set the selected value if provided
                        if (selectedTeamId) {
                            console.log('Setting selected team to:', selectedTeamId);
                            assignedTeamSelect.val(selectedTeamId);
                        }
                        
                        // Destroy and reinitialize Select2 to refresh the dropdown
                        assignedTeamSelect.select2('destroy');
                        assignedTeamSelect.select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            dropdownParent: $('body')
                        });
                        console.log('Select2 refreshed for assigned team dropdown');
                    } else {
                        console.log('No teams found');
                        assignedTeamSelect.append('<option value="">No teams available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    assignedTeamSelect.empty().prop('disabled', false);
                    assignedTeamSelect.append('<option value="">Error loading teams</option>');
                }
            });
        }

        // Initialize the assigned team visibility based on initial team selection
        var initialTeamTypeId = $('#team_type_id').val();
        toggleAssignedTeamField(initialTeamTypeId);

        /**
         * Load sub-teams based on selected team type
         *
         * @param {number} teamTypeId - The ID of the selected team type
         * @param {number|null} selectedSubTeamId - The ID of the sub-team to select (if any)
         */
        function loadSubTeams(teamTypeId, selectedSubTeamId = null) {
            console.log('loadSubTeams called with teamTypeId:', teamTypeId, 'selectedSubTeamId:', selectedSubTeamId);

            var subTeamSelect = $('#sub_team_type_id');

            // Clear and disable the sub-team select while loading
            subTeamSelect.empty().prop('disabled', true);
            subTeamSelect.append('<option value="">Loading sub-teams...</option>');

            // If no team type is selected, show a message and return
            if (!teamTypeId) {
                console.log('No team type selected');
                subTeamSelect.empty().append('<option value="">Select Team First</option>');
                subTeamSelect.prop('disabled', true);
                return;
            }

            // Make AJAX request to get sub-teams
            var url = '/teamtypes/' + teamTypeId + '/sub-teams';
            console.log('Making AJAX request to:', url);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('AJAX success - Sub-teams response:', data);

                    // Enable the select and add default option
                    subTeamSelect.empty().prop('disabled', false);
                    subTeamSelect.append('<option value="">Select Sub Team</option>');

                    // Add options for each sub-team
                    if (Array.isArray(data) && data.length > 0) {
                        console.log('Found', data.length, 'sub-teams');

                        $.each(data, function(index, subTeam) {
                            var selected = (subTeam.id == selectedSubTeamId) ? 'selected' : '';
                            console.log('Adding option:', subTeam.id, subTeam.name, 'Selected:', selected);
                            subTeamSelect.append('<option value="' + subTeam.id + '" ' + selected + '>' + subTeam.name + '</option>');
                        });

                        // Set the selected value if provided
                        if (selectedSubTeamId) {
                            console.log('Setting selected sub-team to:', selectedSubTeamId);
                            subTeamSelect.val(selectedSubTeamId);
                        }

                        // Destroy and reinitialize Select2 to refresh the dropdown
                        subTeamSelect.select2('destroy');
                        subTeamSelect.select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            dropdownParent: $('body')
                        });
                        console.log('Select2 refreshed for sub-team dropdown');
                    } else {
                        console.log('No sub-teams found');
                        subTeamSelect.append('<option value="">No sub-teams available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);

                    subTeamSelect.empty().prop('disabled', false);
                    subTeamSelect.append('<option value="">Error loading sub-teams</option>');
                }
            });
        }
    });
    </script>
@endsection

@push('vendor-style')
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css') }}">
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        padding: 5px 0.75rem;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding: 0;
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
            <div class="card-header">
                <h3 class="card-title">Edit Appointment #{{ $appointment->appointment_ticket_id }}</h3>
                <div class="card-tools">
                    <a href="{{ route('appointment.appointments.show', $appointment->id) }}" class="btn btn-secondary btn-xs">
                        <i class="fas fa-arrow-left"></i> Back to View
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('appointment.appointments.update', $appointment) }}" method="POST" id="appointment-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="escalated_team_id" value="{{ $appointment->escalated_team_id }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" readonly class="form-control @error('account_number') is-invalid @enderror"
                                       id="account_number" name="account_number"
                                       value="{{ old('account_number', $appointment->account_number) }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Appointment Type</label>
                                <input type="hidden" name="appointment_id" value="{{ $appointment->appointment_id }}">
                                <div class="form-control" readonly>{{ $appointment->type ? $appointment->type->type_name : 'Not specified' }}</div>
                                @error('type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sub Type</label>
                                <input type="hidden" name="appointment_type_id" value="{{ $appointment->appointment_type_id }}">
                                <div class="form-control "readonly>{{ $appointment->subType ? $appointment->subType->sub_type_name : 'Not specified' }}</div>
                                @error('appointment_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror"
                                        id="priority" name="priority" required>
                                    @php
                                        // Appointments created via the escalation-to-appointment
                                        // conversion (EscalationsController::update()) validate and
                                        // store priority lowercase ("high"), while this dropdown's
                                        // values are capitalized ("High") and the update() validation
                                        // rule below requires exactly 'High|Medium|Low' — a plain ==
                                        // comparison here never matched, so the field silently stayed
                                        // on its blank placeholder for every such appointment. Being
                                        // `required`, that made the browser block submission of this
                                        // entire form client-side with no visible error — dispatchers
                                        // could never assign a team/technician to these tickets.
                                        $currentPriority = strtolower((string) $appointment->priority);
                                    @endphp
                                    <option value="">Select Priority</option>
                                    <option value="High" {{ $currentPriority === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="Medium" {{ $currentPriority === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="Low" {{ $currentPriority === 'low' ? 'selected' : '' }}>Low</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scheduled_date" class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                       id="scheduled_date" name="scheduled_date"
                                       value="{{ old('scheduled_date', $appointment->scheduled_date ? $appointment->scheduled_date->format('Y-m-d') : '') }}"
                                       required>
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scheduled_time" class="form-label">Scheduled Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('scheduled_time') is-invalid @enderror"
                                       id="scheduled_time" name="scheduled_time"
                                       value="{{ old('scheduled_time', $appointment->scheduled_time ? \Carbon\Carbon::parse($appointment->scheduled_time)->format('H:i') : '') }}"
                                       required>
                                @error('scheduled_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="appointment_location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('appointment_location') is-invalid @enderror"
                                       id="appointment_location" name="appointment_location"
                                       value="{{ old('appointment_location', $appointment->appointment_location) }}" required>
                                @error('appointment_location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="appointment_venue" class="form-label">Venue <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('appointment_venue') is-invalid @enderror"
                                       id="appointment_venue" name="appointment_venue"
                                       value="{{ old('appointment_venue', $appointment->appointment_venue) }}" required>
                                @error('appointment_venue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    @php
                                        // Same bug as the Priority field above: the sibling
                                        // "assigned" edit page (edit_assigned.blade.php) writes
                                        // Title-Case-With-Hyphens status strings ("Escalated-Design"),
                                        // while this dropdown's values come straight from the
                                        // appointment_statuses table (lowercase-hyphenated,
                                        // "escalated-design"). A plain == comparison never matched
                                        // once a ticket had passed through that other page, so this
                                        // required field silently sat on its blank placeholder and
                                        // the browser blocked submission of the whole form —
                                        // including the Team/Sub Team/Assigned To fields below.
                                        $currentStatusNormalized = strtolower(str_replace(' ', '-', (string) $appointment->status));
                                    @endphp
                                    <option value="">Select Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->name }}"
                                            {{ strtolower($status->name) === $currentStatusNormalized ? 'selected' : '' }}
                                            data-color="{{ $status->color }}"
                                            data-badge-class="{{ $status->badge_class }}"
                                        >
                                            {{ $status->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <!-- Status change notes field - will be shown/hidden via JavaScript -->
                                <div id="status-change-notes-container" class="mt-3" style="display: none;">
                                    <label for="status_change_notes" class="form-label">Status Change Reason</label>
                                    <textarea class="form-control" id="status_change_notes" name="status_change_notes" 
                                              rows="2" placeholder="Please provide a reason for this status change"></textarea>
                                    <small class="form-text text-muted">This note will be recorded in the status history</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description_notes" class="form-label">Description / Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description_notes') is-invalid @enderror"
                                          id="description_notes" name="description_notes" rows="3" required>{{ old('description_notes', $appointment->description_notes) }}</textarea>
                                @error('description_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                        </div>

                        <div class="col-md-6">
                          <div class="mb-3">
                              <label for="team_type_id" class="form-label">Team <span class="text-danger">*</span></label>
                              <select name="team_type_id" id="team_type_id" class="form-select @error('team_type_id') is-invalid @enderror">
                                  <option value="">Select Team</option>
                                  @foreach($teamTypes as $teamType)
                                      <option value="{{ $teamType->id }}" {{ old('team_type_id', $appointment->team_type_id) == $teamType->id ? 'selected' : '' }}>
                                          {{ $teamType->type_name }}
                                      </option>
                                  @endforeach
                              </select>
                              @error('team_type_id')
                                  <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                          </div>
                      </div>

                      <div class="col-md-6">
                          <div class="mb-3">
                              <label for="sub_team_type_id" class="form-label">Sub Team <span class="text-danger">*</span></label>
                              <select name="sub_team_type_id" id="sub_team_type_id"  class="form-select select2 @error('sub_team_type_id') is-invalid @enderror">
                                  <option value="">Select Sub Team</option>
                              </select>
                              @error('sub_team_type_id')
                                  <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="mb-3">
                              <label for="assigned_to" class="form-label">Assigned To (Technician)</label>
                              <select name="assigned_to" id="assigned_to" class="form-select select2 @error('assigned_to') is-invalid @enderror">
                                  <option value="">Select Sub Team first</option>
                                  @if($appointment->assigned_to && $appointment->assignee)
                                      <option value="{{ $appointment->assigned_to }}" selected>{{ $appointment->assignee->name }}</option>
                                  @endif
                              </select>
                              <small class="form-text text-muted">Only technicians in the selected Sub Team appear here. The mobile app's "My Appointments"/"My Outages" views match on this field.</small>
                              @error('assigned_to')
                                  <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                          </div>
                      </div>
                      <div class="col-md-6">
                        <label for="assigned_team_id" class="form-label">Assign Team</label>
                        <select name="assigned_team_id" id="assigned_team_id" class="form-select select2 @error('assigned_team_id') is-invalid @enderror">
                            <option value="">Select Team</option>
                            @if($appointment->assigned_team_id)
                                <option value="{{ $appointment->assigned_team_id }}" selected>{{ $appointment->assignedTeam->name ?? 'Unknown Team' }}</option>
                            @endif
                        </select>
                        <small class="form-text text-muted">Selecting a team will automatically update status to "Scheduled-Assigned Team"</small>
                        @error('assigned_team_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                        @if($appointment->status === 'Completed')
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    This appointment was completed on
                                    {{ $appointment->completed_date->format('M d, Y') }} at
                                    {{ $appointment->completed_time }} by
                                    {{ $appointment->closer->name ?? 'System' }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-xs" id="update-btn">
                            <i class="fas fa-save me-1"></i> Update Appointment
                        </button>
                        <a href="{{ route('appointment.appointments.show', $appointment->id) }}" class="btn btn-secondary btn-xs">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <div class="mt-2" id="submission-status" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-spinner fa-spin me-1"></i> Processing your request...
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this appointment? This action cannot be undone.</p>
                                <p class="mb-0"><strong>Ticket ID:</strong> {{ $appointment->appointment_ticket_id }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('appointment.appointments.destroy', $appointment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash me-1"></i> Delete Appointment
                                    </button>
                                </form>
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

