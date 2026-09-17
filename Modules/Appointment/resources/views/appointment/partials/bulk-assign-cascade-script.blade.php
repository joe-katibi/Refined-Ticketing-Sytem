{{--
    Team Type -> Sub Team Type -> Technician cascade for the bulk-assign
    toolbars, mirroring appointment/edit.blade.php's single-appointment
    cascade exactly (same AJAX endpoints, same response shapes) but scoped
    per-form via data-form, since the In-House and Outsource Partner tabs
    each have their own independent toolbar/form on this one page.
--}}
<script>
$(document).ready(function() {
    function loadSubTeams(form, teamTypeId) {
        var subTeamSelect = $('#sub_team_type_id_' + form);
        var assignedToSelect = $('#assigned_to_' + form);

        assignedToSelect.empty().append('<option value="">Select Sub Team First</option>').prop('disabled', true);

        if (!teamTypeId) {
            subTeamSelect.empty().append('<option value="">Select Team First</option>').prop('disabled', true);
            return;
        }

        subTeamSelect.empty().prop('disabled', true).append('<option value="">Loading...</option>');

        $.ajax({
            url: '/teamtypes/' + teamTypeId + '/sub-teams',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                subTeamSelect.empty().prop('disabled', false).append('<option value="">Select Sub Team</option>');
                if (Array.isArray(data) && data.length > 0) {
                    $.each(data, function(i, subTeam) {
                        subTeamSelect.append('<option value="' + subTeam.id + '">' + subTeam.name + '</option>');
                    });
                } else {
                    subTeamSelect.append('<option value="">No sub teams found</option>');
                }
            },
            error: function() {
                subTeamSelect.empty().prop('disabled', false).append('<option value="">Error loading sub teams</option>');
            }
        });
    }

    function loadTechnicians(form, subTeamTypeId) {
        var assignedToSelect = $('#assigned_to_' + form);

        if (!subTeamTypeId) {
            assignedToSelect.empty().append('<option value="">Select Sub Team First</option>').prop('disabled', true);
            return;
        }

        assignedToSelect.empty().prop('disabled', true).append('<option value="">Loading...</option>');

        $.ajax({
            url: '/appointments/sub-team-types/' + subTeamTypeId + '/users',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                assignedToSelect.empty().prop('disabled', false).append('<option value="">Select Technician (optional)</option>');
                var users = (response && response.users) ? response.users : [];
                if (users.length > 0) {
                    $.each(users, function(i, user) {
                        assignedToSelect.append('<option value="' + user.id + '">' + user.name + '</option>');
                    });
                } else {
                    assignedToSelect.append('<option value="">No technicians in this sub team</option>');
                }
            },
            error: function() {
                assignedToSelect.empty().prop('disabled', false).append('<option value="">Error loading technicians</option>');
            }
        });
    }

    function loadAssignedTeams(form) {
        var assignedTeamSelect = $('#assigned_team_id_' + form);

        assignedTeamSelect.empty().prop('disabled', true).append('<option value="">Loading...</option>');

        $.ajax({
            url: '/teams',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                assignedTeamSelect.empty().prop('disabled', false).append('<option value="">Select Team</option>');
                if (Array.isArray(data) && data.length > 0) {
                    $.each(data, function(i, team) {
                        assignedTeamSelect.append('<option value="' + team.id + '">' + team.team_name + '</option>');
                    });
                } else {
                    assignedTeamSelect.append('<option value="">No teams available</option>');
                }
            },
            error: function() {
                assignedTeamSelect.empty().prop('disabled', false).append('<option value="">Error loading teams</option>');
            }
        });
    }

    $(document).on('change', '.bulk-team-type', function() {
        var form = $(this).data('form');
        var teamTypeId = $(this).val();

        loadSubTeams(form, teamTypeId);

        var assignedTeamCol = $('.bulk-assigned-team-col[data-form="' + form + '"]');
        if (teamTypeId == 2) {
            assignedTeamCol.show();
            loadAssignedTeams(form);
        } else {
            assignedTeamCol.hide();
            $('#assigned_team_id_' + form).val('');
        }
    });

    $(document).on('change', '.bulk-sub-team-type', function() {
        loadTechnicians($(this).data('form'), $(this).val());
    });
});
</script>
