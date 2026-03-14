@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Sub Category Report - Escalations')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="fas fa-tags text-warning me-2"></i>
                            Sub Category Analysis Report
                        </h4>
                        <p class="text-muted mb-0">Escalated items breakdown by categories and sub categories</p>
                    </div>
                    <div>
                        <a href="{{ route('escalations.reports.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Reports
                        </a>
                        <a href="{{ route('escalations.reports.export-excel', ['report_type' => 'sub_category', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-success btn-xs">
                            <i class="fas fa-file-excel me-1"></i>
                            Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form method="GET" action="{{ route('escalations.reports.sub-category') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                            </div>
                            <div class="col-md-4">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
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

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Category Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="small-box bg-info text-white p-3 rounded">
                                                <h3>{{ $groupedMetrics->count() }}</h3>
                                                <p class="mb-0">Categories</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-primary text-white p-3 rounded">
                                                <h3>{{ $groupedMetrics->flatten()->count() }}</h3>
                                                <p class="mb-0">Sub Categories</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-warning text-dark p-3 rounded">
                                                <h3>{{ $groupedMetrics->flatten()->sum('total_escalations') }}</h3>
                                                <p class="mb-0">Total Escalations</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small-box bg-success text-white p-3 rounded">
                                                <h3>{{ $groupedMetrics->flatten()->sum('closed_escalations') }}</h3>
                                                <p class="mb-0">Closed Escalations</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    @forelse($groupedMetrics as $categoryName => $subCategories)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-folder me-2"></i>
                                        {{ $categoryName }}
                                        <span class="badge badge-xs bg-light text-dark ms-2">{{ $subCategories->count() }} sub categories</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Sub Category</th>
                                                    <th class="text-center">Total</th>
                                                    <th class="text-center">Closed</th>
                                                    <th class="text-center">Open</th>
                                                    <th class="text-center">Within SLA</th>
                                                    <th class="text-center">SLA Compliance</th>
                                                    <th class="text-center">Avg Resolution (hrs)</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($subCategories as $metric)
                                                @php
                                                    $statusClass = $metric->sla_compliance_percentage >= 80 ? 'success' : ($metric->sla_compliance_percentage >= 60 ? 'warning' : 'danger');
                                                    $closureRate = $metric->total_escalations > 0 ? round(($metric->closed_escalations / $metric->total_escalations) * 100, 1) : 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-tag text-primary me-2"></i>
                                                            <strong>{{ $metric->sub_category_name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-info">{{ $metric->total_escalations }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-success">{{ $metric->closed_escalations }}</span>
                                                        <small class="text-muted d-block">({{ $closureRate }}%)</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-warning">{{ $metric->open_escalations }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-primary">{{ $metric->closed_within_sla }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-{{ $statusClass }}">{{ $metric->sla_compliance_percentage }}%</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-xs bg-secondary">{{ $metric->avg_resolution_time ?? 'N/A' }}</span>
                                                    </td>
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
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th>Category Total</th>
                                                    <th class="text-center">
                                                        <span class="badge badge-xs bg-info">{{ $subCategories->sum('total_escalations') }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <span class="badge badge-xs bg-success">{{ $subCategories->sum('closed_escalations') }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <span class="badge badge-xs bg-warning">{{ $subCategories->sum('open_escalations') }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <span class="badge badge-xs bg-primary">{{ $subCategories->sum('closed_within_sla') }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        @php
                                                            $categoryTotalClosed = $subCategories->sum('closed_escalations');
                                                            $categoryTotalWithinSla = $subCategories->sum('closed_within_sla');
                                                            $categoryCompliance = $categoryTotalClosed > 0 ? round(($categoryTotalWithinSla / $categoryTotalClosed) * 100, 1) : 0;
                                                            $categoryStatusClass = $categoryCompliance >= 80 ? 'success' : ($categoryCompliance >= 60 ? 'warning' : 'danger');
                                                        @endphp
                                                        <span class="badge badge-xs bg-{{ $categoryStatusClass }}">{{ $categoryCompliance }}%</span>
                                                    </th>
                                                    <th class="text-center">
                                                        @php
                                                            $categoryAvgResolution = $subCategories->where('avg_resolution_time', '!=', null)->avg('avg_resolution_time');
                                                        @endphp
                                                        <span class="badge badge-xs bg-secondary">{{ $categoryAvgResolution ? round($categoryAvgResolution, 2) : 'N/A' }}</span>
                                                    </th>
                                                    <th class="text-center">
                                                        @if($categoryCompliance >= 80)
                                                            <i class="fas fa-thumbs-up text-success"></i>
                                                        @elseif($categoryCompliance >= 60)
                                                            <i class="fas fa-thumbs-down text-warning"></i>
                                                        @else
                                                            <i class="fas fa-exclamation-circle text-danger"></i>
                                                        @endif
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-info-circle text-muted fa-3x mb-3"></i>
                                    <h5 class="text-muted">No Data Available</h5>
                                    <p class="text-muted">No escalation data found for the selected date range.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforelse

                    <!-- Performance Legend -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Performance Legend:</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <span class="badge badge-xs bg-success me-2">Excellent</span>
                                            <small>≥ 80% SLA Compliance</small>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="badge badge-xs bg-warning me-2">Needs Attention</span>
                                            <small>60-79% SLA Compliance</small>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="badge badge-xs bg-danger me-2">Critical</span>
                                            <small>< 60% SLA Compliance</small>
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

{{-- Using native HTML5 date inputs instead of Flatpickr for cleaner UI --}}

