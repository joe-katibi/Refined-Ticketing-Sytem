@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Final Reasons')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
<div class="row justify-content-center">
  <div class="col-md-20">
      <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Final Reasons</h3>
              <a href="{{ route('appointment.final-reasons.create') }}" class="btn btn-primary btn-xs">
                <i class="fas fa-plus"></i> Add New
            </a>
          </div>
          <div class="card-body">
              @if(session('success'))
                  <div class="alert alert-success">{{ session('success') }}</div>
              @endif


              <table id="final-reasons-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Edited By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reasons as $reason)
                        <tr>
                            <td>{{ $reason->id }}</td>
                            <td>{{ $reason->final_reason_name }}</td>
                            <td>{{ Str::limit($reason->final_reason_description, 50) }}</td>
                            <td>
                              @if($reason->final_reason_status == 'Active')
                              <span class="badge  bg-label-success">Active</span>
                              @else
                              <span class="badge  bg-label-danger">Inactive</span>
                              @endif
                            </td>
                            <td>{{ $reason->creator ? $reason->creator->name : 'N/A' }}</td>
                            <td>{{ $reason->editor ? $reason->editor->name : 'N/A' }}</td>
                            <td>{{ $reason->created_at }}</td>
                            <td>
                                <a href="{{ route('appointment.final-reasons.edit', $reason->id) }}"
                                   class="btn btn-primary btn-xs" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No final reasons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            {{-- </table>  {{ $reasons->links() }} --}}

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
$('#final-reasons-table').DataTable({
  responsive: true
});
});
</script>
@endsection

