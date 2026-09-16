@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Bulk Upload Installation Data')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Installation Data Upload</h1>
                    <p class="text-muted mb-0">Load site-visit / installation records into the Appointment List</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('appointment.appointments.list') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to Appointment List
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    @if (session('import_errors') && count(session('import_errors')) > 0)
        <div class="alert alert-warning">
            <strong>{{ count(session('import_errors')) }} row(s) could not be processed:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload File</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('installation-upload.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="file" class="form-label">Excel or CSV File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror"
                                   id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">.xlsx, .xls or .csv — up to 10MB.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('installation-upload.template') }}" class="btn btn-outline-secondary btn-xs">
                                <i class="bx bx-download me-1"></i>Download Template
                            </a>
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-upload me-1"></i>Upload &amp; Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-info-circle me-1"></i>Column Guide</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Each row becomes one Appointment (type <strong>Installation</strong>, status
                        <strong>Scheduled-Open</strong>) in the Appointment List, ready for a
                        dispatcher to assign a technician. Rows aren't auto-assigned or pushed
                        into the live FIFO queue.
                    </p>
                    <table class="table table-sm">
                        <tbody>
                            <tr><td><code>Customer Name</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Account Number</code></td><td class="text-muted">Required — matches/creates a Customer record too</td></tr>
                            <tr><td><code>Contact Number</code></td><td class="text-muted">Primary phone</td></tr>
                            <tr><td><code>Alternative Contact Number</code></td><td class="text-muted">Secondary phone</td></tr>
                            <tr><td><code>Date Received</code></td><td class="text-muted">When the request came in</td></tr>
                            <tr><td><code>FDT</code></td><td class="text-muted">FDT code/label</td></tr>
                            <tr><td><code>OLT</code></td><td class="text-muted">OLT name — linked automatically if it matches an existing OLT</td></tr>
                            <tr><td><code>Road Name</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Dispatcher</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Team Assigned</code></td><td class="text-muted">Free text — for reference, not the actual assignment</td></tr>
                            <tr><td><code>Installation Type</code></td><td class="text-muted">Sub-type name, e.g. "New Fiber Installation" (defaults if blank/unmatched)</td></tr>
                            <tr><td><code>Category</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Dispatch Update</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Escalation Date</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Location Coords</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Escalation Notes</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Infra Feedback</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Infra Feedback Date And Time</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Design Feedback</code></td><td class="text-muted"></td></tr>
                        </tbody>
                    </table>
                    <p class="text-muted small mb-0">
                        Only <strong>Account Number</strong> is required on every row.
                        Rows with a problem are skipped and listed after upload — nothing else in the file is affected.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
