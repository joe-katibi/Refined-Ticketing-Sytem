@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Bulk Upload OLT / FDT / FAT / Customer Data')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Bulk Upload</h1>
                    <p class="text-muted mb-0">Load OLTs, slots, PON ports, FDTs, FATs and customers from a spreadsheet</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-user me-1"></i>Customers
                    </a>
                    <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary btn-xs">
                        <i class="bx bx-arrow-back me-1"></i>Back to OLTs
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
                    <form action="{{ route('olt-upload.store') }}" method="POST" enctype="multipart/form-data">
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
                            <a href="{{ route('olt-upload.template') }}" class="btn btn-outline-secondary btn-xs">
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
                        Each row describes one customer's full path through the network.
                        The same OLT / FDT / FAT can appear on many rows — matching rows are
                        merged into the same record instead of duplicated.
                    </p>
                    <table class="table table-sm">
                        <tbody>
                            <tr><td><code>OLT Name</code></td><td class="text-muted">OLT device name</td></tr>
                            <tr><td><code>Vendor</code></td><td class="text-muted">e.g. Huawei, ZTE</td></tr>
                            <tr><td><code>IP address</code></td><td class="text-muted">Used to match an existing OLT</td></tr>
                            <tr><td><code>Location</code></td><td class="text-muted">OLT site / cabinet</td></tr>
                            <tr><td><code>Slots</code></td><td class="text-muted">Exact slot number for this row</td></tr>
                            <tr><td><code>PON Ports</code></td><td class="text-muted">Exact PON port number for this row</td></tr>
                            <tr><td><code>FDT</code></td><td class="text-muted">FDT number on that port</td></tr>
                            <tr><td><code>FAT</code></td><td class="text-muted">FAT number on that FDT</td></tr>
                            <tr><td><code>Customer Account Number</code></td><td class="text-muted">Matches an existing customer, or creates one</td></tr>
                            <tr><td><code>Customer Name</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>Onu Type</code></td><td class="text-muted"></td></tr>
                            <tr><td><code>ONU physical Address</code></td><td class="text-muted">Where the ONU is installed</td></tr>
                            <tr><td><code>Bandwidth Profile Name</code></td><td class="text-muted">Package / plan</td></tr>
                        </tbody>
                    </table>
                    <p class="text-muted small mb-0">
                        Only <strong>OLT Name</strong> and <strong>IP address</strong> are required on every row.
                        Rows with a problem are skipped and listed after upload — nothing else in the file is affected.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
