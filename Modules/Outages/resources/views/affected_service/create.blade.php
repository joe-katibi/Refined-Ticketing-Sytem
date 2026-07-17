@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Create Affected Service')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-plus text-primary me-2"></i>
                        Create New Affected Service
                    </h4>
                    <p class="text-muted mb-0">Add a new service that can be affected during outages</p>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('outages.affected-services.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="service_name" class="form-label">
                                    <i class="fas fa-cogs text-primary me-1"></i>
                                    Service Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('service_name') is-invalid @enderror" 
                                       id="service_name" 
                                       name="service_name" 
                                       value="{{ old('service_name') }}" 
                                       placeholder="Enter the affected service name"
                                       required>
                                @error('service_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="service_description" class="form-label">
                                    <i class="fas fa-align-left text-info me-1"></i>
                                    Description
                                </label>
                                <textarea class="form-control @error('service_description') is-invalid @enderror" 
                                          id="service_description" 
                                          name="service_description" 
                                          rows="4" 
                                          placeholder="Enter a detailed description of this affected service">{{ old('service_description') }}</textarea>
                                @error('service_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Provide additional details about this service and what it covers.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="service_status" class="form-label">
                                    <i class="fas fa-toggle-on text-success me-1"></i>
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('service_status') is-invalid @enderror" 
                                        id="service_status" 
                                        name="service_status" 
                                        required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('service_status') == 'Active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="Inactive" {{ old('service_status') == 'Inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                                @error('service_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Active services will be available for selection during outage creation.</div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('outages.affected-services.index') }}" class="btn btn-outline-secondary btn-xs">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        Back to List
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-warning me-2">
                                            <i class="fas fa-undo me-1"></i>
                                            Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <i class="fas fa-save me-1"></i>
                                            Create Affected Service
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Form validation feedback
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Creating...');
    });
    
    // Character counter for description
    $('#service_description').on('input', function() {
        var maxLength = 1000;
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        if (!$('.char-counter').length) {
            $(this).after('<div class="form-text char-counter"></div>');
        }
        
        $('.char-counter').text(remaining + ' characters remaining').toggleClass('text-danger', remaining < 50);
    });
});
</script>
@endsection

