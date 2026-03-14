<!-- create a new call classification -->
<div class="modal fade" id="addSubDepartment" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-simple modal-edit-Role">
      <div class="modal-content p-2 p-md-5">
          <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-4">
                  <h3 class="mb-2" id="modalTitle">Add Sub Department for </h3>
              </div>

              <form id="addSubDepartmentForm" action="" method="POST" class="row g-4">
                  {{ csrf_field() }}
                  <input type="hidden" name="created_by" value="{{ Auth::user()->id }}">
                  <input type="hidden" name="departemnt_id" id="departmentId" value="">
                  <div class="col-6 col-md-6">
                      <label class="form-label" for="AddSubDepartmentName">Sub Department Name</label>
                      <input
                          type="text"
                          id="AddSubDepartmentName"
                          name="AddSubDepartmentName"
                          class="form-control"
                          placeholder="name" required />
                  </div>
                      <div class="col-6 col-md-6">
                        <label class="form-label" for="Status">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="select2 form-control"
                            required>
                            <option value="" disabled selected>---Select Status---</option>
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                        </select>
                    </div>

                  <!-- Buttons -->
                  <div class="col-12 text-center mt-4">
                      <button type="reset" class="btn btn-label-secondary btn-xs" data-bs-dismiss="modal" aria-label="Close">
                          Cancel
                      </button>
                      @can('view-user-create-department')
                      <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                      @endcan
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>
<script>
  // Listen for the modal trigger event
  const addSubDepartmentModal = document.getElementById('addSubDepartment');
  addSubDepartmentModal.addEventListener('show.bs.modal', function (event) {
      // Button that triggered the modal
      const button = event.relatedTarget;
      
      // Extract the necessary data from the button's data attributes
      const departmentId = button.getAttribute('data-id');
      const departmentName = button.getAttribute('data-department_name');
      
      // Update the form action with the correct department ID
      const form = document.getElementById('addSubDepartmentForm');
      form.action = `/settings/departments/${departmentId}/sub-department/store`;
      
      // Update the department ID hidden field
      document.getElementById('departmentId').value = departmentId;
      
      // Update the modal title
      const modalTitle = document.getElementById('modalTitle');
      modalTitle.textContent = `Add Sub Department for ${departmentName}`;
  });
</script>

