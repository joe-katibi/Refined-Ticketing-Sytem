@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit Escalation')

@section('scripts')
<script>
    // Debug logging function
    function debugLog(message) {
        if (typeof console !== 'undefined' && console.log) {
            console.log('Escalation Form:', message);
        }
    }

    // Initialize functions before they're used
    function hideAllAppointmentTypeFields() {
        debugLog('Hiding all appointment type fields');
        var fields = ['supportFields', 'shiftingFields', 'installationFields', 'wifiExtenderFields'];

        fields.forEach(function(fieldId) {
            var field = document.getElementById(fieldId);
            if (field) {
                field.style.display = 'none';
                // Remove required attribute when hiding
                const inputs = field.querySelectorAll('input, textarea, select');
                inputs.forEach(input => input.required = false);
                debugLog('Hidden field: ' + fieldId);
            }
        });
    }

    function showHideAppointmentTypeFields() {
        debugLog('Showing/hiding appointment type fields');
        const appointmentTypeSelect = document.getElementById('appointment_type_id');
        const selectedOption = appointmentTypeSelect.selectedOptions[0];

        if (!selectedOption) {
            debugLog('No option selected');
            return;
        }

        hideAllAppointmentTypeFields();

        // Get the text content of the selected option
        const optionText = selectedOption.textContent.toLowerCase();
        const dataType = selectedOption.getAttribute('data-type') ? selectedOption.getAttribute('data-type').toLowerCase() : '';

        debugLog('Selected option text:', optionText);
        debugLog('Selected data-type:', dataType);

        // Determine which fields to show based on data-type or option text
        let fieldId = null;

        // Check data-type first
        if (dataType) {
            if (dataType.includes('wifi') || dataType.includes('extender')) {
                fieldId = 'wifiExtenderFields';
            } else if (dataType.includes('support')) {
                fieldId = 'supportFields';
            } else if (dataType.includes('install')) {
                fieldId = 'installationFields';
            } else if (dataType.includes('shift')) {
                fieldId = 'shiftingFields';
            }
        }

        // If no field ID found by data-type, try to determine by option text
        if (!fieldId) {
            if (optionText.includes('wifi') || optionText.includes('extender')) {
                fieldId = 'wifiExtenderFields';
            } else if (optionText.includes('support') || optionText.includes('los')) {
                fieldId = 'supportFields';
            } else if (optionText.includes('install') || optionText.includes('connection')) {
                fieldId = 'installationFields';
            } else if (optionText.includes('shift') || optionText.includes('relocation')) {
                fieldId = 'shiftingFields';
            }
        }

        // Show the appropriate fields
        if (fieldId) {
            const fields = document.getElementById(fieldId);
            if (fields) {
                fields.style.display = 'block';
                // Set required fields
                const requiredFields = fields.querySelectorAll('[required]');
                requiredFields.forEach(field => field.required = true);
                debugLog(`Showing fields for: ${fieldId}`);
            } else {
                debugLog(`Element with ID ${fieldId} not found`);
            }
        } else {
            debugLog('No matching field ID found for data-type:', dataType, 'and option text:', optionText);
        }
    }

    function showHideAppointmentFields() {
        debugLog('Showing/hiding appointment fields');
        var escalationType = document.getElementById('escalation_type');
        var appointmentFields = document.getElementById('appointmentFields');
        var appointmentTypeId = document.getElementById('appointment_type_id');

        if (!escalationType || !appointmentFields || !appointmentTypeId) {
            debugLog('Missing required elements');
            return;
        }

        var currentValue = escalationType.value;
        debugLog('Escalation type changed to: ' + currentValue);

        if (currentValue === 'appointment') {
            debugLog('Showing appointment fields');
            appointmentFields.style.display = 'block';
            appointmentTypeId.required = true;
            showHideAppointmentTypeFields();
        } else {
            debugLog('Hiding appointment fields');
            appointmentFields.style.display = 'none';
            appointmentTypeId.required = false;
            hideAllAppointmentTypeFields();
        }
    }

    // Function to load subcategories via AJAX
    function loadSubcategories(categoryId) {
        const subCategorySelect = document.getElementById('sub_category_id');

        if (!categoryId) {
            subCategorySelect.innerHTML = '<option value="">First select a category</option>';
            return;
        }

        // Show loading state
        subCategorySelect.innerHTML = '<option value="">Loading subcategories...</option>';
        subCategorySelect.disabled = true;

        // Fetch subcategories for the selected category
        fetch(`/list/subcategories/${categoryId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Clear existing options
                subCategorySelect.innerHTML = '<option value="">Select Sub Category</option>';

                // Add new options
                data.forEach(subcategory => {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.sub_category_name;

                    // Check if this was the previously selected value
                    const oldValue = '{{ old('sub_category_id', $escalation->sub_category_id) }}';
                    if (oldValue && oldValue == subcategory.id) {
                        option.selected = true;
                    }

                    subCategorySelect.appendChild(option);
                });

                subCategorySelect.disabled = false;
                debugLog('Subcategories loaded successfully');
            })
            .catch(error => {
                console.error('Error loading subcategories:', error);
                subCategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                subCategorySelect.disabled = false;
            });
    }

    // Initialize when page loads
    // Function to update the parent appointment ID when a sub-appointment is selected
    function updateParentAppointmentId() {
        const appointmentTypeSelect = document.getElementById('appointment_type_id');
        const appointmentIdInput = document.getElementById('appointment_id');
        const selectedOption = appointmentTypeSelect.options[appointmentTypeSelect.selectedIndex];

        if (selectedOption && selectedOption.dataset.parentId) {
            appointmentIdInput.value = selectedOption.dataset.parentId;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        debugLog('Page loaded - initializing');

        // If a category is already selected, load its subcategories
        const categorySelect = document.getElementById('category_id');
        if (categorySelect && categorySelect.value) {
            loadSubcategories(categorySelect.value);
        }

        // Add event listener for appointment type changes
        const appointmentTypeSelect = document.getElementById('appointment_type_id');
        if (appointmentTypeSelect) {
            appointmentTypeSelect.addEventListener('change', updateParentAppointmentId);
            // Initialize the parent appointment ID on page load
            updateParentAppointmentId();
        }

        // Set minimum dates and default times
        var today = new Date().toISOString().split('T')[0];

        // Set min date for all date fields
        var dateFields = ['support_date', 'shifting_date', 'installation_date', 'wifi_extender_date'];
        dateFields.forEach(function(fieldId) {
            var field = document.getElementById(fieldId);
            if (field) {
                field.min = today;
                debugLog('Set min date for ' + fieldId);
            }
        });

        // Set default time if not set
        var timeFields = [
            { id: 'support_time', defaultTime: '09:00' },
            { id: 'shifting_time', defaultTime: '09:00' },
            { id: 'installation_time', defaultTime: '09:00' },
            { id: 'wifi_extender_time', defaultTime: '09:00' }
        ];

        timeFields.forEach(function(timeField) {
            var field = document.getElementById(timeField.id);
            if (field && !field.value) {
                field.value = timeField.defaultTime;
                debugLog('Set default time for ' + timeField.id);
            }
        });

        // Initialize field visibility based on current values
        showHideAppointmentFields();

        // Set up event listeners
        var escalationType = document.getElementById('escalation_type');
        var appointmentTypeId = document.getElementById('appointment_type_id');

        if (escalationType) {
            debugLog('Adding change listener to escalation type');
            escalationType.addEventListener('change', showHideAppointmentFields);
        }

        if (appointmentTypeId) {
            debugLog('Adding change listener to appointment type');
            appointmentTypeId.addEventListener('change', showHideAppointmentTypeFields);
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            debugLog('Form submission validation');
            var escalationType = document.getElementById('escalation_type');
            var appointmentTypeId = document.getElementById('appointment_type_id');

            if (!escalationType || !appointmentTypeId) {
                debugLog('Missing form elements');
                return;
            }

            var currentValue = escalationType.value;
            debugLog('Escalation type on submit: ' + currentValue);

            if (currentValue === 'appointment') {
                var appointmentValue = appointmentTypeId.value;
                debugLog('Appointment type value: ' + appointmentValue);

                if (!appointmentValue) {
                    e.preventDefault();
                    alert('Please select an appointment type');
                    debugLog('Error: No appointment type selected');
                    return false;
                }

                var selectedOption = appointmentTypeId.options[appointmentTypeId.selectedIndex];
                if (selectedOption) {
                    var optionText = selectedOption.text.toLowerCase();
                    debugLog('Selected appointment type: ' + optionText);

                    if (optionText.includes('shift') || optionText.includes('relocation')) {
                        var shiftingDate = document.getElementById('shifting_date');
                        var shiftingAddress = document.getElementById('shifting_address');

                        if (!shiftingDate || !shiftingAddress) {
                            debugLog('Missing shifting fields');
                            return;
                        }

                        if (!shiftingDate.value) {
                            e.preventDefault();
                            alert('Please select a shifting date');
                            debugLog('Error: No shifting date selected');
                            return false;
                        }
                        if (!shiftingAddress.value.trim()) {
                            e.preventDefault();
                            alert('Please enter the new address');
                            debugLog('Error: No address entered');
                            return false;
                        }
                    } else if (optionText.includes('install') || optionText.includes('connection')) {
                        var installationDate = document.getElementById('installation_date');
                        if (!installationDate) {
                            debugLog('Missing installation date field');
                            return;
                        }

                        if (!installationDate.value) {
                            e.preventDefault();
                            alert('Please select an installation date');
                            debugLog('Error: No installation date selected');
                            return false;
                        }
                    } else if (optionText.includes('wifi extender')) {
                        var wifiExtenderDate = document.getElementById('wifi_extender_date');
                        var wifiExtenderAddress = document.getElementById('wifi_extender_address');

                        if (!wifiExtenderDate || !wifiExtenderAddress) {
                            debugLog('Missing WiFi extender fields');
                            return;
                        }

                        if (!wifiExtenderDate.value) {
                            e.preventDefault();
                            alert('Please select a WiFi extender date');
                            debugLog('Error: No WiFi extender date selected');
                            return false;
                        }
                        if (!wifiExtenderAddress.value.trim()) {
                            e.preventDefault();
                            alert('Please enter the WiFi extender address');
                            debugLog('Error: No WiFi extender address entered');
                            return false;
                        }
                    }
                }
            }

            debugLog('Form validation passed');
            return true;
        });
    });


</script>
@endsection

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Edit Escalation</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('escalations.update', $escalation) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- First Row -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ticket_id">Ticket ID <span class="text-danger">*</span></label>
                                    <input type="text" name="ticket_id" id="ticket_id" class="form-control @error('ticket_id') is-invalid @enderror" value="{{ old('ticket_id', $escalation->ticket_id) }}" required>
                                    @error('ticket_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_number">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="account_number" id="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number', $escalation->account_number) }}" required>
                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required onchange="loadSubcategories(this.value)">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $escalation->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sub_category_id">Sub Category <span class="text-danger">*</span></label>
                                    <select name="sub_category_id" id="sub_category_id" class="form-control @error('sub_category_id') is-invalid @enderror" required>
                                        <option value="">First select a category</option>
                                        @if(isset($subcategories) && $subcategories->isNotEmpty())
                                            @foreach($subcategories as $subcategory)
                                                <option value="{{ $subcategory->id }}" {{ old('sub_category_id', $escalation->sub_category_id) == $subcategory->id ? 'selected' : '' }}>
                                                    {{ $subcategory->sub_category_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('sub_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sub_department_id">Sub Department <span class="text-danger">*</span></label>
                                    <select name="sub_department_id" id="sub_department_id" class="form-control @error('sub_department_id') is-invalid @enderror" required>
                                        <option value="">Select Sub Department</option>
                                        @foreach($subDepartments as $subDepartment)
                                            <option value="{{ $subDepartment->id }}" {{ old('sub_department_id', $escalation->sub_department_id) == $subDepartment->id ? 'selected' : '' }}>{{ $subDepartment->sub_department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('sub_department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="priority">Priority <span class="text-danger">*</span></label>
                                    <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                        <option value="">Select Priority</option>
                                        <option value="low" {{ old('priority', $escalation->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', $escalation->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority', $escalation->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description Field -->
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $escalation->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remaining Fields -->
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="">Select Status</option>
                                <option value="Scheduled-Open" {{ old('status', $escalation->status) == 'Scheduled-Open' ? 'selected' : '' }}>Scheduled-Open</option>
                                <option value="Scheduled-Closed" {{ old('status', $escalation->status) == 'Scheduled-Closed' ? 'selected' : '' }}>Scheduled-Closed</option>
                                <option value="Scheduled-Assigned Team" {{ old('status', $escalation->status) == 'Scheduled-Assigned Team' ? 'selected' : '' }}>Scheduled-Assigned Team</option>
                                <option value="Escalated-NOC" {{ old('status', $escalation->status) == 'Escalated-NOC' ? 'selected' : '' }}>Escalated-NOC</option>
                                <option value="Escalated-Open " {{ old('status', $escalation->status) == 'Escalated-Open' ? 'selected' : '' }}>Escalated-Open</option>
                                <option value="Escalated-Closed" {{ old('status', $escalation->status) == 'Escalated-Closed' ? 'selected' : '' }}>Escalated-Closed</option>
                                <option value="Escalated-Infrastructure" {{ old('status', $escalation->status) == 'Escalated-Infrastructure' ? 'selected' : '' }}>Escalated-Infrastructure</option>
                                <option value="Support-Post-Install" {{ old('status', $escalation->status) == 'Support-Post-Install' ? 'selected' : '' }}>Support-Post-Install</option>
                                <option value="Cancelled" {{ old('status', $escalation->status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="Rescheduled" {{ old('status', $escalation->status) == 'Rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="escalation_type">Escalation Type <span class="text-danger">*</span></label>
                            <select name="escalation_type" id="escalation_type" class="form-control @error('escalation_type') is-invalid @enderror" required onchange="showHideAppointmentFields()">
                                <option value="">Select Escalation Type</option>
                                <option value="no_appointment" {{ old('escalation_type', $escalation->escalation_type) == 'no_appointment' ? 'selected' : '' }}>Escalation - No Appointment</option>
                                <option value="appointment" {{ old('escalation_type', $escalation->escalation_type) == 'appointment' ? 'selected' : '' }}>Escalation - Appointment</option>
                            </select>
                            @error('escalation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="appointmentFields" style="display: none;">
                            <div class="form-group">
                                <label for="appointment_type_id">Appointment Type <span class="text-danger">*</span></label>
                                <input type="hidden" name="appointment_id" id="appointment_id" class="form-control @error('appointment_id') is-invalid @enderror" value="{{ old('appointment_id', $escalation->appointment_id ?? '') }}" >
                                <select name="appointment_type_id" id="appointment_type_id" class="form-control @error('appointment_type_id') is-invalid @enderror" onchange="showHideAppointmentTypeFields()">
                                    <option value="">Select Appointment Type</option>
                                    @php
                                        // Debug: Log available appointment types
                                        \Log::info('Available Appointment Types in View:', [
                                            'types' => $appointmentTypes->map(function($type) {
                                                return [
                                                    'id' => $type->id,
                                                    'name' => $type->type_name,
                                                    'has_subtypes' => $type->subTypes->isNotEmpty()
                                                ];
                                            })->toArray()
                                        ]);
                                    @endphp
                                    @foreach($appointmentTypes as $type)
                                        @if($type->subTypes->isNotEmpty())
                                            <optgroup label="{{ $type->type_name }}">
                                                @foreach($type->subTypes as $subType)
                                                    @php
                                                        $isSelected = old('appointment_type_id', $escalation->appointment_type_id) == $subType->id;
                                                        \Log::info('Subtype Option:', [
                                                            'type_id' => $type->id,
                                                            'subtype_id' => $subType->id,
                                                            'name' => $subType->sub_type_name,
                                                            'is_selected' => $isSelected
                                                        ]);
                                                    @endphp
                                                    <option value="{{ $subType->id }}"
                                                            data-type="{{ strtolower($type->type_name) }}"
                                                            data-parent-id="{{ $type->id }}"
                                                            {{ $isSelected ? 'selected' : '' }}>
                                                        {{ $subType->sub_type_name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @else
                                            @php
                                                $isSelected = old('appointment_type_id', $escalation->appointment_type_id) == $type->id;
                                                \Log::info('Type Option:', [
                                                    'type_id' => $type->id,
                                                    'name' => $type->type_name,
                                                    'is_selected' => $isSelected
                                                ]);
                                            @endphp
                                            <option value="{{ $type->id }}"
                                                    data-type="{{ strtolower($type->type_name) }}"
                                                    {{ $isSelected ? 'selected' : '' }}>
                                                {{ $type->type_name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('appointment_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Support Fields -->
                            <div id="supportFields" class="appointment-type-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="support_date">Support Date <span class="text-danger">*</span></label>
                                            <input type="date" name="support_date" id="support_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('support_date', $escalation->support_date ?? '') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="support_time">Support Time <span class="text-danger">*</span></label>
                                            <input type="time" name="support_time" id="support_time" class="form-control" value="{{ old('support_time', $escalation->support_time ?? '09:00') }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="support_address">Support Address <span class="text-danger">*</span></label>
                                    <textarea name="support_address" id="support_address" class="form-control" rows="2" placeholder="Enter the address for support" required>{{ old('support_address', $escalation->support_address ?? '') }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="support_notes">Support Notes</label>
                                    <textarea name="support_notes" id="support_notes" class="form-control" rows="2" placeholder="Enter any additional notes about the support appointment">{{ old('support_notes', $escalation->support_notes ?? '') }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="support_olt_id">OLT <i class="bx bx-info-circle" title="Select OLT from Outages module"></i></label>
                                            <select name="olt_id" id="support_olt_id" class="form-control" onchange="loadOltSlots(this.value, 'support_slot_id')">
                                                <option value="">Select OLT</option>
                                                @foreach($olts as $olt)
                                                    <option value="{{ $olt->id }}" 
                                                            data-vendor="{{ $olt->vendor }}" 
                                                            data-location="{{ $olt->location }}"
                                                            {{ old('olt_id', $escalation->olt_id) == $olt->id ? 'selected' : '' }}>
                                                        {{ $olt->name }} - {{ $olt->vendor }} ({{ $olt->location }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="support_slot_id">Slot <i class="bx bx-info-circle" title="Select slot based on OLT"></i></label>
                                            <select name="slot_id" id="support_slot_id" class="form-control" disabled>
                                                <option value="">Select OLT first</option>
                                                @if($slots->count() > 0)
                                                    @foreach($slots as $slot)
                                                        <option value="{{ $slot->id }}"
                                                                data-slot-number="{{ $slot->slot_number }}"
                                                                data-slot-type="{{ $slot->slot_type }}"
                                                                {{ old('slot_id', $escalation->slot_id) == $slot->id ? 'selected' : '' }}>
                                                            Slot {{ $slot->slot_number }} ({{ $slot->slot_type }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Shifting Fields -->
                            <div id="shiftingFields" class="appointment-type-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="shifting_date">Shifting Date <span class="text-danger">*</span></label>
                                            <input type="date" name="shifting_date" id="shifting_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('shifting_date', $escalation->shifting_date ?? '') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="shifting_time">Shifting Time <span class="text-danger">*</span></label>
                                            <input type="time" name="shifting_time" id="shifting_time" class="form-control" value="{{ old('shifting_time', $escalation->shifting_time ?? '09:00') }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="shifting_address">New Address <span class="text-danger">*</span></label>
                                    <textarea name="shifting_address" id="shifting_address" class="form-control" rows="2" placeholder="Enter the new address for shifting" required>{{ old('shifting_address', $escalation->shifting_address ?? '') }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="shifting_notes">Shifting Notes</label>
                                    <textarea name="shifting_notes" id="shifting_notes" class="form-control" rows="2" placeholder="Enter any additional notes about the shifting">{{ old('shifting_notes', $escalation->shifting_notes ?? '') }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="shifting_olt_id">OLT <i class="bx bx-info-circle" title="Select OLT from Outages module"></i></label>
                                            <select name="olt_id" id="shifting_olt_id" class="form-control" onchange="loadOltSlots(this.value, 'shifting_slot_id')">
                                                <option value="">Select OLT</option>
                                                @foreach($olts as $olt)
                                                    <option value="{{ $olt->id }}" 
                                                            data-vendor="{{ $olt->vendor }}" 
                                                            data-location="{{ $olt->location }}"
                                                            {{ old('olt_id', $escalation->olt_id) == $olt->id ? 'selected' : '' }}>
                                                        {{ $olt->name }} - {{ $olt->vendor }} ({{ $olt->location }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="shifting_slot_id">Slot <i class="bx bx-info-circle" title="Select slot based on OLT"></i></label>
                                            <select name="slot_id" id="shifting_slot_id" class="form-control" disabled>
                                                <option value="">Select OLT first</option>
                                                @if($slots->count() > 0)
                                                    @foreach($slots as $slot)
                                                        <option value="{{ $slot->id }}"
                                                                data-slot-number="{{ $slot->slot_number }}"
                                                                data-slot-type="{{ $slot->slot_type }}"
                                                                {{ old('slot_id', $escalation->slot_id) == $slot->id ? 'selected' : '' }}>
                                                            Slot {{ $slot->slot_number }} ({{ $slot->slot_type }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Installation Fields -->
                            <div id="installationFields" class="appointment-type-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="installation_date">Installation Date <span class="text-danger">*</span></label>
                                            <input type="date" name="installation_date" id="installation_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('installation_date', $escalation->installation_date ?? '') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="installation_time">Installation Time <span class="text-danger">*</span></label>
                                            <input type="time" name="installation_time" id="installation_time" class="form-control" value="{{ old('installation_time', $escalation->installation_time ?? '09:00') }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="installation_address">Installation Address <span class="text-danger">*</span></label>
                                    <textarea name="installation_address" id="installation_address" class="form-control" rows="2" placeholder="Enter the installation address" required>{{ old('installation_address', $escalation->installation_address ?? '') }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="installation_notes">Installation Notes</label>
                                    <textarea name="installation_notes" id="installation_notes" class="form-control" rows="2" placeholder="Enter any additional notes about the installation">{{ old('installation_notes', $escalation->installation_notes ?? '') }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="installation_olt_id">OLT <i class="bx bx-info-circle" title="Select OLT from Outages module"></i></label>
                                            <select name="olt_id" id="installation_olt_id" class="form-control" onchange="loadOltSlots(this.value, 'installation_slot_id')">
                                                <option value="">Select OLT</option>
                                                @foreach($olts as $olt)
                                                    <option value="{{ $olt->id }}" 
                                                            data-vendor="{{ $olt->vendor }}" 
                                                            data-location="{{ $olt->location }}"
                                                            {{ old('olt_id', $escalation->olt_id) == $olt->id ? 'selected' : '' }}>
                                                        {{ $olt->name }} - {{ $olt->vendor }} ({{ $olt->location }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="installation_slot_id">Slot <i class="bx bx-info-circle" title="Select slot based on OLT"></i></label>
                                            <select name="slot_id" id="installation_slot_id" class="form-control" disabled>
                                                <option value="">Select OLT first</option>
                                                @if($slots->count() > 0)
                                                    @foreach($slots as $slot)
                                                        <option value="{{ $slot->id }}"
                                                                data-slot-number="{{ $slot->slot_number }}"
                                                                data-slot-type="{{ $slot->slot_type }}"
                                                                {{ old('slot_id', $escalation->slot_id) == $slot->id ? 'selected' : '' }}>
                                                            Slot {{ $slot->slot_number }} ({{ $slot->slot_type }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- WiFi Extender Fields -->
                            <div id="wifiExtenderFields" class="appointment-type-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="wifi_extender_date">WiFi Extender Date <span class="text-danger">*</span></label>
                                            <input type="date" name="wifi_extender_date" id="wifi_extender_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('wifi_extender_date', $escalation->wifi_extender_date ?? '') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="wifi_extender_time">WiFi Extender Time <span class="text-danger">*</span></label>
                                            <input type="time" name="wifi_extender_time" id="wifi_extender_time" class="form-control" value="{{ old('wifi_extender_time', $escalation->wifi_extender_time ?? '09:00') }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="wifi_extender_address">WiFi Extender Address <span class="text-danger">*</span></label>
                                    <textarea name="wifi_extender_address" id="wifi_extender_address" class="form-control" rows="2" placeholder="Enter the address for WiFi extender installation" required>{{ old('wifi_extender_address', $escalation->wifi_extender_address ?? '') }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="wifi_extender_notes">WiFi Extender Notes</label>
                                    <textarea name="wifi_extender_notes" id="wifi_extender_notes" class="form-control" rows="2" placeholder="Enter any additional notes about the WiFi extender installation">{{ old('wifi_extender_notes', $escalation->wifi_extender_notes ?? '') }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="wifi_extender_olt_id">OLT <i class="bx bx-info-circle" title="Select OLT from Outages module"></i></label>
                                            <select name="olt_id" id="wifi_extender_olt_id" class="form-control" onchange="loadOltSlots(this.value, 'wifi_extender_slot_id')">
                                                <option value="">Select OLT</option>
                                                @foreach($olts as $olt)
                                                    <option value="{{ $olt->id }}" 
                                                            data-vendor="{{ $olt->vendor }}" 
                                                            data-location="{{ $olt->location }}"
                                                            {{ old('olt_id', $escalation->olt_id) == $olt->id ? 'selected' : '' }}>
                                                        {{ $olt->name }} - {{ $olt->vendor }} ({{ $olt->location }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="wifi_extender_slot_id">Slot <i class="bx bx-info-circle" title="Select slot based on OLT"></i></label>
                                            <select name="slot_id" id="wifi_extender_slot_id" class="form-control" disabled>
                                                <option value="">Select OLT first</option>
                                                @if($slots->count() > 0)
                                                    @foreach($slots as $slot)
                                                        <option value="{{ $slot->id }}"
                                                                data-slot-number="{{ $slot->slot_number }}"
                                                                data-slot-type="{{ $slot->slot_type }}"
                                                                {{ old('slot_id', $escalation->slot_id) == $slot->id ? 'selected' : '' }}>
                                                            Slot {{ $slot->slot_number }} ({{ $slot->slot_type }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="fas fa-save"></i> Update Escalation
                            </button>
                            <a href="{{ route('escalations.index') }}" class="btn btn-secondary btn-xs">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>



                </div>
            </div>
        </div>
    </div>
</div>
 @yield('scripts')

<script>
// Function to load slots based on selected OLT
function loadOltSlots(oltId, slotSelectId) {
    const slotSelect = document.getElementById(slotSelectId);
    
    // Reset slot dropdown
    slotSelect.innerHTML = '<option value="">Loading slots...</option>';
    slotSelect.disabled = true;
    
    if (!oltId) {
        slotSelect.innerHTML = '<option value="">Select OLT first</option>';
        return;
    }
    
    // Make AJAX request to get slots
    fetch(`{{ url('/escalations/olts') }}/${oltId}/slots`)
        .then(response => response.json())
        .then(data => {
            slotSelect.innerHTML = '<option value="">Select Slot</option>';
            
            if (data.success && data.slots.length > 0) {
                data.slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.id;
                    option.textContent = slot.display_name;
                    option.setAttribute('data-slot-number', slot.slot_number);
                    option.setAttribute('data-slot-type', slot.slot_type);
                    
                    // Check if this slot should be selected (for edit form)
                    const currentSlotId = '{{ old("slot_id", $escalation->slot_id ?? "") }}';
                    if (slot.id == currentSlotId) {
                        option.selected = true;
                    }
                    
                    slotSelect.appendChild(option);
                });
                slotSelect.disabled = false;
            } else {
                slotSelect.innerHTML = '<option value="">No slots available</option>';
            }
        })
        .catch(error => {
            console.error('Error loading slots:', error);
            slotSelect.innerHTML = '<option value="">Error loading slots</option>';
        });
}

// Initialize slots on page load if OLT is already selected
document.addEventListener('DOMContentLoaded', function() {
    const supportOltSelect = document.getElementById('support_olt_id');
    if (supportOltSelect && supportOltSelect.value) {
        loadOltSlots(supportOltSelect.value, 'support_slot_id');
    }
    
    // Also handle other appointment type OLT selects if they exist
    const oltSelects = ['installation_olt_id', 'shifting_olt_id', 'wifi_extender_olt_id'];
    oltSelects.forEach(selectId => {
        const oltSelect = document.getElementById(selectId);
        if (oltSelect && oltSelect.value) {
            const slotSelectId = selectId.replace('olt_id', 'slot_id');
            loadOltSlots(oltSelect.value, slotSelectId);
        }
    });
});

// Add loading indicator styles
const style = document.createElement('style');
style.textContent = `
    .loading-slots {
        background-image: url('data:image/svg+xml;charset=utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="%23666" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path fill="%23666" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"><animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg>');
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 16px;
    }
`;
document.head.appendChild(style);
</script>

@endsection

