              <!-- Add Role Modal -->
              <div class="modal fade" id="addRole" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered  modal-simple modal-add-department">
                  <div class="modal-content p-3 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="mb-2">Add Role Information</h3>
                      </div>
                      <form action="{{route('roles.store')}}" id="addRoleForm" class="row g-3"  method="POST">
                        {{csrf_field()}}
                        <input  type="hidden" class="form-control" name="created_by" value="{{ Auth::user()->id }}">
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalAddRoleName">Role Name</label>
                          <input
                            type="text"
                            id="modalAddRoleName"
                            name="modalAddRoleName"
                            class="form-control"
                            placeholder="Role" />
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label" for="modalAddRoleDescription">Description</label>
                          <input
                            type="text"
                            id="modalAddRoleDescription"
                            name="modalAddRoleDescription"
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

