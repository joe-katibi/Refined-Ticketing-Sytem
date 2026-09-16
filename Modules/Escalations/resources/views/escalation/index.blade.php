@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'List Escalations')


@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
  @if (session('success'))
    <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          {{ session('success') }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  @endif

  @if (session('error'))
    <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          {{ session('error') }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  @endif
</div>

                        @if($subDepartments->count() > 0)
                        <!-- Nav-pills for SubDepartments -->
                        <ul class="nav nav-pills widget-nav-tabs pb-2 gap-2 mx-1 d-flex flex-nowrap" role="tablist">
                            @foreach($subDepartments as $subDepartment)
                                <li class="nav-item">
                                    <a href="#tab-subdepartment-{{ $subDepartment->id }}" class="nav-link btn bg-warning btn-xs{{ $loop->first ? ' active' : '' }}" data-bs-toggle="tab">
                                        {{ $subDepartment->sub_department_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> No Escalations Found</h5>
                            <p>There are currently no escalations in the system. <a href="{{ route('list.create') }}" class="btn btn-primary btn-sm">Create New Escalation</a></p>
                        </div>
                        @endif
                        <div class="tab-content mt-3">
                            @foreach($subDepartments as $subDepartment)
                                <div class="tab-pane fade{{ $loop->first ? ' show active' : '' }}" id="tab-subdepartment-{{ $subDepartment->id }}">

                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">{{ $subDepartment->sub_department_name }} Escalations</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="responsive-table-wrapper">
                                                <table class="table table-bordered table-hover responsive-table mobile-card-table" id="datatable-{{ $subDepartment->id }}">
                                                    <thead>
                                                        <tr>
                                                            <th>Ticket ID</th>
                                                            <th class="d-none-mobile">Account Number</th>
                                                            <th>Category</th>
                                                            <th class="d-none-tablet">Subcategory</th>
                                                            <th class="d-none-laptop">Description</th>
                                                            <th class="d-none-mobile">Priority</th>
                                                            <th>Status</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $openEscalations = $listsBySubDepartment[$subDepartment->id]->where('status', 'Escalated-Open');
                                                        @endphp
                                                        @forelse($openEscalations as $list)
                                                            <tr>
                                                                <td>{{ $list->ticket_id }}</td>
                                                                <td>{{ $list->account_number }}</td>
                                                                <td>{{ $list->category->category_name ?? '-' }}</td>
                                                                <td>{{ $list->subcategory->sub_category_name ?? '-' }}</td>
                                                                <td>{{ Str::limit($list->description, 50) }}</td>
                                                                <td>
                                                                    <span class="badge badge-xs bg-label-{{ $list->priority === 'High' ? 'danger' : ($list->priority === 'Medium' ? 'warning' : 'primary') }}">
                                                                        {{ $list->priority }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-xs bg-label-warning">Escalated-Open</span>
                                                                </td>
                                                                <td>
                                                                    <div class="action-buttons">
                                                                        @php $nativeId = $nativeEscalationIdMap[$list->id] ?? null; @endphp
                                                                        @if($nativeId)
                                                                            @can('view-view-escalation')
                                                                            <a href="{{ route('escalations.show', $nativeId) }}" class="btn btn-icon btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                                                <i class="fas fa-eye"></i>
                                                                            </a>
                                                                            @endcan
                                                                            @can('view-edit-escalation')
                                                                            <a href="{{ route('escalations.edit', $nativeId) }}" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                                                <i class="fas fa-edit"></i>
                                                                            </a>
                                                                            @endcan
                                                                        @else
                                                                            <span class="badge badge-xs bg-label-secondary" data-bs-toggle="tooltip" title="No linked escalation record">Unlinked</span>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="8" class="text-center">No open escalations found for this department.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- Mobile Card View -->
                                            <div class="mobile-card-view d-none">
                                                @forelse($openEscalations as $list)
                                                    <div class="mobile-card">
                                                        <div class="mobile-card-header">
                                                            {{ $list->ticket_id }}
                                                        </div>
                                                        
                                                        <div class="mobile-card-row">
                                                            <div class="mobile-card-label">Account Number</div>
                                                            <div class="mobile-card-value">{{ $list->account_number }}</div>
                                                        </div>
                                                        
                                                        <div class="mobile-card-row">
                                                            <div class="mobile-card-label">Category</div>
                                                            <div class="mobile-card-value">{{ $list->category->category_name ?? '-' }}</div>
                                                        </div>
                                                        
                                                        <div class="mobile-card-row">
                                                            <div class="mobile-card-label">Status</div>
                                                            <div class="mobile-card-value">
                                                                <span class="badge bg-warning">Escalated-Open</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mobile-card-row">
                                                            <div class="mobile-card-label">Priority</div>
                                                            <div class="mobile-card-value">
                                                                <span class="badge bg-{{ $list->priority === 'High' ? 'danger' : ($list->priority === 'Medium' ? 'warning' : 'primary') }}">
                                                                    {{ $list->priority }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mobile-card-row">
                                                            <div class="mobile-card-label">Actions</div>
                                                            <div class="mobile-card-value">
                                                                <div class="btn-group-responsive">
                                                                    @php $nativeId = $nativeEscalationIdMap[$list->id] ?? null; @endphp
                                                                    @if($nativeId)
                                                                        @can('view-view-escalation')
                                                                        <a href="{{ route('escalations.show', $nativeId) }}" class="btn btn-outline-info btn-sm">
                                                                            <i class="bx bx-show me-1"></i> View
                                                                        </a>
                                                                        @endcan
                                                                        @can('view-edit-escalation')
                                                                        <a href="{{ route('escalations.edit', $nativeId) }}" class="btn btn-outline-primary btn-sm">
                                                                            <i class="bx bx-edit me-1"></i> Edit
                                                                        </a>
                                                                        @endcan
                                                                    @else
                                                                        <span class="badge bg-secondary">Unlinked</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="table-empty-state">
                                                        <i class="bx bx-info-circle"></i>
                                                        <h5>No escalations found</h5>
                                                        <p>No open escalations for this department.</p>
                                                    </div>
                                                @endforelse
                                            </div>
                                      
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
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
  document.addEventListener('DOMContentLoaded', function() {
    // Wait for DOM to be fully loaded before initializing DataTables
    try {
      // Initialize DataTables for each tab
      @foreach($subDepartments as $subDepartment)
        (function() {
          try {
            var tableId = 'datatable-{{ $subDepartment->id }}';
            var table = document.getElementById(tableId);

            if (!table) {
              console.error('Table with ID ' + tableId + ' not found');
              return;
            }

            // Count columns in thead - use the first row as the source of truth
            var headerRow = table.querySelector('thead tr');
            if (!headerRow) {
              console.error('Table ' + tableId + ' has no header row');
              return;
            }
            var headerCells = headerRow.querySelectorAll('th').length;
            console.log('Table ' + tableId + ' has ' + headerCells + ' header cells');

            // Check if table is empty or has a single row with colspan
            var tbody = table.querySelector('tbody');
            if (!tbody) {
              console.error('Table ' + tableId + ' has no tbody');
              return;
            }

            var rows = tbody.querySelectorAll('tr');
            if (rows.length === 0) {
              console.log('Table ' + tableId + ' has no data rows, skipping DataTables');
              return;
            }

            // Check for empty state with colspan
            if (rows.length === 1) {
              var firstRow = rows[0];
              var cells = firstRow.querySelectorAll('td');
              if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                console.log('Table ' + tableId + ' has empty state row with colspan, skipping DataTables');
                return;
              }
            }

            // Verify data rows have consistent column count
            var hasInconsistentColumns = false;
            var rowsWithWrongColumnCount = [];
            
            for (var i = 0; i < rows.length; i++) {
              var cellCount = rows[i].querySelectorAll('td').length;
              
              // Skip rows with colspan as they're likely special rows
              var hasColspan = false;
              var cells = rows[i].querySelectorAll('td');
              for (var j = 0; j < cells.length; j++) {
                if (cells[j].hasAttribute('colspan')) {
                  hasColspan = true;
                  break;
                }
              }
              
              if (!hasColspan && cellCount !== headerCells) {
                hasInconsistentColumns = true;
                rowsWithWrongColumnCount.push(i);
                console.warn('Row ' + i + ' has ' + cellCount + ' cells, expected ' + headerCells);
              }
            }

            if (hasInconsistentColumns) {
              console.warn('Table ' + tableId + ' has inconsistent column counts in rows: ' + rowsWithWrongColumnCount.join(', '));
              // We'll continue anyway and let DataTables handle it, but log the warning
            }

            // Create column definitions based on actual column count
            var columnDefs = [];
            for (var i = 0; i < headerCells - 1; i++) {
              columnDefs.push({ orderable: true, targets: i });
            }
            // Make the last column (Actions) not sortable
            columnDefs.push({ orderable: false, targets: headerCells - 1 });

            // Initialize DataTables with proper configuration and error handling
            try {
              // Force column count consistency before initializing DataTables
              var rows = tbody.querySelectorAll('tr');
              for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].querySelectorAll('td');
                // Skip rows with colspan
                var hasColspan = false;
                for (var j = 0; j < cells.length; j++) {
                  if (cells[j].hasAttribute('colspan')) {
                    hasColspan = true;
                    break;
                  }
                }
                
                if (!hasColspan && cells.length !== headerCells) {
                  console.warn('Fixing row ' + i + ' with ' + cells.length + ' cells (expected ' + headerCells + ')');
                  // Add missing cells or remove excess cells
                  if (cells.length < headerCells) {
                    for (var j = cells.length; j < headerCells; j++) {
                      var cell = document.createElement('td');
                      cell.textContent = '-';
                      rows[i].appendChild(cell);
                    }
                  } else if (cells.length > headerCells) {
                    for (var j = cells.length - 1; j >= headerCells; j--) {
                      rows[i].removeChild(cells[j]);
                    }
                  }
                }
              }
              
              var dataTable = $('#' + tableId).DataTable({
                destroy: true, // In case it was initialized before
                responsive: true,
                dom: 'lfrtip',
                order: [[0, 'desc']], // Sort by Ticket ID in descending order
                columnDefs: columnDefs,
                language: {
                  search: "_INPUT_",
                  searchPlaceholder: "Search records",
                  paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                  }
                },
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                // Add error handling for DataTables warnings and errors
                initComplete: function() {
                  console.log('DataTables successfully initialized for ' + tableId);
                }
              });
            } catch (dtErr) {
              console.error('DataTables initialization error for ' + tableId + ':', dtErr);
            }
          } catch (err) {
            console.error('Error processing table {{ $subDepartment->id }}:', err);
          }
        })();
      @endforeach

      // Initialize tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      })
    } catch (err) {
      console.error('Error in DOMContentLoaded event:', err);
    }
  });
</script>
@endsection

