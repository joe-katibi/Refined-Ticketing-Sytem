@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')
@section('title', 'Appointment Notifications')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Appointment Notifications</h4>
                    <form action="{{ route('appointment.notifications.mark-all-read') }}" method="POST" class="ml-auto">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-xs">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="notification-datatable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">Status</th>
                                    <th width="15%">Type</th>
                                    <th width="40%">Message</th>
                                    <th width="15%">Appointment</th>
                                    <th width="15%">Date</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                <tr class="{{ $notification->read ? '' : 'table-info' }}">
                                    <td class="text-center">
                                        @if($notification->read)
                                            <i class="fas fa-check-circle text-muted"></i>
                                        @else
                                            <i class="fas fa-bell text-primary"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($notification->type)
                                            @case('create')
                                                <span class="badge badge-success text-dark" style="font-size: 13px; padding: 8px 12px;">Created</span>
                                                @break
                                            @case('update')
                                                <span class="badge badge-info text-dark" style="font-size: 13px; padding: 8px 12px;">Updated</span>
                                                @break
                                            @case('assign')
                                                <span class="badge badge-primary text-dark" style="font-size: 13px; padding: 8px 12px;">Assigned</span>
                                                @break
                                            @case('close')
                                                <span class="badge badge-secondary text-dark" style="font-size: 13px; padding: 8px 12px;">Closed</span>
                                                @break
                                            @case('sla_breach')
                                                <span class="badge badge-danger text-dark" style="font-size: 13px; padding: 8px 12px;">SLA Breach</span>
                                                @break
                                            @case('sla_warning')
                                                <span class="badge badge-xs bg-warning text-dark" style="font-size: 13px; padding: 8px 12px;">SLA Warning</span>
                                                @break
                                            @default
                                                <span class="badge badge-dark text-white" style="font-size: 13px; padding: 8px 12px;">{{ $notification->type }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $notification->message }}</td>
                                    <td>
                                        @if($notification->appointment)
                                            <a href="{{ route('appointment.appointments.show', $notification->appointment->id) }}">
                                                {{ $notification->appointment->ticket_id }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group">
                                            @if(!$notification->read)
                                                <form action="{{ route('appointment.notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-xs">
                                                        <i class="fas fa-check"></i> Mark Read
                                                    </button>
                                                </form>
                                            @endif
                                            @if($notification->appointment)
                                                <a href="{{ route('appointment.appointments.show', $notification->appointment->id) }}" class="btn btn-info ml-1">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No notifications found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Function to validate table structure before initializing DataTables
    function validateTableStructure() {
        // Get the table element
        const table = document.getElementById('notification-datatable');
        if (!table) {
            console.error('DataTables initialization error: Table element not found');
            return false;
        }

        // Check if table has any rows
        if (table.rows.length === 0) {
            console.info('DataTables initialization skipped: Table has no rows');
            return false;
        }

        // Check if table has thead and tbody
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        if (!thead || !tbody) {
            console.error('DataTables initialization error: Table missing thead or tbody');
            return false;
        }

        // Check if there are any rows with colspan that spans all columns
        const headerCells = thead.rows[0].cells.length;
        const bodyRows = tbody.rows;

        for (let i = 0; i < bodyRows.length; i++) {
            const row = bodyRows[i];
            if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) {
                // This is likely a "No data available" row, skip validation
                console.info('DataTables initialization: Empty table with colspan detected');
                return true;
            }
        }

        // Validate that all rows have the same number of cells
        for (let i = 0; i < bodyRows.length; i++) {
            const cellCount = bodyRows[i].cells.length;
            if (cellCount !== headerCells) {
                console.error(`DataTables initialization error: Row ${i} has ${cellCount} cells, but header has ${headerCells} cells`);
                return false;
            }
        }

        return true;
    }

    // Initialize DataTables if table structure is valid
    if (validateTableStructure()) {
        try {
            $('#notification-datatable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[4, 'desc']], // Sort by date column (index 4) in descending order
                language: {
                    paginate: {
                        previous: '&nbsp;',
                        next: '&nbsp;'
                    }
                }
            });
            console.log('DataTables initialized successfully');
        } catch (error) {
            console.error('DataTables initialization error:', error);
        }
    }
});
</script>
@endsection

