@section('vendor-style')

<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css')}}" />

@endsection


@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js')}}"></script>
<script src="{{asset('assets/vendor/libs/typeahead-js/typeahead.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bloodhound/bloodhound.js')}}"></script>

@endsection

@section('page-script')
{{-- Debug: Check if teamTypes is available --}}
@php
    \Log::info('Team Types in modal:', ['teamTypes' => $teamTypes ?? null]);

    // Debug output - this will be visible in the page source
    $debugInfo = [
        'teamTypes_exists' => isset($teamTypes) ? 'Yes' : 'No',
        'teamTypes_count' => isset($teamTypes) ? count($teamTypes) : 0,
        'teamTypes_sample' => isset($teamTypes) && count($teamTypes) > 0 ? $teamTypes[0] : 'N/A'
    ];
@endphp

<!-- Debug Output (visible in page source) -->
<!--
    Debug Info:
    - teamTypes exists: {{ $debugInfo['teamTypes_exists'] }}
    - teamTypes count: {{ $debugInfo['teamTypes_count'] }}
    - First team type: {{ json_encode($debugInfo['teamTypes_sample']) }}
-->

{{-- <script src="{{asset('js/top-call-driver.js')}}"></script> --}}
<script src="{{asset('assets/js/forms-selects.js')}}"></script>
<script src="{{asset('assets/js/forms-tagify.js')}}"></script>
<script src="{{asset('assets/js/forms-typeahead.js')}}"></script>



<script>
  // Setup AJAX to always include CSRF token
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $(document).ready(function() {
    // Debug: Log team types to console
    console.log('Team Types in modal:', @json($teamTypes ?? []));
    console.log('Modal JavaScript loaded');

    // Add form submission debugging
    $('#addUserForm').on('submit', function(e) {
      console.log('Form submission triggered');
      console.log('Form data:', $(this).serialize());

      // Basic form validation
      let isValid = true;
      let requiredFields = ['name', 'email', 'username', 'password', 'department_id', 'status'];

      // Check regular fields
      requiredFields.forEach(function(field) {
        let fieldElement = $('#' + field);

        if (fieldElement.length && !fieldElement.val()) {
          console.error('Field ' + field + ' is required but empty');
          isValid = false;
          fieldElement.addClass('is-invalid');

          // Add error message if it doesn't exist
          if (fieldElement.next('.invalid-feedback').length === 0) {
            fieldElement.after('<div class="invalid-feedback">This field is required.</div>');
          }
        } else {
          fieldElement.removeClass('is-invalid');
        }
      });

      // Special handling for roles (Select2 multiple)
      let rolesField = $('#roles');
      if (rolesField.length && (!rolesField.val() || rolesField.val().length === 0)) {
        console.error('Roles field is required but empty');
        isValid = false;
        rolesField.next('.select2-container').addClass('is-invalid');

        // Add error message if it doesn't exist
        if (rolesField.parent().find('.invalid-feedback').length === 0) {
          rolesField.parent().append('<div class="invalid-feedback">Please select at least one role.</div>');
        }
      } else {
        rolesField.next('.select2-container').removeClass('is-invalid');
        rolesField.parent().find('.invalid-feedback').remove();
      }

      if (!isValid) {
        console.error('Form validation failed');
        e.preventDefault();
        return false;
      }

      console.log('Form validation passed, submitting...');

      // Show loading indicator
      let submitBtn = $(this).find('button[type="submit"]');
      let originalBtnText = submitBtn.html();
      submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
      submitBtn.prop('disabled', true);

      // Debug CSRF token and form data
      console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
      console.log('Form CSRF Token:', $('input[name="_token"]').val());

      // Debug all form fields
      let formData = {};
      $.each($(this).serializeArray(), function(i, field) {
        formData[field.name] = field.value;
      });
      console.log('Complete form data:', formData);

      // Check for roles field specifically
      console.log('Roles value:', $('#roles').val());

      // Ensure roles are properly included in the form data
      let rolesValue = $('#roles').val();
      if (rolesValue && rolesValue.length > 0) {
        // Remove any existing hidden role fields to avoid duplicates
        $('input[name="roles[]"]').remove();

        // Add hidden fields for each role
        rolesValue.forEach(function(role) {
          $('<input>').attr({
            type: 'hidden',
            name: 'roles[]',
            value: role
          }).appendTo('#addUserForm');
        });
      }

      // Submit the form directly
      document.getElementById('addUserForm').submit();

      return false; // Prevent default form submission
    });

    // Clear dropdowns on page load
    $('#sub_department_id').empty().append('<option value="">Select Department First</option>');
    $('#sub_team_type_id').empty();

    // Initialize Select2 for all select2 elements
    $('.select2').select2({
      theme: 'bootstrap-5',
      width: '100%',
      dropdownParent: $('#addUser')
    });

    // Initialize Select2 for roles dropdown with proper configuration
    $('#roles').select2({
      dropdownParent: $('#addUser'),
      closeOnSelect: false
    });

    // Initialize the supervisor dropdown visibility
    toggleSupervisorDropdown();

    // Debug: Check if department dropdown exists and is initialized
    console.log('Department dropdown exists:', $('#department_id').length > 0);
    console.log('Department dropdown has select2:', $('#department_id').hasClass('select2-hidden-accessible'));

    // When department is selected, load sub-departments
    $('#department_id').on('select2:select change', function() {
      var departmentId = $(this).val();
      console.log('Department changed to:', departmentId);
      console.log('Event triggered on department dropdown');

      // Always clear the sub-department dropdown first
      $('#sub_department_id').empty();

      if (departmentId) {
        // Show loading indicator
        $('#sub_department_id').append('<option value="">Loading...</option>');

        // Debug: Log the request URL
        var ajaxUrl = '{{ route("teamtypes.sub-departments") }}?department_id=' + departmentId;
        console.log('Fetching sub-departments from:', ajaxUrl);

        $.ajax({
          url: ajaxUrl,
          type: 'GET',
          dataType: 'json',
          beforeSend: function() {
            console.log('AJAX request started');
          },
          success: function(data) {
            // Debug: Log the response data
            console.log('Sub-departments response:', data);
            console.log('Response type:', typeof data);
            console.log('Response length:', data ? data.length : 'null');

            // Clear dropdown again before adding new options
            $('#sub_department_id').empty();

            if (data && data.length > 0) {
              $('#sub_department_id').append('<option value="">Select Sub Department</option>');
              $.each(data, function(key, value) {
                console.log('Adding sub-department:', value);
                $('#sub_department_id').append('<option value="' + value.id + '">' + value.name + '</option>');
              });
              console.log('Sub-departments loaded successfully');
            } else {
              $('#sub_department_id').append('<option value="">No sub-departments available</option>');
              console.log('No sub-departments found for department ID:', departmentId);
            }
          },
          error: function(xhr, status, error) {
            // Debug: Log the error details
            console.error('Error loading sub-departments:', status, error);
            console.error('Response status:', xhr.status);
            console.error('Response text:', xhr.responseText);
            console.error('Full XHR object:', xhr);

            $('#sub_department_id').empty();
            $('#sub_department_id').append('<option value="">Error loading sub-departments</option>');
          }
        });
      } else {
        $('#sub_department_id').append('<option value="">Select Department First</option>');
      }
    });

    // When team type is selected, load sub-team types
    $('#team_type_id').on('change', function() {
      var teamTypeId = $(this).val();
      // Always clear the sub-team type dropdown first
      $('#sub_team_type_id').empty();

      if (teamTypeId) {
        // Show loading indicator
        $('#sub_team_type_id').append('<option value="">Loading...</option>');

        // Debug: Log the request URL
        console.log('Fetching sub-team types from:', '/teamtypes/' + teamTypeId + '/sub-teams');

        $.ajax({
          url: '/teamtypes/' + teamTypeId + '/sub-teams',
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            // Debug: Log the response data
            console.log('Sub-team types response:', data);

            // Clear dropdown again before adding new options
            $('#sub_team_type_id').empty();

            if (data && data.length > 0) {
              $('#sub_team_type_id').append('<option value="">Select Sub Team Type</option>');
              $.each(data, function(key, value) {
                $('#sub_team_type_id').append('<option value="' + value.id + '">' + value.name + '</option>');
              });
            } else {
              $('#sub_team_type_id').append('<option value="">No sub-team types available</option>');
            }
          },
          error: function(xhr, status, error) {
            // Debug: Log the error details
            console.error('Error loading sub-team types:', status, error);
            console.error('Response:', xhr.responseText);

            $('#sub_team_type_id').empty();
            $('#sub_team_type_id').append('<option value="">Error loading sub-team types</option>');
          }
        });
      }
    });

    // Debug: Check if select2 initialized properly
    var teamTypeSelect = $('#team_type_id');
    console.log('Team Type Select2 initialized:', teamTypeSelect.hasClass('select2-hidden-accessible'));
  });
</script>


<script>
  function toggleSupervisorDropdown() {
    var rolesSelect = document.getElementById('roles');
    var teamTypeContainer = document.getElementById('teamTypeContainer');
    var subTeamTypeContainer = document.getElementById('subTeamTypeContainer');

    // Check if Field-Technician role is selected by name
    var isFieldTechnicianSelected = false;
    for (var i = 0; i < rolesSelect.options.length; i++) {
      if (rolesSelect.options[i].selected && rolesSelect.options[i].text === 'Field-Technician') {
        isFieldTechnicianSelected = true;
        break;
      }
    }

    // Show/hide team type and sub team type containers based on Field-Technician role selection
    if (isFieldTechnicianSelected) {
      teamTypeContainer.style.display = 'block';
      subTeamTypeContainer.style.display = 'block';
    } else {
      teamTypeContainer.style.display = 'none';
      subTeamTypeContainer.style.display = 'none';
    }
  }
</script>

@endsection



 <!-- Add User Modal -->
              <div class="modal fade" id="addUser" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-simple modal-edit-user">
                  <div class="modal-content p-2 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="mb-2">Add User Information</h3>
                      </div>
                      @if ($errors->any())
                        <div class="alert alert-danger">
                          <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                            @endforeach
                          </ul>
                        </div>
                      @endif
                      <form action="{{ route('settings.store') }}" id="addUserForm" class="row g-3" method="POST">
                        {{csrf_field()}}
                        <input  type="hidden" class="form-control" name="created_by" value="{{ Auth::user()->id }}">
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="name">Name</label>
                          <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="John" />
                          @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="username">Username</label>
                          <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control @error('username') is-invalid @enderror"
                            value="{{ old('username') }}"
                            placeholder="john.doe" />
                          @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="phone">Phone Number</label>
                          <input
                            type="text"
                            id="phone"
                            name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone') }}"
                            placeholder="+254 700 000000" />
                          @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="email">Email</label>
                          <input
                            type="text"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="john.doe@example.com" />
                          @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="password">Initial Password</label>
                          <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter initial password" />
                          <div id="passwordHelpBlock" class="form-text">
                            User will be required to change this password on first login
                          </div>
                          @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="department_id">Department</label>
                            <select
                            id="department_id"
                            name="department_id"
                            class="select2 form-select @error('department_id') is-invalid @enderror"
                            >
                            <option value="">Select Department</option>
                            @foreach ($department as $dept)
                              <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                            @endforeach
                          </select>
                          @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="sub_department_id">Sub Department</label>
                            <select
                            id="sub_department_id"
                            name="sub_department_id"
                            class="select2 form-select"
                            >
                            <option value="">Select Department First</option>
                            <!-- Sub departments will be loaded dynamically -->
                          </select>
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="status">Status</label>
                            <select
                            id="status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            >
                            <option value="">Select Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                          </select>
                          @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-md-6 col-12 mb-4">
                          <label for="roles" class="form-label">Assign Roles</label>
                          <div class="select2-primary">
                            <select id="roles"
                              name="roles[]"
                              class="select2 form-select @error('roles') is-invalid @enderror"
                              multiple
                              onchange="toggleSupervisorDropdown()">
                              @foreach ($roles as $role)
                              <option value="{{ $role->id }}">
                              {{ $role->name }}
                              </option>
                              @endforeach
                            </select>
                            @error('roles')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-4" id="teamTypeContainer" style="display: none;">
                          <label class="form-label" for="team_type_id">Team Type</label>
                          <div class="select2-primary">
                            <select
                              id="team_type_id"
                              name="team_type_id"
                              class="form-select "
                            >
                              <option value="">Select Team Type</option>
                                @foreach($teamTypes as $teamType)
                                  <option value="{{ $teamType->id }}">{{ $teamType->type_name }}</option>
                                @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-4" id="subTeamTypeContainer" style="display: none;">
                          <label class="form-label" for="sub_team_type_id">Sub Team Type</label>
                          <div class="select2-primary">
                          <select
                            id="sub_team_type_id"
                            name="sub_team_type_id"
                            class="form-select "
                            >
                            <!-- Sub team types will be populated dynamically based on selected Team Type -->
                          </select>
                          </div>
                        </div>

                        <div class="col-12 text-center">
                          <button
                          type="reset"
                          class="btn btn-label-secondary btn-xs"
                          data-bs-dismiss="modal"
                          aria-label="Close">
                          Cancel
                        </button>
                          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
