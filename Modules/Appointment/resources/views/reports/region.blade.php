@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Region Report - Appointments')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <x-toast-notification />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            Region Report
                        </h4>
                        <p class="text-muted mb-0">SLA status, feedback, and final reason per appointment, broken down by region</p>
                    </div>
                    <div>
                        <a href="{{ route('appointment.reports.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Reports
                        </a>
                        <a href="{{ route('appointment.reports.export-excel', ['report_type' => 'region', 'startDate' => $startDate, 'endDate' => $endDate]) }}" class="btn btn-success btn-xs">
                            <i class="fas fa-file-excel me-1"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form method="GET" action="{{ route('appointment.reports.region') }}" class="mb-4">
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

                    <!-- Region Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Region Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Region</th>
                                                    <th class="text-center">Total</th>
                                                    <th class="text-center">Closed</th>
                                                    <th class="text-center">Open</th>
                                                    <th class="text-center">SLA Compliance</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($regionMetrics as $metric)
                                                @php
                                                    $statusClass = $metric->sla_compliance_percentage >= 80 ? 'success' : ($metric->sla_compliance_percentage >= 60 ? 'warning' : 'danger');
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $metric->name }}</strong></td>
                                                    <td class="text-center"><span class="badge badge-xs bg-info">{{ $metric->total_appointments }}</span></td>
                                                    <td class="text-center"><span class="badge badge-xs bg-success">{{ $metric->closed_appointments }}</span></td>
                                                    <td class="text-center"><span class="badge badge-xs bg-warning">{{ $metric->open_appointments }}</span></td>
                                                    <td class="text-center"><span class="badge badge-xs bg-{{ $statusClass }}">{{ $metric->sla_compliance_percentage }}%</span></td>
                                                    <td class="text-center">
                                                        @if($metric->sla_compliance_percentage >= 80)
                                                            <i class="fas fa-check-circle text-success" title="Excellent"></i>
                                                        @elseif($metric->sla_compliance_percentage >= 60)
                                                            <i class="fas fa-exclamation-triangle text-warning" title="Needs Attention"></i>
                                                        @else
                                                            <i class="fas fa-times-circle text-danger" title="Critical"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        No appointments with a region assigned in the selected date range
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

                    <!-- Per-Region Detail -->
                    @forelse($regionGroups as $regionName => $rows)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        {{ $regionName }}
                                    </h5>
                                    <span class="badge badge-xs bg-secondary">{{ $rows->count() }} appointment(s)</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Ticket ID</th>
                                                    <th>Account Number</th>
                                                    <th>Status</th>
                                                    <th>SLA</th>
                                                    <th>Time Since Raised</th>
                                                    <th>Feedback</th>
                                                    <th>Final Reason</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($rows as $row)
                                                <tr>
                                                    <td>{{ $row->appointment_ticket_id ?? 'N/A' }}</td>
                                                    <td>{{ $row->account_number ?? 'N/A' }}</td>
                                                    <td>{{ $row->status }}</td>
                                                    <td><span class="badge badge-xs bg-{{ $row->sla_class }}">{{ $row->sla_status }}</span></td>
                                                    <td><span class="badge badge-xs bg-secondary">{{ $row->age_bucket }}</span></td>
                                                    <td>{{ $row->feedback ? \Illuminate\Support\Str::limit($row->feedback, 80) : 'N/A' }}</td>
                                                    <td>{{ $row->final_reason_name ?? 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No appointments found for the selected date range.
                    </div>
                    @endforelse

                    <!-- Legend -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">SLA Legend:</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <span class="badge badge-xs bg-success me-2">Within SLA</span>
                                            <small>Closed within 2 hours</small>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge badge-xs bg-danger me-2">Breached</span>
                                            <small>Closed after 2 hours</small>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge badge-xs bg-info me-2">Within SLA (pending)</span>
                                            <small>Still open, under 2 hours old</small>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge badge-xs bg-warning me-2">Overdue</span>
                                            <small>Still open, over 2 hours old</small>
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
</div>
@endsection
