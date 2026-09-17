{{--
    Bulk-assign toolbar: Team Type / Sub Team Type / Technician / Assign Team,
    the same fields the single-appointment edit() form sets, applied to every
    checked row in this tab's table. $formId keeps element IDs unique between
    the In-House and Outsource Partner tabs, which each render this partial
    with their own <form>.
--}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="team_type_id_{{ $formId }}" class="form-label mb-1">Team</label>
                <select name="team_type_id" id="team_type_id_{{ $formId }}" class="form-select bulk-team-type" data-form="{{ $formId }}" required>
                    <option value="">Select Team</option>
                    @foreach($teamTypes as $teamType)
                        <option value="{{ $teamType->id }}">{{ $teamType->type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="sub_team_type_id_{{ $formId }}" class="form-label mb-1">Sub Team</label>
                <select name="sub_team_type_id" id="sub_team_type_id_{{ $formId }}" class="form-select bulk-sub-team-type" data-form="{{ $formId }}" disabled required>
                    <option value="">Select Team First</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="assigned_to_{{ $formId }}" class="form-label mb-1">Technician (optional)</label>
                <select name="assigned_to" id="assigned_to_{{ $formId }}" class="form-select bulk-assigned-to" data-form="{{ $formId }}" disabled>
                    <option value="">Select Sub Team First</option>
                </select>
            </div>
            <div class="col-md-3 bulk-assigned-team-col" data-form="{{ $formId }}" style="display: none;">
                <label for="assigned_team_id_{{ $formId }}" class="form-label mb-1">Assign Team (Outsource)</label>
                <select name="assigned_team_id" id="assigned_team_id_{{ $formId }}" class="form-select bulk-assigned-team" data-form="{{ $formId }}">
                    <option value="">Select Team</option>
                </select>
            </div>
        </div>
        <div class="mt-2">
            <span class="text-muted small"><span class="selected-count" data-form="{{ $formId }}">0</span> selected</span>
            <button type="submit" class="btn btn-primary btn-xs ms-2">
                <i class="bx bx-group me-1"></i>
                Bulk Assign Selected
            </button>
        </div>
    </div>
</div>
