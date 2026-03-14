@extends('layouts/layoutMaster')

@section('title', 'Sources')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-20">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Escalation Tickets</h4>
                @can('view-create-escalation')
                <a href="{{ route('list.create') }}" class="btn btn-primary btn-xs">Create Ticket</a>
                @endcan
            </div>
            <div class="card-body">
          @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
          @endif


      <table id="listescalation-table" class="table table-bordered table-hover">
        <thead>
          <tr>
              <th>ID</th>
              <th>Ticket ID</th>
              <th>Account Number</th>
              <th>Category</th>
              <th>Subcategory</th>
              <th>Sub Department</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($lists as $list)
              <tr>
                  <td>{{ $list->id }}</td>
                  <td>{{ $list->ticket_id }}</td>
                  <td>{{ $list->account_number }}</td>
                  <td>{{ $list->category->category_name ?? '-' }}</td>
                  <td>{{ $list->subcategory->sub_category_name ?? '-' }}</td>
                  <td>{{ $list->sub_department->sub_department_name ?? '-' }}</td>
                  <td>{{ $list->priority }}</td>
                  <td>
                      @php
                          $badgeClass = $list->status == 'Escalated-Closed' ? 'bg-label-success' : 'bg-label-warning';
                      @endphp
                      <span class="badge {{ $badgeClass }}">{{ $list->status }}</span>
                  </td>
                  <td>
                      <div class="action-buttons">
                          @can('view-view-escalation')
                          <a href="{{ route('list.show', $list->id) }}" class="btn btn-icon btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                              <i class="fas fa-eye"></i>
                          </a>
                          @endcan
                          @can('view-edit-escalation')
                          <a href="{{ route('list.edit', $list->id) }}" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                              <i class="fas fa-edit"></i>
                          </a>
                          @endcan
                          @can('view-history-escalation')
                          <a href="{{ route('escalations.history', ['escalation' => $list->id]) }}" class="btn btn-icon btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View History">
                              <i class="fas fa-history"></i>
                          </a>
                          @endcan
                      </div>
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
    $('#listescalation-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'desc']], // Sort by hidden ID column
        columnDefs: [
            {
                target: 0, // Hide the first column (ID)
                visible: false,
                searchable: false
            }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
});
</script>
@endsection

