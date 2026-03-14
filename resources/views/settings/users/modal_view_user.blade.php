@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}" />
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
{{-- <script src="{{asset('js/top-call-driver.js')}}"></script> --}}
<script src="{{asset('assets/js/forms-selects.js')}}"></script>
<script src="{{asset('assets/js/forms-tagify.js')}}"></script>
<script src="{{asset('assets/js/forms-typeahead.js')}}"></script>


<style>
  /* Adjust font size and padding for Select2 selected options */
  .select2-container .select2-selection--multiple .select2-selection__rendered li {
      font-size: 13px; /* Adjust as needed */
      line-height: 1.5; /* Adjust as needed */
      padding: 4px 8px;
  }

  /* Ensure the close (x) button is aligned */
  .select2-container .select2-selection--multiple .select2-selection__choice__remove {
      margin-right: 4px;
      font-size: 13px; /* Match font size */
  }

  /* Adjust border radius for consistency */
  .select2-container .select2-selection--multiple .select2-selection__choice {
      border-radius: 4px;
      margin: 2px 4px 2px 0;
  }

  /* Fix the container to not overflow */
  .select2-container {
      max-width: 100%; /* Ensure it fits within the modal */
  }

  /* Fix z-index issues */
.select2-container {
    z-index: 1055 !important; /* Higher than Bootstrap modal's default z-index of 1050 */
}

</style>
<script>
  $(document).ready(function () {
    // Initialize Select2 for all select elements with class 'select2'
    $('.select2').select2({
      width: '100%', // Ensures proper width inside the modal
      placeholder: 'Select an option', // Placeholder text
      allowClear: true, // Allow clearing the selection
    });
  });
</script>
<script>
  function toggleSupervisorDropdown() {
    const roleSelect = document.getElementById('channel');
    const supervisorContainer = document.getElementById('assignSupervisorContainer');

    // Check if the selected role is "agent"
    if (roleSelect.value === "4") {
      supervisorContainer.style.display = "block";
    } else {
      supervisorContainer.style.display = "none";
    }
  }
  /* Style adjustments for disabled select fields */
select[disabled] {
  background-color: #f8f9fa; /* Matches Bootstrap's default input background */
  color: #6c757d; /* Matches Bootstrap's default text color for inputs */
  cursor: not-allowed;
}

</script>


@endsection
                            <!-- View User Modal -->
                            <div class="modal fade" id="viewUser" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog modal-lg modal-dialog-centered  modal-simple modal-edit-user">
                                <div class="modal-content p-2 p-md-5">
                                  <div class="modal-body">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    <div class="text-center mb-4">
                                      <h3 class="mb-2">View User Information</h3>
                                    </div>
                                    <form id="viewUserForm" class="row g-3" onsubmit="return false">
                                      <div class="col-12 col-md-6">
                                        <label class="form-label" for="modalViewUserFirstName">Name</label>
                                        <input
                                          type="text"
                                          id="modalViewUserFirstName"
                                          name="modalViewUserFirstName"
                                          class="form-control"
                                          placeholder="John"
                                          readonly />
                                      </div>
                                      <div class="col-12 col-md-6">
                                        <label class="form-label" for="modalViewUserName">Username</label>
                                        <input
                                          type="text"
                                          id="modalViewUserName"
                                          name="modalViewUserName"
                                          class="form-control"
                                          placeholder="johndoe"
                                          readonly />
                                      </div>
                                      <div class="col-12 col-md-6">
                                        <label class="form-label" for="modalViewUserEmail">Email</label>
                                        <input
                                          type="email"
                                          id="modalViewUserEmail"
                                          name="modalViewUserEmail"
                                          class="form-control"
                                          placeholder="example@domain.com"
                                          readonly />
                                      </div>
                                      <div class="col-12 col-md-6">
                                        <label class="form-label" for="modalViewUserPhone">Phone Number</label>
                                        <input
                                          type="text"
                                          id="modalViewUserPhone"
                                          name="modalViewUserPhone"
                                          class="form-control"
                                          placeholder="+254700000000"
                                          readonly />
                                      </div>
                                      <div class="col-12 col-md-6">
                                        <label class="form-label" for="modalViewUserDepartment">Department</label>
                                        <select
                                          id="modalViewUserDepartment"
                                          name="modalViewUserDepartment"
                                          class="form-select"
                                          disabled>
                                          <option value="">Select Department</option>
                                          @foreach ($department as $row)
                                            <option value="{{$row->id}}">{{$row->department_name}}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                      <div class="col-12 col-md-6">
                                        <label class="form-label" for="modalViewUserSubDepartment">Sub Department</label>
                                        <select
                                          id="modalViewUserSubDepartment"
                                          name="modalViewUserSubDepartment"
                                          class="form-select"
                                          disabled>
                                          <option value="">Select Sub Department</option>
                                        </select>
                                      </div>
                                      <div class="col-12 col-md-6">
                                        <label class="form-label" for="modalViewUserStatus">Status</label>
                                        <select
                                          id="modalViewUserStatus"
                                          name="modalViewUserStatus"
                                          class="form-select"
                                          disabled>
                                          <option value="">Select Status</option>
                                          <option value="1">Active</option>
                                          <option value="0">Inactive</option>
                                        </select>
                                      </div>
                                      <div class="col-md-6 col-12 mb-4">
                                        <label for="modalViewUserRoles" class="form-label">Assign Roles</label>
                                        <div class="select2-primary">
                                          <select id="modalViewUserRoles"
                                            name="modalViewUserRoles"
                                            class="form-select"
                                            disabled>
                                            <option value="">Assign Role</option>
                                            @foreach ($roles as $role)
                                              <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                      </div>
                                      <div class="col-md-6 col-12 mb-4" id="viewTeamTypeContainer" style="display: none;">
                                        <label class="form-label" for="modalViewUserTeamType">Team Type</label>
                                        <div class="select2-primary">
                                          <select
                                            id="modalViewUserTeamType"
                                            name="modalViewUserTeamType"
                                            class="form-select"
                                            disabled>
                                            <option value="">Select Team Type</option>
                                            @foreach($teamTypes as $teamType)
                                              <option value="{{ $teamType->id }}">{{ $teamType->type_name }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                      </div>
                                      <div class="col-md-6 col-12 mb-4" id="viewSubTeamTypeContainer" style="display: none;">
                                        <label class="form-label" for="modalViewUserTeamTypeSub">Sub Team Type</label>
                                        <div class="select2-primary">
                                          <select
                                            id="modalViewUserTeamTypeSub"
                                            name="modalViewUserTeamTypeSub"
                                            class="form-select"
                                            disabled>
                                            <option value="">Select Sub Team Type</option>
                                          </select>
                                        </div>
                                      </div>
                                      <div class="col-12 col-md-6" id="viewAssignSupervisorContainer" style="display: none;">
                                        <label for="modalViewAssignSupervisor" class="form-label">Assign Supervisor</label>
                                        <div class="select2-primary">
                                          <select
                                            id="modalViewAssignSupervisor"
                                            name="modalViewAssignSupervisor"
                                            class="form-select"
                                            disabled>
                                            <option value="">Assign Supervisor</option>
                                            {{-- @foreach ($supervisor as $row)
                                              <option value="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach --}}
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

                                      </div>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!--/ View User Modal -->
                            <script>
                              // Function to check if Team fields should be visible
                              function checkViewTeamFieldsVisibility() {
                                const departmentSelect = document.getElementById('modalViewUserDepartment');
                                const subDepartmentSelect = document.getElementById('modalViewUserSubDepartment');
                                const teamTypeContainer = document.getElementById('viewTeamTypeContainer');
                                const subTeamTypeContainer = document.getElementById('viewSubTeamTypeContainer');
                                
                                const departmentText = departmentSelect.options[departmentSelect.selectedIndex]?.text || '';
                                const subDepartmentText = subDepartmentSelect.options[subDepartmentSelect.selectedIndex]?.text || '';
                                
                                console.log('View Modal - Department:', departmentText, 'Sub Department:', subDepartmentText);
                                
                                // Show Team fields only if Department is Infrastructure or Service Delivery AND Sub Department is Field Technician
                                const shouldShow = (departmentText === 'Infrastructure' || departmentText === 'Service Delivery') && 
                                                 subDepartmentText === 'Field Technician';
                                
                                if (shouldShow) {
                                  teamTypeContainer.style.display = 'block';
                                  subTeamTypeContainer.style.display = 'block';
                                  console.log('View Modal - Team fields shown');
                                } else {
                                  teamTypeContainer.style.display = 'none';
                                  subTeamTypeContainer.style.display = 'none';
                                  // Clear selections when hiding
                                  document.getElementById('modalViewUserTeamType').value = '';
                                  document.getElementById('modalViewUserTeamTypeSub').value = '';
                                  console.log('View Modal - Team fields hidden');
                                }
                              }

                              // Function to load sub departments based on selected department
                              function loadViewSubDepartments(departmentId, selectedSubDeptId = null) {
                                const subDepartmentSelect = document.getElementById('modalViewUserSubDepartment');
                                
                                // Clear existing options
                                subDepartmentSelect.innerHTML = '<option value="">Select Sub Department</option>';
                                
                                if (departmentId) {
                                  fetch(`/teamtypes/sub-departments?department_id=${departmentId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                      data.forEach(subDept => {
                                        const option = document.createElement('option');
                                        option.value = subDept.id;
                                        option.textContent = subDept.name; 
                                        if (selectedSubDeptId && subDept.id == selectedSubDeptId) {
                                          option.selected = true;
                                        }
                                        subDepartmentSelect.appendChild(option);
                                      });
                                      
                                      // Also load team types for this department
                                      loadViewTeamTypesByDepartment(departmentId, null);
                                      
                                      // Check team field visibility after loading sub departments
                                      checkViewTeamFieldsVisibility();
                                    })
                                    .catch(error => {
                                      console.error('Error loading sub departments:', error);
                                    });
                                } else {
                                  checkViewTeamFieldsVisibility();
                                }
                              }

                              // Function to load team types based on selected department in view modal
                              function loadViewTeamTypesByDepartment(departmentId, selectedTeamTypeId = null) {
                                const teamTypeSelect = document.getElementById('modalViewUserTeamType');
                                
                                // Clear existing options
                                teamTypeSelect.innerHTML = '<option value="">Select Team Type</option>';
                                
                                if (departmentId) {
                                  console.log('Loading team types for department ID:', departmentId);
                                  
                                  fetch(`/teamtypes/team-types-by-department?department_id=${departmentId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                      console.log('Team types response:', data);
                                      
                                      if (data && data.length > 0) {
                                        data.forEach(teamType => {
                                          const option = document.createElement('option');
                                          option.value = teamType.id;
                                          option.textContent = teamType.type_name;
                                          if (selectedTeamTypeId && teamType.id == selectedTeamTypeId) {
                                            option.selected = true;
                                          }
                                          teamTypeSelect.appendChild(option);
                                        });
                                        console.log('Loaded', data.length, 'team types for department');
                                        
                                        // Load sub team types if a team type is selected
                                        if (selectedTeamTypeId) {
                                          loadViewSubTeamTypes(selectedTeamTypeId, null);
                                        }
                                      } else {
                                        console.log('No team types found for this department');
                                        const option = document.createElement('option');
                                        option.value = '';
                                        option.textContent = 'No team types available';
                                        teamTypeSelect.appendChild(option);
                                      }
                                      
                                      // Clear sub team type if no team type is selected
                                      if (!selectedTeamTypeId) {
                                        const subTeamTypeSelect = document.getElementById('modalViewUserTeamTypeSub');
                                        subTeamTypeSelect.innerHTML = '<option value="">Select Sub Team Type</option>';
                                      }
                                    })
                                    .catch(error => {
                                      console.error('Error loading team types:', error);
                                      const option = document.createElement('option');
                                      option.value = '';
                                      option.textContent = 'Error loading team types';
                                      teamTypeSelect.appendChild(option);
                                    });
                                } else {
                                  // Clear sub team type when no department is selected
                                  const subTeamTypeSelect = document.getElementById('modalViewUserTeamTypeSub');
                                  subTeamTypeSelect.innerHTML = '<option value="">Select Sub Team Type</option>';
                                }
                              }

                              // Function to load sub team types based on selected team type
                              function loadViewSubTeamTypes(teamTypeId, selectedSubTeamId = null) {
                                const subTeamSelect = document.getElementById('modalViewUserTeamTypeSub');
                                
                                // Clear existing options
                                subTeamSelect.innerHTML = '<option value="">Select Sub Team Type</option>';
                                
                                if (teamTypeId) {
                                  fetch(`/get-sub-team-types/${teamTypeId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                      data.forEach(subTeam => {
                                        const option = document.createElement('option');
                                        option.value = subTeam.id;
                                        option.textContent = subTeam.sub_type_name;
                                        if (selectedSubTeamId && subTeam.id == selectedSubTeamId) {
                                          option.selected = true;
                                        }
                                        subTeamSelect.appendChild(option);
                                      });
                                    })
                                    .catch(error => {
                                      console.error('Error loading sub team types:', error);
                                    });
                                }
                              }

                              document.addEventListener('DOMContentLoaded', function () {
                                  const viewUserButtons = document.querySelectorAll('.view-btn');
                                  viewUserButtons.forEach(button => {
                                      button.addEventListener('click', function () {
                                          // Extract data attributes from the clicked button
                                          const userId = this.getAttribute('data-id');
                                          const userName = this.getAttribute('data-name');
                                          const userUsername = this.getAttribute('data-username');
                                          const userEmail = this.getAttribute('data-email');
                                          const userPhone = this.getAttribute('data-phone');
                                          const userStatus = this.getAttribute('data-user_status');
                                          const userDepartment = this.getAttribute('data-department_id');
                                          const userSubDepartment = this.getAttribute('data-sub_department_id');
                                          const userRole = this.getAttribute('data-role_id');
                                          const userTeamType = this.getAttribute('data-team_type_id');
                                          const userSubTeamType = this.getAttribute('data-sub_team_type_id');

                                          // Populate the modal fields
                                          document.getElementById('modalViewUserFirstName').value = userName || '';
                                          document.getElementById('modalViewUserName').value = userUsername || '';
                                          document.getElementById('modalViewUserEmail').value = userEmail || '';
                                          document.getElementById('modalViewUserPhone').value = userPhone || '';
                                          document.getElementById('modalViewUserStatus').value = userStatus || '';
                                          document.getElementById('modalViewUserDepartment').value = userDepartment || '';
                                          document.getElementById('modalViewUserRoles').value = userRole || '';
                                          
                                          // Load sub departments and set selected value
                                          if (userDepartment) {
                                            loadViewSubDepartments(userDepartment, userSubDepartment);
                                          } else {
                                            document.getElementById('modalViewUserSubDepartment').value = '';
                                            checkViewTeamFieldsVisibility();
                                          }
                                          
                                          // Set team type and load sub team types
                                          if (userTeamType) {
                                            document.getElementById('modalViewUserTeamType').value = userTeamType;
                                            loadViewSubTeamTypes(userTeamType, userSubTeamType);
                                          } else {
                                            document.getElementById('modalViewUserTeamType').value = '';
                                            document.getElementById('modalViewUserTeamTypeSub').value = '';
                                          }
                                      });
                                  });

                                  // Add event listeners for dynamic behavior (though fields are disabled, this maintains consistency)
                                  document.getElementById('modalViewUserDepartment').addEventListener('change', function() {
                                    const departmentId = this.value;
                                    loadViewSubDepartments(departmentId);
                                  });

                                  document.getElementById('modalViewUserSubDepartment').addEventListener('change', function() {
                                    checkViewTeamFieldsVisibility();
                                  });

                                  document.getElementById('modalViewUserTeamType').addEventListener('change', function() {
                                    const teamTypeId = this.value;
                                    loadViewSubTeamTypes(teamTypeId);
                                  });
                              });
                            </script>

