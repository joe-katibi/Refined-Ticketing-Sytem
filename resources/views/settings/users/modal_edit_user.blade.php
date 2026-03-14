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
</script>
</script>

@endsection

              <!-- Edit User Modal -->
              <div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-simple modal-edit-user">
                  <div class="modal-content p-2 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="mb-2">Edit User Information</h3>
                      </div>
                      <form action="" id="editUserForm" class="row g-3" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" class="form-control" name="edited_by" value="{{ Auth::user()->id }}">
                        <input type="hidden" name="modal_row_id" value="" id="modalEditUserId">
                        
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditUserFirstName">Name</label>
                          <input
                            type="text"
                            id="modalEditUserFirstName"
                            name="modalEditUserFirstName"
                            class="form-control"
                            placeholder="John"
                            required />
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditUserName">Username</label>
                          <input
                            type="text"
                            id="modalEditUserName"
                            name="modalEditUserName"
                            class="form-control"
                            placeholder="john.doe"
                            required />
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditUserEmail">Email</label>
                          <input
                            type="email"
                            id="modalEditUserEmail"
                            name="modalEditUserEmail"
                            class="form-control"
                            placeholder="example@domain.com"
                            required />
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditUserPhone">Phone Number</label>
                          <input
                            type="text"
                            id="modalEditUserPhone"
                            name="modalEditUserPhone"
                            class="form-control"
                            placeholder="+254700000000" />
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditUserDepartment">Department</label>
                          <select
                            id="modalEditUserDepartment"
                            name="modalEditUserDepartment"
                            class="form-select"
                            required>
                            <option value="">Select Department</option>
                            @foreach ($department as $dept)
                              <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditUserSubDepartment">Sub Department</label>
                          <select
                            id="modalEditUserSubDepartment"
                            name="modalEditUserSubDepartment"
                            class="form-select"
                            required>
                            <option value="">Select Sub Department</option>
                          </select>
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditUserStatus">Status</label>
                          <select
                            id="modalEditUserStatus"
                            name="modalEditUserStatus"
                            class="form-select"
                            required>
                            <option value="">Select Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                          </select>
                        </div>
                        <div class="col-md-6 col-12 mb-4">
                          <label for="modalEditUserRoles" class="form-label">Assign Roles</label>
                          <div class="select2-primary">
                            <select id="modalEditUserRoles"
                              name="modalEditUserRoles"
                              class="form-select"
                              required>
                              <option value="">Assign Role</option>
                              @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-4" id="editTeamTypeContainer" style="display: none;">
                          <label class="form-label" for="modalEditUserTeamType">Team Type</label>
                          <div class="select2-primary">
                            <select
                              id="modalEditUserTeamType"
                              name="modalEditUserTeamType"
                              class="form-select">
                              <option value="">Select Team Type</option>
                              @foreach($teamTypes as $teamType)
                                <option value="{{ $teamType->id }}">{{ $teamType->type_name }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-4" id="editSubTeamTypeContainer" style="display: none;">
                          <label class="form-label" for="modalEditUserTeamTypeSub">Sub Team Type</label>
                          <div class="select2-primary">
                            <select
                              id="modalEditUserTeamTypeSub"
                              name="modalEditUserTeamTypeSub"
                              class="form-select">
                              <option value="">Select Sub Team Type</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-12 col-md-6" id="editAssignSupervisorContainer" style="display: none;">
                          <label for="modalEditAssignSupervisor" class="form-label">Assign Supervisor</label>
                          <div class="select2-primary">
                            <select
                              id="modalEditAssignSupervisor"
                              name="modalEditAssignSupervisor"
                              class="form-select">
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
                          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <!--/ Edit User Modal -->
              <script>
                // Function to check if team fields should be visible in edit modal
                function checkEditTeamFieldsVisibility() {
                  const departmentSelect = document.getElementById('modalEditUserDepartment');
                  const subDepartmentSelect = document.getElementById('modalEditUserSubDepartment');
                  const teamTypeContainer = document.getElementById('editTeamTypeContainer');
                  const subTeamTypeContainer = document.getElementById('editSubTeamTypeContainer');
                  
                  const departmentText = departmentSelect.options[departmentSelect.selectedIndex]?.text || '';
                  const subDepartmentText = subDepartmentSelect.options[subDepartmentSelect.selectedIndex]?.text || '';
                  
                  console.log('Edit Modal - Department:', departmentText, 'Sub Department:', subDepartmentText);
                  
                  // Show Team fields only if Department is Infrastructure or Service Delivery AND Sub Department is Field Technician
                  const shouldShow = (departmentText === 'Infrastructure' || departmentText === 'Service Delivery') && 
                                   subDepartmentText === 'Field Technician';
                  
                  if (shouldShow) {
                    teamTypeContainer.style.display = 'block';
                    subTeamTypeContainer.style.display = 'block';
                    console.log('Edit Modal - Team fields shown');
                  } else {
                    teamTypeContainer.style.display = 'none';
                    subTeamTypeContainer.style.display = 'none';
                    // Clear selections when hiding
                    document.getElementById('modalEditUserTeamType').value = '';
                    document.getElementById('modalEditUserTeamTypeSub').value = '';
                    console.log('Edit Modal - Team fields hidden');
                  }
                }

                // Function to load team types based on selected department in edit modal
                function loadEditTeamTypesByDepartment(departmentId, selectedTeamTypeId = null) {
                  const teamTypeSelect = document.getElementById('modalEditUserTeamType');
                  
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
                            loadEditSubTeamTypes(selectedTeamTypeId, null);
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
                          const subTeamTypeSelect = document.getElementById('modalEditUserTeamTypeSub');
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
                    const subTeamTypeSelect = document.getElementById('modalEditUserTeamTypeSub');
                    subTeamTypeSelect.innerHTML = '<option value="">Select Sub Team Type</option>';
                  }
                }

                // Function to load sub departments based on selected department in edit modal
                function loadEditSubDepartments(departmentId, selectedSubDeptId = null) {
                  const subDepartmentSelect = document.getElementById('modalEditUserSubDepartment');
                  
                  // Clear existing options
                  subDepartmentSelect.innerHTML = '<option value="">Select Sub Department</option>';
                  
                  if (departmentId) {
                    fetch(`/teamtypes/sub-departments?department_id=${departmentId}`)
                      .then(response => response.json())
                      .then(data => {
                        data.forEach(subDept => {
                          const option = document.createElement('option');
                          option.value = subDept.id;
                          option.textContent = subDept.name; // Note: API returns 'name' not 'sub_department_name'
                          if (selectedSubDeptId && subDept.id == selectedSubDeptId) {
                            option.selected = true;
                          }
                          subDepartmentSelect.appendChild(option);
                        });
                        
                        // Also load team types for this department
                        loadEditTeamTypesByDepartment(departmentId, null);
                        
                        // Check team field visibility after loading sub departments
                        checkEditTeamFieldsVisibility();
                      })
                      .catch(error => {
                        console.error('Error loading sub departments:', error);
                      });
                  } else {
                    checkEditTeamFieldsVisibility();
                  }
                }

                // Function to load team types based on selected department in edit modal
                function loadEditTeamTypesByDepartment(departmentId, selectedTeamTypeId = null) {
                  const teamTypeSelect = document.getElementById('modalEditUserTeamType');
                  
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
                            loadEditSubTeamTypes(selectedTeamTypeId, null);
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
                          const subTeamTypeSelect = document.getElementById('modalEditUserTeamTypeSub');
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
                    const subTeamTypeSelect = document.getElementById('modalEditUserTeamTypeSub');
                    subTeamTypeSelect.innerHTML = '<option value="">Select Sub Team Type</option>';
                  }
                }

                // Function to load sub team types based on selected team type in edit modal
                function loadEditSubTeamTypes(teamTypeId, selectedSubTeamId = null) {
                  const subTeamSelect = document.getElementById('modalEditUserTeamTypeSub');
                  
                  // Clear existing options
                  subTeamSelect.innerHTML = '<option value="">Select Sub Team Type</option>';
                  
                  if (teamTypeId) {
                    console.log('Loading sub team types for team type ID:', teamTypeId);
                    
                    fetch(`/teamtypes/${teamTypeId}/sub-teams`)
                      .then(response => response.json())
                      .then(data => {
                        console.log('Sub team types response:', data);
                        
                        if (data && data.length > 0) {
                          data.forEach(subTeam => {
                            const option = document.createElement('option');
                            option.value = subTeam.id;
                            option.textContent = subTeam.name; // API returns 'name' not 'sub_type_name'
                            if (selectedSubTeamId && subTeam.id == selectedSubTeamId) {
                              option.selected = true;
                            }
                            subTeamSelect.appendChild(option);
                          });
                          console.log('Loaded', data.length, 'sub team types');
                        } else {
                          console.log('No sub team types found for this team type');
                          const option = document.createElement('option');
                          option.value = '';
                          option.textContent = 'No sub team types available';
                          subTeamSelect.appendChild(option);
                        }
                      })
                      .catch(error => {
                        console.error('Error loading sub team types:', error);
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Error loading sub team types';
                        subTeamSelect.appendChild(option);
                      });
                  }
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const editUserButtons = document.querySelectorAll('.edit-user-btn');
                    editUserButtons.forEach(button => {
                        button.addEventListener('click', function () {
                            // Extract data attributes from the clicked button
                            const userId = this.getAttribute('data-user-id');
                            const userName = this.getAttribute('data-user-name');
                            const userUsername = this.getAttribute('data-username');
                            const userEmail = this.getAttribute('data-user-email');
                            const userPhone = this.getAttribute('data-phone');
                            const userStatus = this.getAttribute('data-user_status');
                            const userDepartment = this.getAttribute('data-department_id');
                            const userSubDepartment = this.getAttribute('data-sub_department_id');
                            const roleId = this.getAttribute('data-role_id');
                            const userTeamType = this.getAttribute('data-team_type_id');
                            const userSubTeamType = this.getAttribute('data-sub_team_type_id');

                            // Populate the modal fields
                            document.getElementById('editUserForm').action = `{{ url('settings/users') }}/${userId}/update`;
                            document.getElementById('modalEditUserId').value = userId;
                            document.getElementById('modalEditUserFirstName').value = userName || '';
                            document.getElementById('modalEditUserName').value = userUsername || '';
                            document.getElementById('modalEditUserEmail').value = userEmail || '';
                            document.getElementById('modalEditUserPhone').value = userPhone || '';
                            document.getElementById('modalEditUserStatus').value = userStatus || '';
                            document.getElementById('modalEditUserDepartment').value = userDepartment || '';
                            document.getElementById('modalEditUserRoles').value = roleId || '';
                            
                            // Load sub departments and set selected value
                            if (userDepartment) {
                              loadEditSubDepartments(userDepartment, userSubDepartment);
                            } else {
                              document.getElementById('modalEditUserSubDepartment').value = '';
                              checkEditTeamFieldsVisibility();
                            }
                            
                            // Set team type and load sub team types
                            if (userTeamType) {
                              document.getElementById('modalEditUserTeamType').value = userTeamType;
                              loadEditSubTeamTypes(userTeamType, userSubTeamType);
                            } else {
                              document.getElementById('modalEditUserTeamType').value = '';
                              document.getElementById('modalEditUserTeamTypeSub').value = '';
                            }
                        });
                    });

                    // Add event listeners for dynamic behavior
                    document.getElementById('modalEditUserDepartment').addEventListener('change', function() {
                      const departmentId = this.value;
                      loadEditSubDepartments(departmentId);
                    });

                    document.getElementById('modalEditUserSubDepartment').addEventListener('change', function() {
                      checkEditTeamFieldsVisibility();
                    });

                    document.getElementById('modalEditUserTeamType').addEventListener('change', function() {
                      const teamTypeId = this.value;
                      loadEditSubTeamTypes(teamTypeId);
                    });
                });
              </script>

