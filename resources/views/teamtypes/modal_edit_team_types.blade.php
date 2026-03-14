<!-- Debug: Modal included -->
<script>
    console.log('Modal template included in DOM');
    
    // Check if the modal element exists
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editTeamTypeModal');
        console.log('Modal element found:', !!modal);
        if (modal) {
            console.log('Modal HTML:', modal.outerHTML);
        }
    });
</script>

<!-- Edit Team Type Modal -->
<div class="modal fade" id="editTeamTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Team Type') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTeamTypeForm" method="POST" action="" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">

                    <div class="form-group mb-3">
                        <label for="edit_type_name" class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="type_name" id="edit_type_name"
                               class="form-control @error('type_name') is-invalid @enderror"
                               required>
                        @error('type_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea name="description" id="edit_description"
                                 class="form-control @error('description') is-invalid @enderror"
                                 rows="3"></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary btn-xs" data-bs-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary btn-xs">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            {{ __('Update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function initializeEditModal() {
    // Edit button click handler
    document.querySelectorAll('.edit-team-type').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Edit button clicked');
            console.log('Button attributes:', this.attributes);

            let teamType = {};

            // Check if data is passed as a JSON string in data-team-type
            const teamTypeData = this.getAttribute('data-team-type');
            console.log('Raw teamTypeData:', teamTypeData);

            if (teamTypeData) {
                try {
                    teamType = JSON.parse(teamTypeData);
                    console.log('Parsed teamType from JSON:', teamType);
                } catch (e) {
                    console.error('Error parsing teamTypeData:', e);
                }
            } else {
                // Fallback to individual data attributes
                teamType = {
                    id: this.getAttribute('data-id'),
                    type_name: this.getAttribute('data-type_name'),
                    description: this.getAttribute('data-description'),
                    status: this.getAttribute('data-status')
                };
                console.log('TeamType from individual attributes:', teamType);
            }

            // Debug: Log the form and fields
            const form = document.getElementById('editTeamTypeForm');
            console.log('Form element:', form);
            console.log('Form fields:', {
                id: document.getElementById('edit_id'),
                type_name: document.getElementById('edit_type_name'),
                description: document.getElementById('edit_description'),
                status: document.getElementById('edit_status')
            });

            if (!form) {
                console.error('Form with ID editTeamTypeForm not found!');
                return;
            }

            // Set form action URL
            form.action = `/teamtypes/${teamType.id}`;

            // Populate form fields
            const fields = {
                'edit_id': teamType.id,
                'edit_type_name': teamType.type_name || '',
                'edit_description': teamType.description || '',
                'edit_status': teamType.status || 'Active'
            };

            Object.entries(fields).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    console.log(`Setting ${id} to:`, value);
                    element.value = value;
                } else {
                    console.error(`Element with ID ${id} not found!`);
                }
            });

            // Initialize and show the modal
            const editModalElement = document.getElementById('editTeamTypeModal');
            const editModal = new bootstrap.Modal(editModalElement);
            editModal.show();
        });
    });

    // Handle edit form submission
    const editForm = document.getElementById('editTeamTypeForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = editForm.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');

            // Show loading state
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(editForm);

            fetch(editForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.success) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTeamTypeModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Team type updated successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    // Handle validation errors
                    let errorMessage = 'An error occurred while updating the team type.';
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).join('\n');
                    } else if (data.message) {
                        errorMessage = data.message;
                    }

                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                // Reset loading state
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            });
        });
    }
}

// Listen for the modal show event
const editModalElement = document.getElementById('editTeamTypeModal');
if (editModalElement) {
    editModalElement.addEventListener('show.bs.modal', function(event) {
        console.log('Modal show event triggered');
        const button = event.relatedTarget; // Button that triggered the modal
        
        // Extract info from data-* attributes
        const id = button.getAttribute('data-id');
        const typeName = button.getAttribute('data-type_name');
        const description = button.getAttribute('data-description');
        const status = button.getAttribute('data-status');
        
        console.log('Modal data:', {id, typeName, description, status});
        
        // Update the modal's content
        const modal = this;
        modal.querySelector('#edit_id').value = id;
        modal.querySelector('#edit_type_name').value = typeName || '';
        modal.querySelector('#edit_description').value = description || '';
        modal.querySelector('#edit_status').value = status || 'Active';
        
        // Update the form action
        const form = modal.querySelector('#editTeamTypeForm');
        if (form) {
            form.action = `/teamtypes/${id}`;
        }
    });
}

// Also initialize the modal on page load as a fallback
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    // Check if we have any edit buttons
    const editButtons = document.querySelectorAll('.edit-team-type');
    console.log('Found edit buttons:', editButtons.length);
    
    // Add click handler to each button as a fallback
    editButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            console.log('Edit button clicked (fallback)');
            const id = this.getAttribute('data-id');
            const typeName = this.getAttribute('data-type_name');
            const description = this.getAttribute('data-description');
            const status = this.getAttribute('data-status');
            
            console.log('Button data:', {id, typeName, description, status});
            
            // Set form values
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_type_name').value = typeName || '';
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit_status').value = status || 'Active';
            
            // Update form action
            const form = document.getElementById('editTeamTypeForm');
            if (form) {
                form.action = `/teamtypes/${id}`;
            }
        });
    });
});
</script>
@endpush

