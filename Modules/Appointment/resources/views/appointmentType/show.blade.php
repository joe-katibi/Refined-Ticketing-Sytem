@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'View Appointment Type')

@push('styles')
<style>
    .info-box {
        border-left: 4px solid #4e73df;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
    }
    .info-value {
        color: #212529;
    }
</style>
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Appointment Type Details</h3>
                    <div>
                        <a href="{{ route('appointment.types.edit', $appointmentType->id) }}"
                           class="btn btn-primary btn-xs">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('appointment.types.index') }}"
                           class="btn btn-secondary btn-xs">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="mb-2">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value">{{ $appointmentType->type_name }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Status:</span>
                                    <span class="info-value">{!! $appointmentType->status_badge !!}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Description:</span>
                                    <span class="info-value">{{ $appointmentType->type_description ?: 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="mb-2">
                                    <span class="info-label">Created By:</span>
                                    <span class="info-value">{{ $appointmentType->createdBy->name ?? 'System' }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Last Updated:</span>
                                    <span class="info-value">{{ $appointmentType->updated_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="info-label">Created At:</span>
                                    <span class="info-value">{{ $appointmentType->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Sub-Appointment Types</h3>
                            <a href="{{ route('appointment.types.sub-types.create', $appointmentType->id) }}"
                               class="btn btn-primary btn-xs">
                                <i class="fas fa-plus"></i> Add Sub-Type
                            </a>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if($appointmentType->subTypes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="sub-types-table" >
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($appointmentType->subTypes as $subType)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $subType->sub_type_name }}</td>
                                                    <td>{{ $subType->sub_type_description ?: 'N/A' }}</td>
                                                    <td>{!! $subType->status_badge !!}</td>
                                                    <td>{{ $subType->createdBy->name ?? 'System' }}</td>
                                                    <td>{{ $subType->created_at->format('M d, Y h:i A') }}</td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('appointment.types.sub-types.edit', [$appointmentType->id, $subType->id]) }}"
                                                               class="btn btn-primary btn-xs"
                                                               data-bs-toggle="tooltip"
                                                               title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            {{-- <form action="{{ route('appointment.types.sub-types.destroy', [$appointmentType->id, $subType->id]) }}"
                                                                  method="POST"
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Are you sure you want to delete this sub-type? This action cannot be undone.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="btn btn-danger btn-xs"
                                                                        data-bs-toggle="tooltip"
                                                                        title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form> --}}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No sub-appointment types found. Click the "Add Sub-Type" button to create one.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
$('#sub-types-table').DataTable({
  responsive: true
});
});
</script>
@endsection

