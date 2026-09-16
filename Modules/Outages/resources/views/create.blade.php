@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Create New Outage')

@section('content')
<div class="container-xxXl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">
                    <span class="text-muted fw-light">Outages /</span> Create New Outage
                </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('outages.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i> Back to Outages
                    </a>
                    <a href="{{ route('outage-dashboard.index') }}" class="btn btn-info btn-xs">
                        <i class="bx bx-bar-chart-alt-2 me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Card Form -->
    <div class="row">
        <div class="col-lg-12 col-xl-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="bx bx-plus-circle me-2"></i>Create New Outage
                    </h4>
                    <p class="card-text mb-0 mt-1 opacity-75">Fill in the details below to create a new outage record</p>
                </div>

                <form action="{{ route('outages.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Basic Information Section -->
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="bx bx-info-circle me-2"></i>Basic Information
                                </h5>
                            </div>

                            <div class="col-md-4">
                                <label for="ticket_type" class="form-label fw-semibold">Ticket Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('ticket_type') is-invalid @enderror"
                                        id="ticket_type" name="ticket_type" required>
                                    <option value="">Select Ticket Type</option>
                                    <option value="regular" {{ old('ticket_type') == 'regular' ? 'selected' : '' }}>Regular Outage (OUT-n)</option>
                                    <option value="emergency" {{ old('ticket_type') == 'emergency' ? 'selected' : '' }}>Emergency Outage (EMO-n)</option>
                                    <option value="planned_maintenance" {{ old('ticket_type') == 'planned_maintenance' ? 'selected' : '' }}>Planned Maintenance (PLM-n)</option>
                                </select>
                                @error('ticket_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="title" class="form-label fw-semibold">Outage Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}"
                                       placeholder="Enter a descriptive title for the outage" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    @php
                                        $statusOptions = \Modules\Outages\Models\Outage::getStatusOptions();
                                    @endphp
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">For planned maintenance, status will be auto-set to 'Scheduled Maintenance'</small>
                            </div>

                            <div class="col-md-4">
                                <label for="impact" class="form-label fw-semibold">Impact <span class="text-danger">*</span></label>
                                <select class="form-select @error('impact') is-invalid @enderror"
                                        id="impact" name="impact" required>
                                    <option value="">Select Impact</option>
                                    <option value="Low" {{ old('impact') == 'Low' ? 'selected' : '' }}>Low</option>
                                    <option value="Medium" {{ old('impact') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="High" {{ old('impact') == 'High' ? 'selected' : '' }}>High</option>
                                    <option value="Critical" {{ old('impact') == 'Critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                @error('impact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="urgency" class="form-label fw-semibold">Urgency <span class="text-danger">*</span></label>
                                <select class="form-select @error('urgency') is-invalid @enderror"
                                        id="urgency" name="urgency" required>
                                    <option value="">Select Urgency</option>
                                    <option value="Low" {{ old('urgency') == 'Low' ? 'selected' : '' }}>Low</option>
                                    <option value="Medium" {{ old('urgency') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="High" {{ old('urgency') == 'High' ? 'selected' : '' }}>High</option>
                                    <option value="Critical" {{ old('urgency') == 'Critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                @error('urgency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Priority will be auto-calculated based on Impact and Urgency</small>
                            </div>

                            <div class="col-md-4">
                                <label for="priority" class="form-label fw-semibold">Priority <span class="text-muted">(Auto-calculated)</span></label>
                                <input type="text" class="form-control" id="priority_display" readonly
                                       placeholder="Will be calculated automatically">
                                <input type="hidden" name="priority" id="priority">
                            </div>

                            <div class="col-md-4">
                                <label for="total_customers_affected" class="form-label fw-semibold">Total Customers Affected</label>
                                <input type="number" class="form-control @error('total_customers_affected') is-invalid @enderror"
                                       id="total_customers_affected" name="total_customers_affected"
                                       placeholder="Enter number of affected customers" min="0"
                                       value="{{ old('total_customers_affected') }}">
                                @error('total_customers_affected')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Estimated number of customers impacted by this outage</small>
                            </div>

                            <div class="col-md-4">
                                <label for="start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                       id="start_date" name="start_date"
                                       value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="start_time" class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                       id="start_time" name="start_time"
                                       value="{{ old('start_time', date('H:i')) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Describe the outage details..." required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="resolution_notes" class="form-label fw-semibold">Resolution Notes</label>
                                <textarea class="form-control @error('resolution_notes') is-invalid @enderror"
                                          id="resolution_notes" name="resolution_notes" rows="3"
                                          placeholder="Add any resolution notes or initial observations...">{{ old('resolution_notes') }}</textarea>
                                @error('resolution_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Technical Information Section -->
                            <div class="col-12 mt-5">
                                <h5 class="text-info border-bottom pb-2 mb-3">
                                    <i class="bx bx-cog me-2"></i>Technical Information
                                </h5>
                            </div>

                            <div class="col-md-4">
                                <label for="olt_id" class="form-label fw-semibold">OLT <span class="text-danger">*</span></label>
                                <select class="form-select @error('olt_id') is-invalid @enderror" id="olt_id" name="olt_id" required>
                                    <option value="">Select OLT</option>
                                    @foreach($olts as $olt)
                                        <option value="{{ $olt->id }}" {{ old('olt_id') == $olt->id ? 'selected' : '' }}
                                                data-ip="{{ $olt->ip_address }}" data-location="{{ $olt->location }}">
                                            {{ $olt->name }} ({{ $olt->ip_address }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('olt_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            <div class="col-md-4">
                                <label for="slot_id" class="form-label fw-semibold">Slot <span class="text-danger">*</span></label>
                                <select class="form-select @error('slot_id') is-invalid @enderror" id="slot_id" name="slot_id" required disabled>
                                    <option value="">Select OLT first</option>
                                </select>
                                @error('slot_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            <div class="col-md-4">
                                <label for="port_id" class="form-label fw-semibold">PON Port <span class="text-danger">*</span></label>
                                <select class="form-select @error('port_id') is-invalid @enderror" id="port_id" name="port_id" required disabled>
                                    <option value="">Select slot first</option>
                                </select>
                                @error('port_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            <!-- Impact Information Section -->
                            <div class="col-12 mt-5">
                                <h5 class="text-warning border-bottom pb-2 mb-3">
                                    <i class="bx bx-error-alt me-2"></i>Impact Information
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label for="affected_areas" class="form-label fw-semibold">Affected Areas</label>
                                <select class="form-select @error('affected_areas') is-invalid @enderror"
                                        id="affected_areas" name="affected_areas[]" multiple size="4">
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}"
                                                {{ in_array($area->id, old('affected_areas', [])) ? 'selected' : '' }}>
                                            {{ $area->area_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Hold Ctrl/Cmd to select multiple areas</div>
                                @error('affected_areas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="affected_services" class="form-label fw-semibold">Affected Services</label>
                                <select class="form-select @error('affected_services') is-invalid @enderror"
                                        id="affected_services" name="affected_services[]" multiple size="4">
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}"
                                                {{ in_array($service->id, old('affected_services', [])) ? 'selected' : '' }}>
                                            {{ $service->service_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Hold Ctrl/Cmd to select multiple services</div>
                                @error('affected_services')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Assignment Information Section -->
                            <div class="col-12 mt-5">
                                <h5 class="text-success border-bottom pb-2 mb-3">
                                    <i class="bx bx-user-check me-2"></i>Assignment Information
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label for="assigned_team_type" class="form-label fw-semibold">Assigned Team Type</label>
                                <select class="form-select @error('assigned_team_id') is-invalid @enderror"
                                        id="assigned_team_type" name="assigned_team_type">
                                    <option value="">Select Team Type</option>
                                    @foreach($teamTypes as $teamType)
                                        <option value="{{ $teamType->id }}" {{ old('assigned_team_type') == $teamType->id ? 'selected' : '' }}>
                                            {{ $teamType->type_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_team_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="assigned_sub_team_type_id" class="form-label fw-semibold">Assigned Sub Team Type</label>
                                <select class="form-select @error('assigned_sub_team_type_id') is-invalid @enderror"
                                        id="assigned_sub_team_type_id" name="assigned_sub_team_type_id" disabled>
                                    <option value="">Select Team Type first</option>
                                </select>
                                @error('assigned_sub_team_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="assigned_to" class="form-label fw-semibold">Assigned To</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror"
                                        id="assigned_to" name="assigned_to" disabled>
                                    <option value="">Select Sub Team Type first</option>
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('outages.index') }}" class="btn btn-outline-secondary btn-xs">
                                <i class="bx bx-x me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-save me-2"></i>Create Outage
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Custom styles for form elements */
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.form-text {
    font-size: 0.875em;
    color: #6c757d;
}
/* Multi-select styling */
.form-select[multiple] {
    min-height: 120px;
}
</style>
@endpush

@push('scripts')
<script>
$(function () {

    // Priority calculation function
    function calculatePriority() {
        var impact = $('#impact').val().toLowerCase();
        var urgency = $('#urgency').val().toLowerCase();
        var priority = 'Low';

        if (impact === 'critical' && urgency === 'critical') {
            priority = 'Critical';
        } else if ((impact === 'critical' && urgency === 'high') ||
                   (impact === 'high' && urgency === 'critical')) {
            priority = 'Critical';
        } else if ((impact === 'high' && urgency === 'high') ||
                   (impact === 'critical' && urgency === 'medium') ||
                   (impact === 'medium' && urgency === 'critical')) {
            priority = 'High';
        } else if ((impact === 'high' && urgency === 'medium') ||
                   (impact === 'medium' && urgency === 'high') ||
                   (impact === 'critical' && urgency === 'low') ||
                   (impact === 'low' && urgency === 'critical')) {
            priority = 'Medium';
        }

        $('#priority').val(priority);
        $('#priority_display').val(priority);

        // Update display color based on priority
        var displayField = $('#priority_display');
        displayField.removeClass('text-success text-warning text-danger text-info');

        switch(priority) {
            case 'Critical':
                displayField.addClass('text-danger fw-bold');
                break;
            case 'High':
                displayField.addClass('text-warning fw-bold');
                break;
            case 'Medium':
                displayField.addClass('text-info');
                break;
            default:
                displayField.addClass('text-success');
        }
    }

    // Calculate priority when impact or urgency changes
    var impactManuallySet = false;
    $('#impact').on('change', function() {
        // A real user interaction fires 'change'; the auto-suggestion below sets
        // .val() via script, which does not dispatch 'change', so this only
        // captures the user's own choice.
        impactManuallySet = true;
        $('#impact-suggestion').remove();
    });
    $('#impact, #urgency').change(function() {
        if ($('#impact').val() && $('#urgency').val()) {
            calculatePriority();
        }
    });

    // Filter users based on selected team
    $('#assigned_team').change(function() {
        var teamId = $(this).val();
        var userSelect = $('#assigned_to');

        if (teamId) {
            // You can implement AJAX call here to filter users by team
            // For now, we'll show all users
        }
    });

    // Set priority color based on selection
    $('#priority').change(function() {
        var priority = $(this).val();
        var $this = $(this);

        $this.removeClass('border-info border-warning border-danger');

        switch(priority) {
            case 'Critical':
                $this.addClass('border-danger');
                break;
            case 'High':
                $this.addClass('border-warning');
                break;
            case 'Medium':
                $this.addClass('border-info');
                break;
            default:
                break;
        }
    });

    // Auto-populate current date/time for start_time if empty
    if (!$('#start_time').val()) {
        var now = new Date();
        var year = now.getFullYear();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var day = String(now.getDate()).padStart(2, '0');
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');

        $('#start_time').val(year + '-' + month + '-' + day + 'T' + hours + ':' + minutes);
    }

    // Cascading Dropdowns for OLT -> Slot -> Port
    $('#olt_id').change(function() {
        const oltId = $(this).val();
        const $slotSelect = $('#slot_id');
        const $portSelect = $('#port_id');
        const $oltInfo = $('#olt-info');
        const $slotInfo = $('#slot-info');
        const $portInfo = $('#port-info');

        // Reset dependent dropdowns
        $slotSelect.html('<option value="">Loading slots...</option>').prop('disabled', true);
        $portSelect.html('<option value="">Select slot first</option>').prop('disabled', true);
        $slotInfo.text('');
        $portInfo.text('');

        if (oltId) {
            // Show OLT info
            const selectedOption = $(this).find('option:selected');
            const location = selectedOption.data('location');
            if (location) {
                $oltInfo.html(`<i class="bx bx-map-pin me-1"></i>Location: ${location}`);
            }

            // Fetch slots for selected OLT
            $.ajax({
                url: `/api/outages/olts/${oltId}/slots`,
                method: 'GET',
                success: function(response) {
                    if (response.success && response.slots.length > 0) {
                        let options = '<option value="">Select Slot</option>';
                        response.slots.forEach(function(slot) {
                            options += `<option value="${slot.id}" data-type="${slot.slot_type}" data-number="${slot.slot_number}">${slot.display_name}</option>`;
                        });
                        $slotSelect.html(options).prop('disabled', false);
                    } else {
                        $slotSelect.html('<option value="">No active slots available</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching slots:', xhr);
                    $slotSelect.html('<option value="">Error loading slots</option>');
                }
            });
        } else {
            $slotSelect.html('<option value="">Select OLT first</option>');
            $oltInfo.text('');
        }
    });

    $('#slot_id').change(function() {
        const slotId = $(this).val();
        const $portSelect = $('#port_id');
        const $slotInfo = $('#slot-info');
        const $portInfo = $('#port-info');

        // Reset port dropdown
        $portSelect.html('<option value="">Loading ports...</option>').prop('disabled', true);
        $portInfo.text('');

        if (slotId) {
            // Show slot info
            const selectedOption = $(this).find('option:selected');
            const slotType = selectedOption.data('type');
            const slotNumber = selectedOption.data('number');
            if (slotType) {
                $slotInfo.html(`<i class="bx bx-grid me-1"></i>Type: ${slotType}`);
            }

            // Fetch ports for selected slot
            $.ajax({
                url: `/api/outages/slots/${slotId}/ports`,
                method: 'GET',
                success: function(response) {
                    if (response.success && response.ports.length > 0) {
                        let options = '<option value="">Select PON Port</option>';
                        response.ports.forEach(function(port) {
                            options += `<option value="${port.id}" data-type="${port.pon_port_type}" data-number="${port.pon_port_number}">${port.display_name}</option>`;
                        });
                        $portSelect.html(options).prop('disabled', false);
                    } else {
                        $portSelect.html('<option value="">No active ports available</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching ports:', xhr);
                    $portSelect.html('<option value="">Error loading ports</option>');
                }
            });
        } else {
            $portSelect.html('<option value="">Select slot first</option>');
            $slotInfo.text('');
        }
    });

    $('#port_id').change(function() {
        const $portInfo = $('#port-info');

        if ($(this).val()) {
            // Show port info
            const selectedOption = $(this).find('option:selected');
            const portType = selectedOption.data('type');
            const portNumber = selectedOption.data('number');
            if (portType) {
                $portInfo.html(`<i class="bx bx-plug me-1"></i>Type: ${portType}`);
            }
        } else {
            $portInfo.text('');
        }
    });

    // Team cascading dropdowns functionality
    $('#assigned_team_type').on('change', function() {
        const teamTypeId = $(this).val();
        const $subTeamSelect = $('#assigned_sub_team_type_id');
        const $userSelect = $('#assigned_to');

        // Reset dependent dropdowns
        $subTeamSelect.html('<option value="">Loading sub team types...</option>').prop('disabled', true);
        $userSelect.html('<option value="">Select Sub Team Type first</option>').prop('disabled', true);

        if (!teamTypeId) {
            $subTeamSelect.html('<option value="">Select Team Type first</option>');
            return;
        }

        // Fetch sub team types for selected team type
        $.ajax({
            url: `/api/outages/team-types/${teamTypeId}/sub-teams`,
            method: 'GET',
            success: function(response) {
                if (response.success && response.subTeamTypes.length > 0) {
                    let options = '<option value="">Select Sub Team Type</option>';
                    response.subTeamTypes.forEach(function(subTeamType) {
                        options += `<option value="${subTeamType.id}">${subTeamType.display_name}</option>`;
                    });
                    $subTeamSelect.html(options).prop('disabled', false);
                } else {
                    $subTeamSelect.html('<option value="">No sub team types available</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching sub team types:', error);
                $subTeamSelect.html('<option value="">Error loading sub team types</option>');
            }
        });
    });

    $('#assigned_sub_team_type_id').on('change', function() {
        const subTeamTypeId = $(this).val();
        const $userSelect = $('#assigned_to');

        // Reset user dropdown
        $userSelect.html('<option value="">Loading users...</option>').prop('disabled', true);

        if (!subTeamTypeId) {
            $userSelect.html('<option value="">Select Sub Team Type first</option>');
            return;
        }

        // Fetch users for selected sub team type
        $.ajax({
            url: `/api/outages/sub-team-types/${subTeamTypeId}/users`,
            method: 'GET',
            success: function(response) {
                if (response.success && response.users.length > 0) {
                    let options = '<option value="">Select User</option>';
                    response.users.forEach(function(user) {
                        options += `<option value="${user.id}">${user.display_name}</option>`;
                    });
                    $userSelect.html(options).prop('disabled', false);
                } else {
                    $userSelect.html('<option value="">No users available</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching users:', error);
                $userSelect.html('<option value="">Error loading users</option>');
            }
        });
    });

    // Auto-calculate impact based on Total Customers Affected
    function calculateImpactFromCustomers() {
        const customersAffected = parseInt($('#total_customers_affected').val()) || 0;
        let suggestedImpact = 'Low';

        if (customersAffected > 500) {
            suggestedImpact = 'Critical';
        } else if (customersAffected > 50) {
            suggestedImpact = 'High';
        } else if (customersAffected > 10) {
            suggestedImpact = 'Medium';
        } else {
            suggestedImpact = 'Low';
        }

        // Show a suggestion, but never silently overwrite an impact the user
        // already picked themselves — this previously clobbered a manually
        // selected "High" back down to an auto-suggested "Medium" the moment
        // the customer count was entered, with no indication to the user.
        const $impactSelect = $('#impact');
        const $impactSuggestion = $('#impact-suggestion');

        if (customersAffected > 0) {
            if (!impactManuallySet) {
                $impactSelect.val(suggestedImpact);
                calculatePriority();
            }

            const suggestionText = impactManuallySet
                ? `Auto-suggested impact would be: ${suggestedImpact} (${customersAffected} customers)`
                : `Suggested impact: ${suggestedImpact} (${customersAffected} customers)`;

            if (!$impactSuggestion.length) {
                $impactSelect.after(`<small id="impact-suggestion" class="form-text text-info">${suggestionText}</small>`);
            } else {
                $impactSuggestion.text(suggestionText);
            }
        } else {
            $impactSuggestion.remove();
        }
    }

    // Bind the calculation to Total Customers Affected input
    $('#total_customers_affected').on('input change', function() {
        calculateImpactFromCustomers();
    });


});
</script>
@endpush

