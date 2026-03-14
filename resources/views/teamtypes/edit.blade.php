@php
$configData = Helper::appClasses();
$pageConfigs = ['myLayout' => 'vertical'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit Team Type')

@push('styles')
<style>
    .form-label {
        font-weight: 500;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Edit Team Type</h4>
                <a href="{{ route('teamtypes.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('teamtypes.update', $teamtype->id) }}" class="form form-vertical">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="type_name" class="form-label">
                                    Type Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="type_name"
                                    class="form-control @error('type_name') is-invalid @enderror"
                                    name="type_name"
                                    value="{{ old('type_name', $teamtype->type_name) }}"
                                    placeholder="Enter team type name"
                                    required>
                                @error('type_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    Description
                                </label>
                                <textarea id="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    name="description"
                                    rows="3"
                                    placeholder="Enter description (optional)">{{ old('description', $teamtype->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">
                                    Department
                                </label>
                                <select id="department_id" 
                                    class="form-select @error('department_id') is-invalid @enderror" 
                                    name="department_id">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                            {{ old('department_id', $teamtype->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sub_department_id" class="form-label">
                                    Sub Department
                                </label>
                                <select id="sub_department_id" 
                                    class="form-select @error('sub_department_id') is-invalid @enderror" 
                                    name="sub_department_id">
                                    <option value="">Select Sub Department</option>
                                    @if($teamtype->sub_department_id && $teamtype->subDepartment)
                                        <option value="{{ $teamtype->sub_department_id }}" selected>
                                            {{ $teamtype->subDepartment->sub_department_name }}
                                        </option>
                                    @endif
                                </select>
                                @error('sub_department_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label d-block">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="status"
                                           id="status_active"
                                           value="Active"
                                           {{ old('status', $teamtype->status) === 'Active' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_active">
                                        Active
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="status"
                                           id="status_inactive"
                                           value="Inactive"
                                           {{ old('status', $teamtype->status) === 'Inactive' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_inactive">
                                        Inactive
                                    </label>
                                </div>
                                @error('status')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-1"></i> Update Team Type
                            </button>
                            <a href="{{ route('teamtypes.index') }}" class="btn btn-outline-secondary btn-xs">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize form validation
        $('form').on('submit', function(e) {
            const typeName = $('#type_name').val().trim();
            if (!typeName) {
                e.preventDefault();
                $('#type_name').addClass('is-invalid');
                $('<div class="invalid-feedback">The type name field is required.</div>').insertAfter('#type_name');
            }
        });

        // Clear validation on input
        $('#type_name').on('input', function() {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        });

        // Handle department change to load sub departments
        $('#department_id').on('change', function() {
            const departmentId = $(this).val();
            const subDepartmentSelect = $('#sub_department_id');
            const currentSubDepartmentId = '{{ old("sub_department_id", $teamtype->sub_department_id) }}';
            
            // Clear sub department options
            subDepartmentSelect.html('<option value="">Select Sub Department</option>');
            
            if (departmentId) {
                // Show loading state
                subDepartmentSelect.prop('disabled', true);
                subDepartmentSelect.html('<option value="">Loading...</option>');
                
                // Fetch sub departments
                $.ajax({
                    url: '{{ route("teamtypes.sub-departments") }}',
                    method: 'GET',
                    data: { department_id: departmentId },
                    success: function(data) {
                        subDepartmentSelect.html('<option value="">Select Sub Department</option>');
                        $.each(data, function(index, subDept) {
                            const selected = subDept.id == currentSubDepartmentId ? 'selected' : '';
                            subDepartmentSelect.append(
                                $('<option></option>').val(subDept.id).text(subDept.name).prop('selected', selected)
                            );
                        });
                        subDepartmentSelect.prop('disabled', false);
                    },
                    error: function() {
                        subDepartmentSelect.html('<option value="">Error loading sub departments</option>');
                        subDepartmentSelect.prop('disabled', false);
                    }
                });
            }
        });

        // Initialize sub departments on page load if department is selected
        if ($('#department_id').val()) {
            $('#department_id').trigger('change');
        }
    });
</script>
@endpush

