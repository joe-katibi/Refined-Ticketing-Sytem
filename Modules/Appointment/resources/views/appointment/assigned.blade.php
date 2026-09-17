@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Assigned Appointments')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Nav-pills for Team Types -->
    <ul class="nav nav-pills widget-nav-tabs pb-2 gap-2 mx-1 d-flex flex-nowrap" role="tablist">
        <li class="nav-item">
            <a href="#tab-inhouse" class="nav-link btn btn-primary active" data-bs-toggle="tab">
                In-House
            </a>
        </li>
        <li class="nav-item">
            <a href="#tab-outsource" class="nav-link btn btn-primary" data-bs-toggle="tab">
                Outsource Partner
            </a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- In-House Tab -->
        <div class="tab-pane fade show active" id="tab-inhouse">
            <form method="POST" action="{{ route('appointment.appointments.bulk_assign') }}" class="bulk-assign-form">
                @csrf
                @include('appointment::appointment.partials.bulk-assign-toolbar', ['teamTypes' => $teamTypes, 'formId' => 'inhouse'])

                <div class="table-responsive">
                    <table class="table table-bordered" id="datatable-inhouse">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="select-all" data-form="inhouse"></th>
                                <th>Ticket ID</th>
                                <th>Account Number</th>
                                <th>Type</th>
                                <th>Sub Type</th>
                                <th>Team Type</th>
                                <th>Sub Team Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Scheduled Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inhouseAppointments as $appointment)
                                <tr>
                                    <td>
                                        @if($appointment->status === 'scheduled-assigned-team')
                                            <input type="checkbox" name="appointment_ids[]" value="{{ $appointment->id }}" class="row-check" data-form="inhouse">
                                        @endif
                                    </td>
                                    <td>{{ $appointment->appointment_ticket_id }}</td>
                                    <td>{{ $appointment->account_number }}</td>
                                    <td>{{ $appointment->type->type_name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->subType->sub_type_name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->teamType->type_name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->subTeamType->sub_type_name ?? 'N/A' }}</td>
                                    <td>{!! $appointment->priority_badge !!}</td>
                                    <td>{!! $appointment->status_badge !!}</td>
                                    <td>
                                        @if($appointment->scheduled_date)
                                            {{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('appointment.appointments.edit_assigned', $appointment->id) }}" class="btn btn-icon btn-info btn-sm">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        <!-- Outsource Partner Tab -->
        <div class="tab-pane fade" id="tab-outsource">
            <form method="POST" action="{{ route('appointment.appointments.bulk_assign') }}" class="bulk-assign-form">
                @csrf
                @include('appointment::appointment.partials.bulk-assign-toolbar', ['teamTypes' => $teamTypes, 'formId' => 'outsource'])

                <div class="table-responsive">
                    <table class="table table-bordered" id="datatable-outsource">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="select-all" data-form="outsource"></th>
                                <th>Ticket ID</th>
                                <th>Account Number</th>
                                <th>Type</th>
                                <th>Sub Type</th>
                                <th>Team Type</th>
                                <th>Sub Team Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Scheduled Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outsourceAppointments as $appointment)
                                <tr>
                                    <td>
                                        @if($appointment->status === 'scheduled-assigned-team')
                                            <input type="checkbox" name="appointment_ids[]" value="{{ $appointment->id }}" class="row-check" data-form="outsource">
                                        @endif
                                    </td>
                                    <td>{{ $appointment->appointment_ticket_id }}</td>
                                    <td>{{ $appointment->account_number }}</td>
                                    <td>{{ $appointment->type->type_name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->subType->sub_type_name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->teamType->type_name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->subTeamType->sub_type_name ?? 'N/A' }}</td>
                                    <td>{!! $appointment->priority_badge !!}</td>
                                    <td>{!! $appointment->status_badge !!}</td>
                                    <td>
                                        @if($appointment->scheduled_date)
                                            {{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('appointment.appointments.edit_assigned', $appointment->id) }}" class="btn btn-icon btn-info btn-sm">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable for In-House
            $('#datatable-inhouse').DataTable({
                responsive: true,
                order: [[8, 'asc']], // Sort by scheduled date by default
                columnDefs: [{ orderable: false, targets: 0 }] // checkbox column
            });

            // Initialize DataTable for Outsource Partner
            $('#datatable-outsource').DataTable({
                responsive: true,
                order: [[8, 'asc']], // Sort by scheduled date by default
                columnDefs: [{ orderable: false, targets: 0 }]
            });

            function updateSelectedCount(form) {
                var count = $('.row-check[data-form="' + form + '"]:checked').length;
                $('.selected-count[data-form="' + form + '"]').text(count);
            }

            // Select-all toggles only the checkboxes belonging to its own
            // form — DataTables re-parents rows into its own wrapper, but
            // the data-form attribute keeps the two tabs' selections from
            // ever bleeding into each other.
            $(document).on('change', '.select-all', function() {
                var form = $(this).data('form');
                $('.row-check[data-form="' + form + '"]').prop('checked', this.checked);
                updateSelectedCount(form);
            });

            $(document).on('change', '.row-check', function() {
                updateSelectedCount($(this).data('form'));
            });

            // Submitting with nothing selected is a no-op worth catching
            // client-side rather than round-tripping to the server to be
            // told the same thing.
            $('.bulk-assign-form').on('submit', function(e) {
                var $form = $(this);
                var checked = $form.find('.row-check:checked').length;
                if (checked === 0) {
                    e.preventDefault();
                    alert('Select at least one appointment to bulk-assign.');
                    return false;
                }
                if (!$form.find('select[name="team_type_id"]').val() || !$form.find('select[name="sub_team_type_id"]').val()) {
                    e.preventDefault();
                    alert('Choose a Team and Sub Team before bulk-assigning.');
                    return false;
                }
            });
        });
    </script>
    @include('appointment::appointment.partials.bulk-assign-cascade-script')
@endsection
