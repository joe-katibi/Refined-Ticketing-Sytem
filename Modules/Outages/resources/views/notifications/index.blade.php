@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')
@section('title', 'Outage Notifications')

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
                    <h4 class="card-title">Outage Notifications</h4>
                    <form action="{{ route('outages.notifications.mark-all-read') }}" method="POST" class="ml-auto">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-xs">
                            <i class='bx bx-check-double'></i> Mark All as Read
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
                                    <th width="15%">Outage</th>
                                    <th width="15%">Date</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                <tr class="{{ $notification->read ? '' : 'table-info' }}">
                                    <td class="text-center">
                                        @if($notification->read)
                                            <i class='bx bx-check-circle text-muted'></i>
                                        @else
                                            <i class='bx bx-bell text-primary'></i>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($notification->type)
                                            @case('create')
                                                <span class="badge bg-success" style="font-size: 13px; padding: 8px 12px;">Created</span>
                                                @break
                                            @case('update')
                                                <span class="badge bg-info" style="font-size: 13px; padding: 8px 12px;">Updated</span>
                                                @break
                                            @case('assign')
                                                <span class="badge bg-primary" style="font-size: 13px; padding: 8px 12px;">Assigned</span>
                                                @break
                                            @case('close')
                                                <span class="badge bg-secondary" style="font-size: 13px; padding: 8px 12px;">Closed</span>
                                                @break
                                            @case('status_change')
                                                <span class="badge bg-warning" style="font-size: 13px; padding: 8px 12px;">Status Changed</span>
                                                @break
                                            @case('download')
                                                <span class="badge bg-dark" style="font-size: 13px; padding: 8px 12px;">Downloaded</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark" style="font-size: 13px; padding: 8px 12px;">{{ ucfirst($notification->type) }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $notification->message }}</td>
                                    <td>
                                        @if($notification->outage)
                                            <a href="{{ route('outages.show', $notification->outage->id) }}" class="text-decoration-none">
                                                {{ $notification->outage->ticket_number }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group">
                                            @if(!$notification->read)
                                                <form action="{{ route('outages.notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class='bx bx-check'></i> Mark Read
                                                    </button>
                                                </form>
                                            @endif
                                            @if($notification->outage)
                                                <a href="{{ route('outages.show', $notification->outage->id) }}" class="btn btn-info btn-sm ms-1">
                                                    <i class='bx bx-show'></i> View
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="d-flex flex-column align-items-center py-4">
                                            <i class='bx bx-bell-off' style="font-size: 3rem; color: #ccc;"></i>
                                            <h5 class="mt-2 text-muted">No notifications found</h5>
                                            <p class="text-muted">You're all caught up! No outage notifications to display.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($notifications->hasPages())
                        <div class="mt-3">
                            {{ $notifications->links() }}
                        </div>
                    @endif
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
                // This is likely a "No data available" row, skip DataTables initialization
                console.info('DataTables initialization: Empty table with colspan detected, skipping DataTables');
                return false;
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
