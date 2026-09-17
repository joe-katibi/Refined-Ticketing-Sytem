@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Open Tickets Report - Appointments')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <x-toast-notification />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="fas fa-hourglass-half text-warning me-2"></i>
                            Open Tickets Report
                        </h4>
                        <p class="text-muted mb-0">Still-open appointments broken down by OLT, sub category, and team</p>
                    </div>
                    <div>
                        <a href="{{ route('appointment.reports.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Reports
                        </a>
                        <a href="{{ route('appointment.reports.export-excel', ['report_type' => 'open_tickets', 'startDate' => $startDate, 'endDate' => $endDate]) }}" class="btn btn-success btn-xs">
                            <i class="fas fa-file-excel me-1"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form method="GET" action="{{ route('appointment.reports.open-tickets') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="startDate" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="startDate" name="startDate" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-4">
                                <label for="endDate" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="endDate" name="endDate" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-xs">
                                        <i class="fas fa-filter me-1"></i>
                                        Apply Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Total -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="small-box bg-warning text-dark p-3 rounded text-center">
                                <h3>{{ $totalOpen }}</h3>
                                <p class="mb-0">Total Open Tickets</p>
                            </div>
                        </div>
                    </div>

                    <!-- Three Breakdown Tables -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header"><h6 class="card-title mb-0"><i class="fas fa-broadcast-tower me-1"></i> By OLT</h6></div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm mb-0">
                                            <thead class="table-dark"><tr><th>OLT</th><th class="text-center">Open</th></tr></thead>
                                            <tbody>
                                                @forelse($byOlt as $item)
                                                <tr><td>{{ $item->name }}</td><td class="text-center"><span class="badge badge-xs bg-warning">{{ $item->open_count }}</span></td></tr>
                                                @empty
                                                <tr><td colspan="2" class="text-center text-muted">No data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header"><h6 class="card-title mb-0"><i class="fas fa-tags me-1"></i> By Sub Category</h6></div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm mb-0">
                                            <thead class="table-dark"><tr><th>Sub Category</th><th class="text-center">Open</th></tr></thead>
                                            <tbody>
                                                @forelse($bySubCategory as $item)
                                                <tr><td>{{ $item->sub_category_name }}</td><td class="text-center"><span class="badge badge-xs bg-warning">{{ $item->open_count }}</span></td></tr>
                                                @empty
                                                <tr><td colspan="2" class="text-center text-muted">No data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header"><h6 class="card-title mb-0"><i class="fas fa-users me-1"></i> By Team</h6></div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm mb-0">
                                            <thead class="table-dark"><tr><th>Team</th><th class="text-center">Open</th></tr></thead>
                                            <tbody>
                                                @forelse($byTeam as $item)
                                                <tr><td>{{ $item->team_name }}</td><td class="text-center"><span class="badge badge-xs bg-warning">{{ $item->open_count }}</span></td></tr>
                                                @empty
                                                <tr><td colspan="2" class="text-center text-muted">No data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Open Ticket Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ticket ID</th>
                                                    <th>Account Number</th>
                                                    <th>Status</th>
                                                    <th>OLT</th>
                                                    <th>Sub Category</th>
                                                    <th>Team</th>
                                                    <th>Time Since Raised</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($tickets as $ticket)
                                                <tr>
                                                    <td>{{ $ticket->appointment_ticket_id ?? 'N/A' }}</td>
                                                    <td>{{ $ticket->account_number ?? 'N/A' }}</td>
                                                    <td>{{ $ticket->status }}</td>
                                                    <td>{{ $ticket->olt_name }}</td>
                                                    <td>{{ $ticket->sub_category_name }}</td>
                                                    <td>{{ $ticket->team_name }}</td>
                                                    <td><span class="badge badge-xs bg-secondary">{{ $ticket->age_bucket }}</span></td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        No open tickets found for the selected date range
                                                    </td>
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
            </div>
        </div>
    </div>
</div>
@endsection
