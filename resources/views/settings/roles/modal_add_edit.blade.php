              <!-- edit Role Modal -->
              <div class="modal fade" id="editRole" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered  modal-simple modal-edit-Role">
                  <div class="modal-content p-2 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="mb-2">Edit Role Information</h3>
                      </div>

                      <form  id="editRoleForm" action="" class="row g-3"  method="POST">
                        @csrf
                        @method('PUT')
                        <input  type="hidden" class="form-control" name="edited_by" value="{{ Auth::user()->id }}">
                        <input type="hidden" name="modal_row_id" id="modal_row_id" value="" >
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditRoleName">Role Name</label>
                          <input
                            type="text"
                            id="modalEditRoleName"
                            name="modalAddRoleName"
                            value=""
                            class="form-control"
                            placeholder="Role" />
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalEditRoleDescription">Description</label>
                          <input
                            type="text"
                            id="modalEditRoleDescription"
                            name="modalAddRoleDescription"
                            value=""
                            class="form-control"
                            placeholder="Description" />
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
              <!--/ Edit Role Modal -->

