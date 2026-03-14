@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit Affected Area')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    {{-- Include toast notification component --}}
    <x-toast-notification />
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Edit Affected Area
                    </h4>
                    <p class="text-muted mb-0">Update the affected area details</p>
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

                    <form action="{{ route('outages.affected-areas.update', $affectedArea->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="area_name" class="form-label">
                                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                    Area Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('area_name') is-invalid @enderror" 
                                       id="area_name" 
                                       name="area_name" 
                                       value="{{ old('area_name', $affectedArea->area_name) }}" 
                                       placeholder="Enter the affected area name"
                                       required>
                                @error('area_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="area_description" class="form-label">
                                    <i class="fas fa-align-left text-info me-1"></i>
                                    Description
                                </label>
                                <textarea class="form-control @error('area_description') is-invalid @enderror" 
                                          id="area_description" 
                                          name="area_description" 
                                          rows="4" 
                                          placeholder="Enter a detailed description of this affected area">{{ old('area_description', $affectedArea->area_description) }}</textarea>
                                @error('area_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Provide additional details about this area and what it covers.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="area_status" class="form-label">
                                    <i class="fas fa-toggle-on text-success me-1"></i>
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('area_status') is-invalid @enderror" 
                                        id="area_status" 
                                        name="area_status" 
                                        required>
                                    <option value="">Select Status</option>
                                    <option value="Active" {{ old('area_status', $affectedArea->area_status) == 'Active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="Inactive" {{ old('area_status', $affectedArea->area_status) == 'Inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                                @error('area_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Active areas will be available for selection during outage creation.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    Record Information
                                </label>
                                <div class="card bg-light">
                                    <div class="card-body p-2">
                                        <small class="text-muted">
                                            <strong>Created:</strong> {{ $affectedArea->created_at->format('M d, Y H:i') }}<br>
                                            @if($affectedArea->creator)
                                                <strong>By:</strong> {{ $affectedArea->creator->name }}<br>
                                            @endif
                                            @if($affectedArea->updated_at != $affectedArea->created_at)
                                                <strong>Last Updated:</strong> {{ $affectedArea->updated_at->format('M d, Y H:i') }}<br>
                                                @if($affectedArea->editor)
                                                    <strong>By:</strong> {{ $affectedArea->editor->name }}
                                                @endif
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('outages.affected-areas.index') }}" class="btn btn-outline-secondary btn-xs">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        Back to List
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-warning me-2">
                                            <i class="fas fa-undo me-1"></i>
                                            Reset Changes
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <i class="fas fa-save me-1"></i>
                                            Update Affected Area
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
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');
    });
    
    // Character counter for description
    $('#area_description').on('input', function() {
        var maxLength = 1000;
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        if (!$('.char-counter').length) {
            $(this).after('<div class="form-text char-counter"></div>');
        }
        
        $('.char-counter').text(remaining + ' characters remaining').toggleClass('text-danger', remaining < 50);
    });
    
    // Highlight changes
    $('input, textarea, select').on('change', function() {
        $(this).addClass('border-warning');
    });
});
</script>
@endsection

