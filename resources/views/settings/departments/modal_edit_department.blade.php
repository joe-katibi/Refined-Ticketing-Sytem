              <!-- edit Department Modal -->
              <div class="modal fade" id="editDepartment" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-simple modal-edit-department">
                  <div class="modal-content p-2 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="mb-2">Edit Department Information</h3>
                      </div>
                      <form id="editDepartmentForm" action="" class="row g-3" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="department_id" id="department_id" value="">
                        <input type="hidden" class="form-control" name="edited_by" value="{{ Auth::user()->id }}">
                        
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditDepartmentName">Department Name</label>
                          <input
                            type="text"
                            id="modalEditDepartmentName"
                            name="modalAddDepartmentName"
                            value=""
                            class="form-control"
                            placeholder="Department Name" />
                        </div>

                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditDepartmentDescription">Description</label>
                          <input
                            type="text"
                            id="modalEditDepartmentDescription"
                            name="modalAddDepartmentDescription"
                            value=""
                            class="form-control"
                            placeholder="Description" />
                        </div>
                        <div class="col-12 text-center">
                          <button type="button" class="btn btn-label-secondary btn-xs" data-bs-dismiss="modal">
                            Cancel
                          </button>
                          <button type="submit" class="btn btn-primary me-sm-3 me-1">Save Changes</button>
                        </div>
                      </form>

                    </div>
                  </div>
                </div>
              </div>
              <!--/ Edit Department Modal -->

