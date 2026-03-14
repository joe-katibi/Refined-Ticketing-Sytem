@extends('layouts.app')

@section('title', 'Report Downloads')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Report Downloads</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="bx bx-download me-2"></i>Report Downloads
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-list-ul me-2"></i>My Download History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="reportDownloadsTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Report Name</th>
                                    <th>Date Range</th>
                                    <th>Requested At</th>
                                    <th>Completed At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">
                    <i class="bx bx-error me-2"></i>Error Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <strong>Error Message:</strong>
                    <div id="errorMessage" class="mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bx bx-warning me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this download record? This action cannot be undone.</p>
                <p class="text-muted"><small>Note: The associated file will also be deleted from the server.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bx bx-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/vendor/dataTables.bootstrap5.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/vendor/responsive.bootstrap5.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('assets/js/vendor/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/vendor/dataTables.bootstrap5.js') }}"></script>
<script src="{{ asset('assets/js/vendor/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/js/vendor/responsive.bootstrap5.min.js') }}"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#reportDownloadsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("report-downloads.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'report_type', name: 'report_type' },
            { data: 'report_name', name: 'report_name' },
            { data: 'date_range', name: 'date_range', orderable: false },
            { data: 'requested_at_formatted', name: 'requested_at' },
            { data: 'completed_at_formatted', name: 'completed_at' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']], // Order by requested_at descending
        responsive: true,
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: '<div class="text-center"><i class="bx bx-download bx-lg text-muted mb-3"></i><br>No download records found</div>'
        }
    });

    // Auto-refresh table every 30 seconds to show status updates
    setInterval(function() {
        table.ajax.reload(null, false);
    }, 30000);

    // Variables for delete functionality
    let deleteId = null;

    // Show error details
    window.showError = function(id) {
        $.get('{{ route("report-downloads.error", ":id") }}'.replace(':id', id))
            .done(function(response) {
                $('#errorMessage').text(response.error_message);
                $('#errorModal').modal('show');
            })
            .fail(function() {
                showToast('Error', 'Failed to load error details', 'error');
            });
    };

    // Delete download record
    window.deleteDownload = function(id) {
        deleteId = id;
        $('#deleteModal').modal('show');
    };

    // Confirm delete
    $('#confirmDelete').click(function() {
        if (deleteId) {
            $.ajax({
                url: '{{ route("report-downloads.destroy", ":id") }}'.replace(':id', deleteId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    table.ajax.reload();
                    showToast('Success', response.message, 'success');
                },
                error: function() {
                    showToast('Error', 'Failed to delete download record', 'error');
                }
            });
        }
        deleteId = null;
    });

    // Toast notification function
    function showToast(title, message, type = 'info') {
        const toastClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
        const toast = `
            <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        const toastContainer = $('#toast-container');
        if (toastContainer.length === 0) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        $('#toast-container').append(toast);
        $('.toast').last().toast('show');
        
        // Remove toast after it's hidden
        $('.toast').last().on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>
@endpush
