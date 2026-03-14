@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'User Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="ti ti-users me-2"></i>User Management Dashboard
                    </h4>
                    <small class="text-muted">Comprehensive overview of system users</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Total Users</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2">{{ number_format($totalUsers) }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge badge-xs bg-label-primary rounded p-2">
                                <i class="ti ti-users ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Active Users</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2 text-success">{{ number_format($activeUsers) }}</h4>
                                <small class="text-success">{{ $statusDistribution[0]['percentage'] }}%</small>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge badge-xs bg-label-success rounded p-2">
                                <i class="ti ti-user-check ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">Inactive Users</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2 text-danger">{{ number_format($inactiveUsers) }}</h4>
                                <small class="text-danger">{{ $statusDistribution[1]['percentage'] }}%</small>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge badge-xs bg-label-danger rounded p-2">
                                <i class="ti ti-user-x ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text">New Users (30 days)</p>
                            <div class="d-flex align-items-end mb-2">
                                <h4 class="card-title mb-0 me-2 text-info">{{ number_format($recentUsers) }}</h4>
                            </div>
                        </div>
                        <div class="card-icon">
                            <span class="badge badge-xs bg-label-info rounded p-2">
                                <i class="ti ti-user-plus ti-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Status Distribution Chart -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Status Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="statusChart"></div>
                </div>
            </div>
        </div>

        <!-- Top Departments Chart -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Users by Department</h5>
                </div>
                <div class="card-body">
                    <div id="departmentChart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables Row -->
    <div class="row">
        <!-- Users per Department Table -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Users per Department</h5>
                    <span class="badge badge-xs bg-primary">{{ $usersPerDepartment->count() }} Departments</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th class="text-end">Users</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usersPerDepartment as $dept)
                                <tr>
                                    <td>{{ $dept->department_name }}</td>
                                    <td class="text-end">
                                        <span class="badge badge-xs bg-label-primary">{{ $dept->user_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ $totalUsers > 0 ? round(($dept->user_count / $totalUsers) * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users per Role Table -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Users per Role</h5>
                    <span class="badge badge-xs bg-success">{{ $usersPerRole->count() }} Roles</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th class="text-end">Users</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usersPerRole as $role)
                                <tr>
                                    <td>{{ $role->role_name }}</td>
                                    <td class="text-end">
                                        <span class="badge badge-xs bg-label-success">{{ $role->user_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{ $totalUsers > 0 ? round(($role->user_count / $totalUsers) * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sub Department and Team Tables Row -->
    <div class="row">
        <!-- Users per Sub Department Table -->
        @if($usersPerSubDepartment->count() > 0)
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Users per Sub Department</h5>
                    <span class="badge badge-xs bg-warning">{{ $usersPerSubDepartment->count() }} Sub Departments</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sub Department</th>
                                    <th class="text-end">Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usersPerSubDepartment->take(10) as $subDept)
                                <tr>
                                    <td>{{ $subDept->sub_department_name }}</td>
                                    <td class="text-end">
                                        <span class="badge badge-xs bg-label-warning">{{ $subDept->user_count }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Users per Team Type Table -->
        @if($usersPerTeamType->count() > 0)
        <div class="col-lg-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Users per Team Type</h5>
                    <span class="badge badge-xs bg-info">{{ $usersPerTeamType->count() }} Team Types</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Team Type</th>
                                    <th class="text-end">Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usersPerTeamType->take(10) as $teamType)
                                <tr>
                                    <td>{{ $teamType->type_name }}</td>
                                    <td class="text-end">
                                        <span class="badge badge-xs bg-label-info">{{ $teamType->user_count }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Summary Statistics Row -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-label-primary rounded">
                                        <i class="ti ti-building"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Top Department</h6>
                                    <small class="text-muted">
                                        {{ $topDepartment ? $topDepartment->department_name . ' (' . $topDepartment->user_count . ')' : 'N/A' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-label-success rounded">
                                        <i class="ti ti-shield-check"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Top Role</h6>
                                    <small class="text-muted">
                                        {{ $topRole ? $topRole->role_name . ' (' . $topRole->user_count . ')' : 'N/A' }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-label-warning rounded">
                                        <i class="ti ti-users-group"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">With Supervisors</h6>
                                    <small class="text-muted">{{ $usersWithSupervisors }} users</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12 mb-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="avatar me-3">
                                    <div class="avatar-initial bg-label-info rounded">
                                        <i class="ti ti-calendar-stats"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Recent Additions</h6>
                                    <small class="text-muted">{{ $recentUsers }} in last 30 days</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Pie Chart
    const statusChartEl = document.querySelector('#statusChart');
    if (statusChartEl) {
        const statusChart = new ApexCharts(statusChartEl, {
            chart: {
                type: 'donut',
                height: 300
            },
            series: [{{ $activeUsers }}, {{ $inactiveUsers }}],
            labels: ['Active', 'Inactive'],
            colors: ['#28c76f', '#ea5455'],
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Users',
                                formatter: function() {
                                    return '{{ $totalUsers }}';
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                formatter: function(val, opts) {
                    return opts.w.config.series[opts.seriesIndex];
                }
            }
        });
        statusChart.render();
    }

    // Department Bar Chart
    const departmentChartEl = document.querySelector('#departmentChart');
    if (departmentChartEl) {
        const departmentChart = new ApexCharts(departmentChartEl, {
            chart: {
                type: 'bar',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            series: [{
                name: 'Users',
                data: [
                    @foreach($usersPerDepartment->take(5) as $dept)
                        {{ $dept->user_count }},
                    @endforeach
                ]
            }],
            xaxis: {
                categories: [
                    @foreach($usersPerDepartment->take(5) as $dept)
                        '{{ Str::limit($dept->department_name, 15) }}',
                    @endforeach
                ]
            },
            colors: ['#7367f0'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                    columnWidth: '60%'
                }
            },
            dataLabels: {
                enabled: true
            },
            grid: {
                show: true,
                strokeDashArray: 3
            }
        });
        departmentChart.render();
    }
});
</script>
@endsection
@endsection

