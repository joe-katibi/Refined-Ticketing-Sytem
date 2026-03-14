@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Appointment Histories')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Appointment /</span> Histories
    </h4>

    <!-- Nav-pills for Appointment Types -->
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
                                <th>Status</th>
                                <th>Action By</th>
                                <th>Priority</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historiesByType[$type->id] as $appointmentId => $histories)
                                @php $latestHistory = $histories->first(); @endphp
                                <tr>
                                    <td>{{ $latestHistory->ticket_id ?? 'N/A' }}</td>
                                    <td>{{ $appointmentId }}</td>
                                    <td>
                                        <span class="badge badge-xs bg-label-{{
                                            $latestHistory->status === 'completed' ? 'success' :
                                            ($latestHistory->status === 'in_progress' ? 'primary' : 'warning')
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $latestHistory->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $latestHistory->actionBy->name ?? 'System' }}</td>
                                    <td>{{ ucfirst($latestHistory->priority) }}</td>
                                    <td>{{ $latestHistory->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('appointment.history.show', $appointmentId) }}"
                                           class="btn btn-icon btn-xs">
                                            <i class='bx bx-show'></i>
                                        </a>
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

@push('scripts')
<script>
    // Initialize DataTables for each tab
    @foreach($appointmentTypes as $type)
        $('#datatable-{{ $type->id }}').DataTable({
            responsive: true,
            dom: 'lfrtip',
            order: [[5, 'desc']] // Sort by created_at by default
        });
    @endforeach
</script>
@endpush

@endsection

