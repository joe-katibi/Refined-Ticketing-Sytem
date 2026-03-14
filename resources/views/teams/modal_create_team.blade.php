<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createTeamForm" method="POST" action="{{ route('team.store') }}" novalidate>
                    @csrf

                    <div class="form-group mb-3">
                        <label for="team_type_id" class="form-label">Team Type <span class="text-danger">*</span></label>
                        <select name="team_type_id" id="team_type_id" class="form-select @error('team_type_id') is-invalid @enderror" required>
                            <option value="">Select Team Type</option>
                            @foreach($teamTypes as $type)
                                <option value="{{ $type->id }}" {{ old('team_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('team_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Partner Selection (shown only for Outsource) -->
                    <div class="form-group mb-3" id="partnerField" style="display: none;">
                        <label for="partner_id" class="form-label">Partner <span class="text-danger">*</span></label>
                        <select name="partner_id" id="partner_id" class="form-select @error('partner_id') is-invalid @enderror">
                            <option value="">Select Partner</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->partner_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('partner_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Users Selection (shown only for Inhouse) -->
                    <div class="form-group mb-3" id="usersField" style="display: none;">
                        <label for="users" class="form-label">Select Technicians <span class="text-danger">*</span></label>
                        <select name="users[]" id="users" class="form-select select2 @error('users') is-invalid @enderror" multiple>
                            @foreach($inhouseTechnicians as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('users')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="team_name" class="form-label">Team Name <span class="text-danger">*</span></label>
                        <input type="text" name="team_name" id="team_name"
                               class="form-control @error('team_name') is-invalid @enderror"
                               value="{{ old('team_name') }}" required>
                        @error('team_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description"
                                 class="form-control @error('description') is-invalid @enderror"
                                 rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary btn-xs" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-xs">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Create Team
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for users multi-select
        if ($.fn.select2) {
            $('.select2').select2({
                placeholder: 'Select Technicians',
                allowClear: true,
                width: '100%'
            });
        }

        // Handle team type change
        $('#team_type_id').on('change', function() {
            const teamTypeId = $(this).val();
            const partnerField = $('#partnerField');
            const usersField = $('#usersField');
            const partnerSelect = $('#partner_id');
            const usersSelect = $('#users');

            // Reset fields
            partnerSelect.prop('required', false);
            usersSelect.prop('required', false);
            partnerField.hide();
            usersField.hide();

            if (!teamTypeId) return;

            // Get the selected team type name
            const teamTypeName = $(this).find('option:selected').text().toLowerCase();

            if (teamTypeName.includes('outsource')) {
                // Show partner field for outsource teams
                partnerSelect.prop('required', true);
                partnerField.show();
            } else if (teamTypeName.includes('inhouse')) {
                // Show users field for inhouse teams
                usersSelect.prop('required', true);
                usersField.show();
            }
        });

        // Trigger change on page load if there's a selected team type
        if ($('#team_type_id').val()) {
            $('#team_type_id').trigger('change');
        }
    });
</script>
@endpush

