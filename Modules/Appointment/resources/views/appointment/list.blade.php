@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Appointment List')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('installation-upload.create') }}" class="btn btn-outline-primary btn-xs">
            <i class="bx bx-upload me-1"></i>Bulk Upload Installation Data
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Nav-pills for Appointment Types -->
    @if($appointmentTypes->count() > 0)
    <ul class="nav nav-pills widget-nav-tabs pb-2 gap-2 mx-1 d-flex flex-nowrap" role="tablist">
        @foreach($appointmentTypes as $type)
            <li class="nav-item">
                <a href="#tab-type-{{ $type->id }}"
                   class="nav-link btn bg-warning btn-xs{{ $loop->first ? ' active' : '' }}"
                   data-bs-toggle="tab">
                    {{ $type->type_name }}
                </a>
            </li>
        @endforeach
    </ul>
    @else
    <div class="alert alert-info">
        <h4>No Appointment Types Found</h4>
        <p>No appointment types have been configured yet. Please create appointment types first to view the appointment list.</p>
        <a href="{{ route('appointment.types.create') }}" class="btn btn-primary">Create Appointment Type</a>
    </div>
    @endif

    <div class="tab-content mt-3">
        @foreach($appointmentTypes as $type)
            <div class="tab-pane fade{{ $loop->first ? ' show active' : '' }}"
                 id="tab-type-{{ $type->id }}">
                <div class="table-responsive">
                    <table class="table table-bordered" id="datatable-{{ $type->id }}">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Appointment ID</th>
                                <th>Account Number</th>
                                <th>Type</th>
                                <th>Sub Type</th>
                                <th>OLT</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Scheduled Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointmentsByType[$type->id] ?? [] as $appointment)
                                <tr>
                                    <td>{{ $appointment->escalation_ticket_id ?? 'N/A' }}</td>
                                    <td>{{ $appointment->appointment_ticket_id ?? 'N/A' }}</td>
                                    <td>{{ $appointment->account_number ?? 'N/A' }}</td>
                                    <td>{{ $appointment->type->type_name ?? 'N/A' }}</td>
                                    <td>{{ $appointment->subType->sub_type_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($appointment->olt)
                                            <span class="badge badge-xs bg-info">{{ $appointment->olt->name }}</span>
                                            <br><small class="text-muted">{{ $appointment->olt->location ?? 'No location' }}</small>
                                            @if($appointment->slot_id)
                                                <br><small class="text-muted">Slot: {{ $appointment->slot_id }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-xs bg-secondary">No OLT</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-xs bg-label-{{
                                            $appointment->priority === 'high' ? 'danger' :
                                            ($appointment->priority === 'medium' ? 'warning' : 'primary')
                                        }}">
                                            {{ ucfirst($appointment->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-xs bg-label-{{
                                            $appointment->status === 'completed' ? 'success' :
                                            ($appointment->status === 'in_progress' ? 'primary' : 'warning')
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $appointment->scheduled_date ? \Carbon\Carbon::parse($appointment->scheduled_date)->format('Y-m-d') : 'N/A' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('appointment.appointments.show', $appointment->id) }}"
                                              class="btn btn-icon btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                              <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('appointment.appointments.edit', $appointment->id) }}"
                                              class="btn btn-icon btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                              <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
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
  // Initialize DataTables for each tab
  @foreach($appointmentTypes as $type)
      $('#datatable-{{ $type->id }}').DataTable({
          responsive: true,
          dom: 'lfrtip',
          order: [[0, 'desc']] // Sort by scheduled date by default
      });
  @endforeach

  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
  });
</script>
@endsection
