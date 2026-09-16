@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Savannah Ticketing Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
    --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
}

body {
    background: linear-gradient(120deg, #f6f9fc 0%, #e9ecef 100%);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.dashboard-container {
    padding: 2rem 1rem;
    max-width: 1400px;
    margin: 0 auto;
}

.dashboard-header {
    background: var(--primary-gradient);
    color: white;
    padding: 3rem 2rem;
    margin-bottom: 3rem;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 100%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1.5" fill="white" opacity="0.08"/><circle cx="50" cy="10" r="0.8" fill="white" opacity="0.12"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    animation: float 20s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.project-title {
    font-size: 3.5rem;
    font-weight: 800;
    background: linear-gradient(45deg, #ffffff, #e3f2fd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 2;
}

.dashboard-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 1rem;
    position: relative;
    z-index: 2;
}

.welcome-text {
    font-size: 0.95rem;
    opacity: 0.8;
    position: relative;
    z-index: 2;
}

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.stat-card.escalations {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card.appointments {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-card.outages {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.stat-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stat-label {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.stat-sublabel {
    font-size: 0.9rem;
    opacity: 0.8;
}

.module-section {
    margin-bottom: 4rem;
}

.module-header {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.module-icon {
    font-size: 2rem;
    margin-right: 1rem;
    color: #667eea;
}

.module-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
}

.module-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.module-stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
}

.module-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.module-stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.module-stat-label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #64748b;
}

.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }
.text-info { color: #3b82f6 !important; }
.text-secondary { color: #6b7280 !important; }
.text-primary { color: #667eea !important; }

.quick-actions {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    margin-top: 3rem;
}

.quick-actions-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1.5rem;
    text-align: center;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.action-btn {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.action-btn:hover {
    transform: translateY(-2px);
    text-decoration: none;
}

.action-btn.primary {
    background: var(--primary-gradient);
    color: white;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.action-btn.success {
    background: var(--success-gradient);
    color: white;
    box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
}

.action-btn.warning {
    background: var(--warning-gradient);
    color: white;
    box-shadow: 0 5px 15px rgba(250, 112, 154, 0.3);
}

.module-section {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-top: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.module-header {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid rgba(102, 126, 234, 0.1);
}

.module-icon {
    font-size: 2rem;
    color: #667eea;
    margin-right: 1rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 0.75rem;
    border-radius: 12px;
}

.module-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
}

.module-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.module-stat-card {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.module-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.module-stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.module-stat-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 1rem 0.5rem;
    }

    .project-title {
        font-size: 2.5rem;
    }

    .stats-overview {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .module-stats {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
}

/* This dashboard predates the site-wide dark theme and hardcodes its own
   light colors (white cards, #2d3748 headings, etc.) instead of the
   template's Bootstrap CSS variables, so the global dark-style stylesheet
   swap (core-dark.css/theme-default-dark.css) never reaches it. Re-theme
   the same selectors here, scoped to .dark-style, rather than rewriting the
   section to use theme variables throughout. */
.dark-style body {
    background: linear-gradient(120deg, #1a1c2e 0%, #14152a 100%);
}

.dark-style .stat-card {
    background: #2b2c40;
    border-color: rgba(255, 255, 255, 0.08);
}

.dark-style .module-section,
.dark-style .quick-actions {
    background: #2b2c40;
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.dark-style .module-header {
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

.dark-style .module-title,
.dark-style .quick-actions-title {
    color: #cbcbe2;
}

.dark-style .module-icon {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
}

.dark-style .module-stat-card {
    background: linear-gradient(135deg, #323249, #383856);
    border-color: rgba(255, 255, 255, 0.06);
}

.dark-style .module-stat-label {
    color: #a3a4cc;
}
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header text-center">
        <h1 class="project-title">Savannah Ticketing</h1>
        <p class="dashboard-subtitle">Comprehensive Service Management Dashboard</p>
        <div class="welcome-text">
            <i class="fas fa-user-circle me-2"></i>
            Welcome back, {{ Auth::user()->name }} | {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="stats-overview">
        <div class="stat-card escalations text-center">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-number">{{ $escalationsTotal ?? 0 }}</div>
            <div class="stat-label">Total Escalations</div>
            <div class="stat-sublabel">{{ $escalationsToday ?? 0 }} created today</div>
        </div>
        
        <div class="stat-card appointments text-center">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-number">{{ $appointmentsTotal ?? 0 }}</div>
            <div class="stat-label">Total Appointments</div>
            <div class="stat-sublabel">{{ $appointmentsToday ?? 0 }} scheduled today</div>
        </div>
        
        <div class="stat-card outages text-center">
            <div class="stat-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="stat-number">{{ $outagesTotal ?? 0 }}</div>
            <div class="stat-label">Total Outages</div>
            <div class="stat-sublabel">{{ $outagesActive ?? 0 }} currently active</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="quick-actions-title">
            <i class="fas fa-rocket me-2"></i>Quick Actions
        </div>
        <div class="action-buttons">
            <a href="{{ route('list.create') }}" class="action-btn primary">
                <i class="fas fa-plus"></i>
                Create Escalation
            </a>
            <a href="/appointments/create" class="action-btn success">
                <i class="fas fa-calendar-plus"></i>
                Schedule Appointment
            </a>
            <a href="/outages/create" class="action-btn warning">
                <i class="fas fa-exclamation-circle"></i>
                Report Outage
            </a>
        </div>
    </div>

    <!-- Escalations Module Section -->
    <div class="module-section">
        <div class="module-header">
            <i class="module-icon fas fa-exclamation-triangle"></i>
            <h2 class="module-title">Escalations Module</h2>
        </div>
        <div class="module-stats">
            <div class="module-stat-card">
                <div class="module-stat-number text-success">{{ $escalationsOpen ?? 0 }}</div>
                <div class="module-stat-label">Open Cases</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-secondary">{{ $escalationsClosed ?? 0 }}</div>
                <div class="module-stat-label">Closed Cases</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-warning">{{ $escalationsHigh ?? 0 }}</div>
                <div class="module-stat-label">High Priority</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-info">{{ $escalationsThisWeek ?? 0 }}</div>
                <div class="module-stat-label">Created This Week</div>
            </div>
        </div>
    </div>

    <!-- Appointments Module Section -->
    <div class="module-section">
        <div class="module-header">
            <i class="module-icon fas fa-calendar-check"></i>
            <h2 class="module-title">Site Visits (Appointments) Module</h2>
        </div>
        <div class="module-stats">
            <div class="module-stat-card">
                <div class="module-stat-number text-info">{{ $appointmentsOpen ?? 0 }}</div>
                <div class="module-stat-label">Open</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-success">{{ $appointmentsCompleted ?? 0 }}</div>
                <div class="module-stat-label">Completed</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-warning">{{ $appointmentsThisWeek ?? 0 }}</div>
                <div class="module-stat-label">Scheduled This Week</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-danger">{{ $appointmentsCancelled ?? 0 }}</div>
                <div class="module-stat-label">Cancelled</div>
            </div>
        </div>
    </div>

    <!-- Outages Module Section -->
    <div class="module-section">
        <div class="module-header">
            <i class="module-icon fas fa-bolt"></i>
            <h2 class="module-title">Outages Module</h2>
        </div>
        <div class="module-stats">
            <div class="module-stat-card">
                <div class="module-stat-number text-warning">{{ $outagesActive ?? 0 }}</div>
                <div class="module-stat-label">Active</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-success">{{ $outagesResolved ?? 0 }}</div>
                <div class="module-stat-label">Resolved</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-danger">{{ $outagesCritical ?? 0 }}</div>
                <div class="module-stat-label">Critical</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-danger">{{ $outagesEmergency ?? 0 }}</div>
                <div class="module-stat-label">Emergency</div>
            </div>
        </div>
    </div>

    <!-- FIFO Queue Module Section -->
    <div class="module-section">
        <div class="module-header">
            <i class="module-icon fas fa-list-ol"></i>
            <h2 class="module-title">FIFO Dispatch Queues</h2>
        </div>
        <div class="module-stats">
            <div class="module-stat-card">
                <div class="module-stat-number {{ ($fifoWaiting['escalation'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">{{ $fifoWaiting['escalation'] ?? 0 }}</div>
                <div class="module-stat-label">Escalations Waiting</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number {{ ($fifoWaiting['appointment'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">{{ $fifoWaiting['appointment'] ?? 0 }}</div>
                <div class="module-stat-label">Appointments Waiting</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number {{ ($fifoWaiting['outage'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">{{ $fifoWaiting['outage'] ?? 0 }}</div>
                <div class="module-stat-label">Outages Waiting</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-primary">{{ $fifoOldestWaitMinutes !== null ? $fifoOldestWaitMinutes . 'm' : '—' }}</div>
                <div class="module-stat-label">Oldest Wait</div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('fifo.index', 'escalation') }}" class="action-btn primary d-inline-flex" style="max-width: 260px;">
                <i class="fas fa-fast-forward"></i> Open Dispatch Console
            </a>
        </div>
    </div>

    <!-- Users Module Section -->
    <div class="module-section">
        <div class="module-header">
            <i class="module-icon fas fa-users"></i>
            <h2 class="module-title">Users</h2>
        </div>
        <div class="module-stats">
            <div class="module-stat-card">
                <div class="module-stat-number text-primary">{{ $usersTotal ?? 0 }}</div>
                <div class="module-stat-label">Total Users</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-success">{{ $usersActive ?? 0 }}</div>
                <div class="module-stat-label">Active</div>
            </div>
            <div class="module-stat-card">
                <div class="module-stat-number text-secondary">{{ $usersInactive ?? 0 }}</div>
                <div class="module-stat-label">Inactive</div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="module-section">
        <div class="module-header">
            <i class="module-icon fas fa-clock"></i>
            <h2 class="module-title">Recent Activity (last 7 days)</h2>
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

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

