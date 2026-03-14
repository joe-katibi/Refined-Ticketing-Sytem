              <!-- Add Department Modal -->
<div class="modal fade" id="addDepartment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-simple modal-add-department">
        <div class="modal-content p-3 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2">Add Department Information</h3>
                </div>
                <form action="{{ route('settings.departments.post') }}" id="addDepartmentForm" class="row g-3" method="POST">
                    @csrf
                    <input type="hidden" name="created_by" value="{{ Auth::user()->id }}">
                    
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="add_department_name">Department Name</label>
                        <input type="text"
                               id="add_department_name"
                               name="modalAddDepartmentName"
                               class="form-control"
                               placeholder="Department Name" />
                    </div>
                    
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="add_department_description">Description</label>
                        <input type="text"
                               id="add_department_description"
                               name="modalAddDepartmentDescription"
                               class="form-control"
                               placeholder="Department Description" />
                    </div>
                    
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-label-secondary btn-xs" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Add Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

