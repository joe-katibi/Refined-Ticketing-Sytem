@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Show Escalation')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Escalation Details</h4>
                    <a href="{{ route('escalations.history', $escalation->id) }}" class="btn btn-outline-primary btn-xs">
                        <i class="fas fa-history me-1"></i> View History
                    </a>
                </div>

                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8">{{ $escalation->name }}</dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $escalation->description }}</dd>

                        <dt class="col-sm-4">Priority</dt>
                        <dd class="col-sm-8">
                            @php
                                $priorityLabels = [
                                    'low' => 'Low',
                                    'medium' => 'Medium',
                                    'high' => 'High',
                                ];
                            @endphp
                            <span class="badge badge-{{ $escalation->priority == 'high' ? 'danger' : ($escalation->priority == 'medium' ? 'warning' : 'info') }}">
                                {{ $priorityLabels[$escalation->priority] ?? ucfirst($escalation->priority) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @php
                                $statusLabels = [
                                    'open' => 'Open',
                                    'in_progress' => 'In Progress',
                                    'resolved' => 'Resolved',
                                ];
                            @endphp
                            <span class="badge badge-{{ $escalation->status == 'resolved' ? 'success' : ($escalation->status == 'in_progress' ? 'info' : 'primary') }}">
                                {{ $statusLabels[$escalation->status] ?? ucfirst($escalation->status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Created At</dt>
                        <dd class="col-sm-8">{{ $escalation->created_at->format('M d, Y H:i') }}</dd>
                    </dl>
                    <a href="{{ route('escalations.edit', $escalation) }}" class="btn btn-warning btn-xs">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('escalations.index') }}" class="btn btn-secondary btn-xs">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

