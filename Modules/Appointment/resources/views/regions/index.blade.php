@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Regions')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
<div class="row justify-content-center">
  <div class="col-md-20">
      <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Regions</h3>
              @can('view-appointment-regions-create')
              <a href="{{ route('appointment.regions.create') }}" class="btn btn-primary btn-xs">
                <i class="bx bx-plus"></i> Add New
            </a>
              @endcan
          </div>
          <div class="card-body">
              @if(session('success'))
                  <div class="alert alert-success">{{ session('success') }}</div>
              @endif

              <table id="regions-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>OLTs</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Edited By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($regions as $region)
                        <tr>
                            <td>{{ $region->id }}</td>
                            <td>{{ $region->name }}</td>
                            <td>
                                @forelse($region->olts as $olt)
                                    <span class="badge bg-label-info">{{ $olt->name }}</span>
                                @empty
                                    <span class="text-muted">No OLTs assigned</span>
                                @endforelse
                            </td>
                            <td>
                              @if($region->status == 'Active')
                              <span class="badge bg-label-success">Active</span>
                              @else
                              <span class="badge bg-label-danger">Inactive</span>
                              @endif
                            </td>
                            <td>{{ $region->creator ? $region->creator->name : 'N/A' }}</td>
                            <td>{{ $region->editor ? $region->editor->name : 'N/A' }}</td>
                            <td>{{ $region->created_at }}</td>
                            <td>
                                @can('view-appointment-regions-edit')
                                <a href="{{ route('appointment.regions.edit', $region->id) }}"
                                   class="btn btn-primary btn-xs" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                    <i class="bx bx-edit"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No regions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

          </div>
      </div>
  </div>
</div>
</div>

@endsection

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
$('#regions-table').DataTable({
  responsive: true
});
});
</script>
@endsection
