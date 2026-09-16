@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
@endsection

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">Dashboard</h4>
            <span class="text-muted">Welcome back, {{ Auth::user()->name }} — {{ now()->format('l, F j, Y') }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('list.create') }}" class="btn btn-primary btn-sm">New Escalation</a>
            <a href="{{ route('appointment.appointments.create') }}" class="btn btn-outline-primary btn-sm">New Appointment</a>
            <a href="{{ route('outages.create') }}" class="btn btn-outline-primary btn-sm">New Outage</a>
        </div>
    </div>

    <!-- Headline totals -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="badge rounded bg-label-danger p-2 me-3"><i class="ti ti-alert-triangle ti-md"></i></span>
                    <div>
                        <h4 class="mb-0">{{ $escalationsTotal ?? 0 }}</h4>
                        <small class="text-muted">Escalations</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="badge rounded bg-label-info p-2 me-3"><i class="ti ti-calendar-check ti-md"></i></span>
                    <div>
                        <h4 class="mb-0">{{ $appointmentsTotal ?? 0 }}</h4>
                        <small class="text-muted">Appointments</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="badge rounded bg-label-warning p-2 me-3"><i class="ti ti-bolt ti-md"></i></span>
                    <div>
                        <h4 class="mb-0">{{ $outagesTotal ?? 0 }}</h4>
                        <small class="text-muted">Outages</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="badge rounded bg-label-secondary p-2 me-3"><i class="ti ti-users ti-md"></i></span>
                    <div>
                        <h4 class="mb-0">{{ $usersTotal ?? 0 }}</h4>
                        <small class="text-muted">Users ({{ $usersActive ?? 0 }} active)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI breakdown by module -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Escalations</h6>
                    <a href="{{ route('escalations.index') }}" class="small">View all</a>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Open</span>
                        <span class="fw-semibold">{{ $escalationsOpen ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">High priority</span>
                        <span class="fw-semibold {{ ($escalationsHigh ?? 0) > 0 ? 'text-danger' : '' }}">{{ $escalationsHigh ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Closed</span>
                        <span class="fw-semibold">{{ $escalationsClosed ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Created today</span>
                        <span class="fw-semibold">{{ $escalationsToday ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Created this week</span>
                        <span class="fw-semibold">{{ $escalationsThisWeek ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Appointments</h6>
                    <a href="{{ route('appointment.appointments.index') }}" class="small">View all</a>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Open</span>
                        <span class="fw-semibold">{{ $appointmentsOpen ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Completed</span>
                        <span class="fw-semibold text-success">{{ $appointmentsCompleted ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Cancelled</span>
                        <span class="fw-semibold {{ ($appointmentsCancelled ?? 0) > 0 ? 'text-danger' : '' }}">{{ $appointmentsCancelled ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Scheduled today</span>
                        <span class="fw-semibold">{{ $appointmentsToday ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Scheduled this week</span>
                        <span class="fw-semibold">{{ $appointmentsThisWeek ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Outages</h6>
                    <a href="{{ route('outages.index') }}" class="small">View all</a>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Active</span>
                        <span class="fw-semibold {{ ($outagesActive ?? 0) > 0 ? 'text-warning' : '' }}">{{ $outagesActive ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Resolved</span>
                        <span class="fw-semibold text-success">{{ $outagesResolved ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Critical</span>
                        <span class="fw-semibold {{ ($outagesCritical ?? 0) > 0 ? 'text-danger' : '' }}">{{ $outagesCritical ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">High priority</span>
                        <span class="fw-semibold">{{ $outagesHigh ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Emergency</span>
                        <span class="fw-semibold {{ ($outagesEmergency ?? 0) > 0 ? 'text-danger' : '' }}">{{ $outagesEmergency ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unassigned queue + users -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Unassigned Queue (FIFO)</h6>
                    <a href="{{ route('fifo.index', 'escalation') }}" class="small">Open dispatch console</a>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <h5 class="mb-0 {{ ($fifoWaiting['escalation'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">{{ $fifoWaiting['escalation'] ?? 0 }}</h5>
                            <small class="text-muted">Escalations waiting</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <h5 class="mb-0 {{ ($fifoWaiting['appointment'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">{{ $fifoWaiting['appointment'] ?? 0 }}</h5>
                            <small class="text-muted">Appointments waiting</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <h5 class="mb-0 {{ ($fifoWaiting['outage'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">{{ $fifoWaiting['outage'] ?? 0 }}</h5>
                            <small class="text-muted">Outages waiting</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <h5 class="mb-0">{{ $fifoOldestWaitMinutes !== null ? $fifoOldestWaitMinutes . 'm' : '—' }}</h5>
                            <small class="text-muted">Oldest wait</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Users</h6>
                    <a href="{{ route('settings.users') }}" class="small">Manage</a>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Total</span>
                        <span class="fw-semibold">{{ $usersTotal ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Active</span>
                        <span class="fw-semibold text-success">{{ $usersActive ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-top">
                        <span class="text-muted">Inactive</span>
                        <span class="fw-semibold">{{ $usersInactive ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Volume trend -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Volume — Last 6 Months</h6>
                </div>
                <div class="card-body">
                    <div id="volumeTrendChart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Recent Activity (last 7 days)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Ticket</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEscalations->concat($recentAppointments)->concat($recentOutages)->sortByDesc('created_at')->take(10) as $item)
                                <tr>
                                    <td>
                                        @if($item instanceof \Modules\Escalations\App\Models\Escalation) Escalation
                                        @elseif($item instanceof \Modules\Appointment\Models\Appointment) Appointment
                                        @else Outage
                                        @endif
                                    </td>
                                    <td>{{ $item->ticket_id ?? $item->appointment_ticket_id ?? $item->ticket_number ?? '#' . $item->id }}</td>
                                    <td><span class="badge bg-label-secondary">{{ $item->status }}</span></td>
                                    <td>{{ $item->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No activity in the last 7 days.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('page-script')
<script>
(function () {
    const monthlyData = @json($monthlyData ?? []);

    // ApexCharts doesn't follow the page's own dark-style class, so its
    // default (light-mode) axis/legend text renders low-contrast against
    // the dark theme's navy background — tell it explicitly.
    const isDark = document.documentElement.classList.contains('dark-style');

    const chart = new ApexCharts(document.querySelector('#volumeTrendChart'), {
        chart: { type: 'area', height: 300, toolbar: { show: false } },
        theme: { mode: isDark ? 'dark' : 'light' },
        series: [
            { name: 'Escalations', data: monthlyData.map(m => m.escalations) },
            { name: 'Appointments', data: monthlyData.map(m => m.appointments) },
            { name: 'Outages', data: monthlyData.map(m => m.outages) },
        ],
        xaxis: { categories: monthlyData.map(m => m.month) },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
        legend: { position: 'top', horizontalAlign: 'right' },
        grid: { borderColor: isDark ? '#444564' : '#e9ecef' },
    });
    chart.render();
})();
</script>
@endsection
