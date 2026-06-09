@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Partners')


@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Partners') }}</span>
                    <button type="button" class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#createPartnerModal">
                        <i class="ti ti-plus me-1"></i> {{ __('Add New Partner') }}
                    </button>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered" id="partners-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Partner Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Edited By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($partners as $index => $partner)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $partner->partner_name }}</td>
                                        <td>{{ Str::limit($partner->description, 50) }}</td>
                                        <td>
                                            @if($partner->status == 'active')
                                            <span class="badge badge-xs bg-label-success">Active</span>
                                            @else
                                            <span class="badge badge-xs bg-label-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $partner->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $partner->editor->name ?? 'N/A' }}</td>
                                        <td>{{ $partner->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-primary edit-partner"
                                                data-bs-toggle="modal" data-bs-target="#editPartnerModal"
                                                data-id="{{ $partner->id }}"
                                                data-partner_name="{{ $partner->partner_name }}"
                                                data-description="{{ $partner->description }}"
                                                data-status="{{ $partner->status }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            {{-- <form action="{{ route('partners.destroy', $partner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this partner?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No partners found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- <div class="d-flex justify-content-center mt-4">
                        {{ $partners->links() }}
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    @include('partners.modal_create_partner')
    @include('partners.modal_edit_partner')
@stop

@push('scripts')
<script>
// Initialize modals when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize create form handling
    const createForm = document.getElementById('createPartnerForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');

            // Show loading state
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            fetch('{{ route('partners.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createPartnerModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Partner created successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Close modal and refresh the partners table instead of full page reload
                        const modal = bootstrap.Modal.getInstance(document.getElementById('createPartnerModal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Clear the form
                        $('#createPartnerForm')[0].reset();
                        // Reload the partners data via AJAX (if you have a function for this)
                        if (typeof loadPartners === 'function') {
                            loadPartners();
                        } else {
                            // Fallback: just remove the modal from backdrop
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('.modal').hide();
                        }
                    });
                } else {
                    // Handle validation errors
                    let errorMessage = 'An error occurred while creating the partner.';
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).join('\n');
                    } else if (data.message) {
                        errorMessage = data.message;
                    }

                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while creating the partner.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                // Reset loading state
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            });
        });
    }

    // Handle edit modal show event
    const editModalElement = document.getElementById('editPartnerModal');
    if (editModalElement) {
        editModalElement.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget; // Button that triggered the modal

            // Extract info from data-* attributes
            const id = button.getAttribute('data-id');
            const partnerName = button.getAttribute('data-partner_name');
            const description = button.getAttribute('data-description');
            const status = button.getAttribute('data-status');

            // Update the modal's content
            const modal = this;
            modal.querySelector('#edit_id').value = id;
            modal.querySelector('#edit_partner_name').value = partnerName || '';
            modal.querySelector('#edit_description').value = description || '';
            modal.querySelector('#edit_status').value = status || 'active';

            // Update the form action
            const form = modal.querySelector('#editPartnerForm');
            if (form) {
                form.action = `/partners/${id}`;
            }
        });
    }

    // Handle edit form submission
    const editForm = document.getElementById('editPartnerForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');

            // Show loading state
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(this);
            formData.append('_method', 'PUT');

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editPartnerModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Partner updated successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Close modal and refresh the partners table instead of full page reload
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editPartnerModal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Reload the partners data via AJAX (if you have a function for this)
                        if (typeof loadPartners === 'function') {
                            loadPartners();
                        } else {
                            // Fallback: just remove the modal from backdrop
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('.modal').hide();
                        }
                    });
                } else {
                    // Handle validation errors
                    let errorMessage = 'An error occurred while updating the partner.';
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).join('\n');
                    } else if (data.message) {
                        errorMessage = data.message;
                    }

                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating the partner.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                // Reset loading state
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            });
        });
    }
});
</script>
@endpush

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endsection



@section('page-script')
<script>
$(document).ready(function() {
    $('#partners-table').DataTable({
        responsive: true,
        dom: 'lfrtip' // Default DataTables layout: length, filter, table, info, pagination
      });
});
</script>
@endsection
