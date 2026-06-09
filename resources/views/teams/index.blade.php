@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Teams')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Teams List</h4>
                        <button type="button" class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                            <i class="fas fa-plus"></i> Add New Team
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered" id="teams-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Team Name</th>
                                    <th>Team Type</th>
                                    <th>Partner</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($teams as $index => $team)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $team->team_name }}</td>
                                        <td>{{ $team->teamType->type_name ?? 'N/A' }}</td>
                                        <td>{{ $team->partner->partner_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-xs bg-label-{{ $team->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($team->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $team->creator->name ?? 'System' }}</td>
                                        <td>{{ $team->updated_at->diffForHumans() }}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary edit-team"
                                                data-bs-toggle="modal" data-bs-target="#editTeamModal"
                                                data-id="{{ $team->id }}"
                                                data-team-type-id="{{ $team->team_type_id }}"
                                                data-partner-id="{{ $team->partner_id }}"
                                                data-team-name="{{ $team->team_name }}"
                                                data-description="{{ $team->description }}"
                                                data-status="{{ $team->status }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            {{-- <form action="{{ route('team.destroy', $team->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this team?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No teams found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $teams->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Team Modal -->
    @include('teams.modal_create_team', [
        'teamTypes' => $teamTypes,
        'partners' => $partners,
        'inhouseTechnicians' => $inhouseTechnicians
    ])

    <!-- Edit Team Modal -->
    @include('teams.modal_edit_team')


    @push('page-scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#teams-table').DataTable({
                responsive: true,
                paging: false,
                searching: true,
                ordering: true,
                info: false,
                lengthChange: false
            });

            // Handle partner change to load team types
            $('#partner_id, #edit_partner_id').on('change', function() {
                const partnerId = $(this).val();
                const teamTypeSelect = $(this).closest('form').find('select[name="team_type_id"]');

                if (partnerId) {
                    // Clear existing options
                    teamTypeSelect.html('<option value="">Loading team types...</option>');

                    // Fetch team types for the selected partner
                    $.get(`/api/partners/${partnerId}/team-types`, function(data) {
                        if (data.length > 0) {
                            let options = '<option value="">Select Team Type</option>';
                            data.forEach(function(teamType) {
                                options += `<option value="${teamType.id}">${teamType.type_name}</option>`;
                            });
                            teamTypeSelect.html(options);
                        } else {
                            teamTypeSelect.html('<option value="">No team types available</option>');
                        }
                    }).fail(function() {
                        teamTypeSelect.html('<option value="">Error loading team types</option>');
                    });
                } else {
                    teamTypeSelect.html('<option value="">Select a partner first</option>');
                }
            });

            // Handle form submissions
            $('#createTeamForm, #editTeamForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const spinner = submitBtn.find('.spinner-border');

                // Show loading state
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                // Submit form via AJAX
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'Operation completed successfully',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Close modal and refresh the teams table instead of full page reload
                            $('.modal').modal('hide');
                            // Reload the teams data via AJAX (if you have a function for this)
                            if (typeof loadTeams === 'function') {
                                loadTeams();
                            } else {
                                // Fallback: just remove the modal from backdrop
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');
                                $('.modal').hide();
                            }
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).join('\n');
                        }

                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
