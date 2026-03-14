<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTeamForm" method="POST" action="" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group mb-3">
                        <label for="edit_partner_id" class="form-label">Partner <span class="text-danger">*</span></label>
                        <select name="partner_id" id="edit_partner_id" class="form-select" required>
                            <option value="">Select Partner</option>
                            @foreach(\App\Models\Partner::where('status', 'active')->get() as $partner)
                                <option value="{{ $partner->id }}">{{ $partner->partner_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_team_type_id" class="form-label">Team Type <span class="text-danger">*</span></label>
                        <select name="team_type_id" id="edit_team_type_id" class="form-select" required>
                            <option value="">Select Partner First</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_team_name" class="form-label">Team Name <span class="text-danger">*</span></label>
                        <input type="text" name="team_name" id="edit_team_name"
                               class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea name="description" id="edit_description"
                                 class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary btn-xs" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-xs">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Update Team
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit modal show event
    const editModalElement = document.getElementById('editTeamModal');
    if (editModalElement) {
        editModalElement.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget; // Button that triggered the modal
            
            // Extract info from data-* attributes
            const id = button.getAttribute('data-id');
            const teamTypeId = button.getAttribute('data-team-type-id');
            const partnerId = button.getAttribute('data-partner-id');
            const teamName = button.getAttribute('data-team-name');
            const description = button.getAttribute('data-description');
            const status = button.getAttribute('data-status');
            
            // Update the modal's content
            const modal = this;
            modal.querySelector('#edit_id').value = id;
            modal.querySelector('#edit_partner_id').value = partnerId;
            modal.querySelector('#edit_team_type_id').value = teamTypeId;
            modal.querySelector('#edit_team_name').value = teamName;
            modal.querySelector('#edit_description').value = description || '';
            modal.querySelector('#edit_status').value = status || 'active';
            
            // Update the form action
            const form = modal.querySelector('#editTeamForm');
            if (form) {
                form.action = `/team/${id}`;
            }

            // Trigger partner change to load team types
            $('#edit_partner_id').trigger('change');
        });
    }
});
</script>
@endpush

