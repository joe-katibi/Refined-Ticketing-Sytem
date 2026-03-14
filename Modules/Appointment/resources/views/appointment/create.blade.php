@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Create Appointment')

@push('style')
<!-- Select2 CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2-bootstrap-5-theme.min.css') }}">
@endpush


@push('scripts')
<!-- Select2 JS -->
<script src="{{ asset('assets/vendor/libs/select2/select2.full.min.js') }}"></script>

<script>
// Simple test script - if you see this in console, scripts are loading
console.log('=== Scripts are loading! ===');
</script>
<script>
// Immediately log script loading
console.log('=== Appointment Create Script Loaded ===');

jQuery(document).ready(function($) {
    console.log('=== Document Ready Handler Executed ===');

    // Get references to form elements
    const $form = $('#appointment-form');
    const $appointmentType = $('#appointment_id');
    const $subType = $('#appointment_type_id');

    console.log('Form element:', $form.length ? 'Found' : 'Not found');
    console.log('Appointment Type element:', $appointmentType.length ? 'Found' : 'Not found');
    console.log('Sub-Type element:', $subType.length ? 'Found' : 'Not found');

    if (!$form.length || !$appointmentType.length || !$subType.length) {
        console.error('Required form elements are missing!');
        return;
    }

    // Remove any existing Select2 instances
    if ($subType.hasClass('select2-hidden-accessible')) {
        $subType.select2('destroy');
    }

    // Remove any existing Select2 containers
    $('.select-container .select2-container').remove();

    if (!$form.length || !$appointmentType.length || !$subType.length) {
        console.error('Required form elements are missing!');
        return;
    }

    // Initialize Select2 with error handling
    try {
        console.log('Initializing Select2...');
        
        // Initialize all select2 elements
        $('.select2').each(function() {
            var $this = $(this);
            
            // Check if Select2 is already initialized
            if ($this.hasClass('select2-hidden-accessible')) {
                $this.select2('destroy');
            }
            
            // Initialize with default options
            $this.select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });
        
        // Initialize status dropdown with color indicators
        $('#status').select2({
            theme: 'bootstrap-5',
            width: '100%',
            templateResult: formatStatusOption,
            templateSelection: formatStatusOption
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
        
        // Initialize the appointment type dropdown with Select2
        $appointmentType.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select Type',
            allowClear: true
        });

        // Initialize the sub-type dropdown with Select2 (initially disabled)
        $subType.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select Type First',
            allowClear: true,
            dropdownParent: $subType.parent()
        });

        // Disable initially
        $subType.prop('disabled', true);

        console.log('Select2 initialized successfully');
    } catch (e) {
        console.error('Error initializing Select2:', e);
    }

    // Log initial state
    console.log('Initial appointment_id value:', $appointmentType.val());
    console.log('Initial appointment_type_id value:', $subType.val());

    // Handle type change for appointment types
    function handleAppointmentTypeChange() {
        console.log('=== Appointment Type Changed Handler ===');

        const typeId = $appointmentType.val();
        console.log('Selected Type ID:', typeId);

        // Clear existing options
        $subType.empty();

        if (!typeId) {
            // No type selected, disable sub-type dropdown
            $subType.prop('disabled', true);
            $subType.trigger('change');
            return;
        }

        // Show loading state
        $subType.prop('disabled', true);
        $subType.append('<option value="">Loading...</option>');
        $subType.trigger('change');

        // Build URL and make AJAX request
        const url = `/appointments/get-sub-types/${typeId}`;

        console.log('Making AJAX request to URL:', url);
        
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                console.log('AJAX request starting...');
            },
            success: function(response) {
                console.log('AJAX Success! Response:', response);
                
                // Clear the dropdown
                $subType.empty();

                if (response && response.length > 0) {
                    console.log(`Found ${response.length} sub-types`);
                    
                    // Add default option
                    $subType.append('<option value="">Select Sub Type</option>');

                    // Add options from response
                    response.forEach(function(item) {
                        const displayName = item.sub_type_name || item.type_name || 'Unknown';
                        console.log(`Adding sub-type: ${displayName} (ID: ${item.id})`);
                        $subType.append(`<option value="${item.id}">${displayName}</option>`);
                    });

                    // Enable the dropdown
                    $subType.prop('disabled', false);
                } else {
                    console.log('No sub-types found in response');
                    // No options available
                    $subType.append('<option value="">No sub-types available</option>');
                    $subType.prop('disabled', true);
                }

                // Refresh Select2
                $subType.trigger('change');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                $subType.empty();
                $subType.append('<option value="">Error loading sub-types</option>');
                $subType.prop('disabled', true);
                $subType.trigger('change');
            }
        });
    }

    // Handle OLT change for slot loading
    function handleOltChange() {
        console.log('=== OLT Changed Handler ===');

        const oltId = $('#olt_id').val();
        const $slotSelect = $('#slot_id');
        console.log('Selected OLT ID:', oltId);

        // Clear existing slot options
        $slotSelect.empty();

        if (!oltId) {
            // No OLT selected, disable slot dropdown
            $slotSelect.prop('disabled', true);
            $slotSelect.append('<option value="">Select OLT First</option>');
            $slotSelect.trigger('change');
            return;
        }

        // Show loading state
        $slotSelect.prop('disabled', true);
        $slotSelect.append('<option value="">Loading slots...</option>');
        $slotSelect.trigger('change');

        // Build URL and make AJAX request
        const url = `/appointments/get-slots/${oltId}`;

        console.log('Making AJAX request to URL:', url);
        
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                console.log('Slot AJAX request starting...');
            },
            success: function(response) {
                console.log('Slot AJAX Success! Response:', response);
                
                // Clear the dropdown
                $slotSelect.empty();

                if (response && response.length > 0) {
                    console.log(`Found ${response.length} slots`);
                    
                    // Add default option
                    $slotSelect.append('<option value="">Select Slot</option>');

                    // Add options from response
                    response.forEach(function(slot) {
                        const displayName = slot.name || `Slot ${slot.slot_number}`;
                        console.log(`Adding slot: ${displayName} (ID: ${slot.id})`);
                        $slotSelect.append(`<option value="${slot.id}">${displayName}</option>`);
                    });

                    // Enable the dropdown
                    $slotSelect.prop('disabled', false);
                } else {
                    console.log('No slots found in response');
                    // No options available
                    $slotSelect.append('<option value="">No slots available</option>');
                    $slotSelect.prop('disabled', true);
                }

                // Refresh Select2
                $slotSelect.trigger('change');
            },
            error: function(xhr, status, error) {
                console.error('Slot AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                $slotSelect.empty();
                $slotSelect.append('<option value="">Error loading slots</option>');
                $slotSelect.prop('disabled', true);
                $slotSelect.trigger('change');
            }
        });
    }

    // Set up event listener for appointment type change
    $appointmentType.on('change', handleAppointmentTypeChange);

    // Set up event listener for OLT change
    $('#olt_id').on('change', handleOltChange);

    // Trigger change handler if there's an initial value
    if ($appointmentType.val()) {
        handleAppointmentTypeChange();
    }

    // Trigger OLT change handler if there's an initial value
    if ($('#olt_id').val()) {
        handleOltChange();
    }

    // Prevent duplicate form submissions
    $form.on('submit', function(e) {
        console.log('Form submission attempted');

        // If the form is already submitting, prevent another submission
        if ($(this).data('submitting')) {
            console.log('Preventing duplicate submission');
            e.preventDefault();
            return false;
        }

        // Check required fields
        let isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // If form is not valid, prevent submission
        if (!isValid) {
            console.log('Form validation failed');
            e.preventDefault();
            return false;
        }

        // Mark the form as submitting
        $(this).data('submitting', true);

        // Add a visual indicator that the form is submitting
        $(this).find('button[type="submit"]').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');

        console.log('Form submission proceeding');
    });

    // Log initial selected type if any
    var initialType = $('#appointment_id').val();
    if (initialType) {
        console.log('Initial type selected on page load:', initialType);
        $('#appointment_id').trigger('change');
    } else {
        console.log('No initial type selected');
    }

    console.log('Script initialization complete');
});
</script>
@endpush


@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />
    <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create New Appointment</h3>
                <div class="card-tools">
                    <a href="{{ route('appointment.appointments.index') }}" class="btn btn-secondary btn-xs">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('appointment.appointments.store') }}" method="POST" id="appointment-form">
                    @csrf
                    {{-- Hidden submission token to prevent duplicate submissions --}}
                    <input type="hidden" name="_submission_token" value="{{ $submissionToken ?? md5(uniqid(mt_rand(), true)) }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                       id="account_number" name="account_number"
                                       value="{{ old('account_number') }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="appointment_id" class="form-label">Appointment Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('appointment_id') is-invalid @enderror"
                                        id="appointment_id" name="appointment_id" required>
                                    <option value="">Select Type</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('appointment_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->type_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('appointment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="appointment_type_id" class="form-label">Sub Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('appointment_type_id') is-invalid @enderror"
                                        id="appointment_type_id" name="appointment_type_id" required >
                                    <option value="">Select Type First</option>
                                </select>
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
                                    <option value="">Select Priority</option>
                                    <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                                    <option value="Medium" {{ old('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select select2 @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->name }}" 
                                            {{ old('status') == $status->name ? 'selected' : '' }}
                                            data-color="{{ $status->color }}"
                                            data-badge-class="{{ $status->badge_class }}">
                                            {{ $status->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scheduled_date" class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                       id="scheduled_date" name="scheduled_date"
                                       value="{{ old('scheduled_date') }}" 
                                       min="{{ date('Y-m-d') }}" required>
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
                                       value="{{ old('scheduled_time') }}" required>
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
                                       value="{{ old('appointment_location') }}" required>
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
                                       value="{{ old('appointment_venue') }}" required>
                                @error('appointment_venue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="olt_id" class="form-label">OLT Name</label>
                                <select class="form-select select2 @error('olt_id') is-invalid @enderror"
                                        id="olt_id" name="olt_id">
                                    <option value="">Select OLT</option>
                                    @foreach($olts as $olt)
                                        <option value="{{ $olt->id }}" {{ old('olt_id') == $olt->id ? 'selected' : '' }}>
                                            {{ $olt->name }} ({{ $olt->location }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('olt_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slot_id" class="form-label">Slot Name</label>
                                <select class="form-select select2 @error('slot_id') is-invalid @enderror"
                                        id="slot_id" name="slot_id" disabled>
                                    <option value="">Select OLT First</option>
                                </select>
                                @error('slot_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description_notes" class="form-label">Description / Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description_notes') is-invalid @enderror"
                                          id="description_notes" name="description_notes"
                                          rows="4" required>{{ old('description_notes') }}</textarea>
                                @error('description_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-xs">
                            <i class="fas fa-save me-1"></i> Create Appointment
                        </button>
                        <a href="{{ route('appointment.appointments.index') }}" class="btn btn-secondary btn-xs">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

