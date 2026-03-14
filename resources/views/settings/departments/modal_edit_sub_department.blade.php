<!-- Edit Sub Department Modal -->
<div class="modal fade" id="editSubDepartment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-simple modal-edit-sub-department">
        <div class="modal-content p-2 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2">Edit Sub Department</h3>
                    <p class="text-muted">Update sub department information</p>
                </div>

                <form id="editSubDepartmentForm" action="" class="row g-3" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" class="form-control" name="edited_by" value="{{ Auth::user()->id }}">
                    
                    <div class="col-12">
                        <label class="form-label" for="edit_sub_department_name">Sub Department Name</label>
                        <input
                            type="text"
                            id="edit_sub_department_name"
                            name="sub_department_name"
                            value=""
                            class="form-control"
                            placeholder="Enter sub department name"
                            required />
                    </div>
                    
                    <div class="col-12 text-center">
                        <button
                            type="reset"
                            class="btn btn-label-secondary btn-xs"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Update Sub Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- / Edit Sub Department Modal -->

