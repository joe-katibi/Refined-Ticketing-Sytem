@extends('layouts.contentNavbarLayout')

@section('title', 'Infrastructure New Appointments')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Infrastructure New Appointments</h5>
            <small class="text-muted">All assigned appointments with status 'Escalated-Infrastructure'</small>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="infrastructure-new-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ticket ID</th>
                            <th>Account Number</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>OLT Name</th>
                            <th>Assigned Team</th>
                            <th>Scheduled</th>
                            <th>Location</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $appointment->appointment_ticket_id ?? 'N/A' }}</td>
                                <td>{{ $appointment->account_number ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-xs bg-warning">{{ $appointment->status }}</span>
                                </td>
                                <td>{{ $appointment->priority ?? 'N/A' }}</td>
                                <td>
                                    @if($appointment->olt)
                                        {{ $appointment->olt->name }}
                                        <br><small class="text-muted">{{ $appointment->olt->location }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $appointment->assignedTeam ? $appointment->assignedTeam->name : 'N/A' }}</td>
                                <td>
                                    @if($appointment->scheduled_date)
                                        {{ $appointment->scheduled_date->format('M d, Y') }}<br>
                                        <small class="text-muted">{{ $appointment->scheduled_time ?? '' }}</small>
                                    @else
                                        Not Scheduled
                                    @endif
                                </td>
                                <td>{{ $appointment->appointment_location ?? 'N/A' }}</td>
                                <td>{{ $appointment->creator ? $appointment->creator->name : 'System' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        @can('view-site-visit-appointment')
                                        <a href="{{ route('appointment.appointments.show', $appointment->id) }}"
                                           class="btn btn-icon btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        @can('view-site-visit-appointment')
                                        <a href="{{ route('appointment.appointments.edit', $appointment->id) }}"
                                           class="btn btn-icon btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No Infrastructure appointments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  $('#infrastructure-new-table').DataTable({
    responsive: true,
    dom: 'lfrtip',
  });
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
