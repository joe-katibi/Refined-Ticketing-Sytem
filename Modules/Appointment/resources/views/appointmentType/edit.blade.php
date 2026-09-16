@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Edit Appointment Type')

@push('style')
<style>
    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    .card-title {
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Appointment Type</h3>
                    <div class="card-tools">
                        <a href="{{ route('appointment.types.index') }}" class="btn btn-secondary btn-xs">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('appointment.types.update', $appointmentType->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type_name">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="type_name" id="type_name"
                                           class="form-control @error('type_name') is-invalid @enderror"
                                           value="{{ old('type_name', $appointmentType->type_name) }}"
                                           required>
                                    @error('type_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code_prefix">Ticket Prefix <span class="text-danger">*</span></label>
                                    <input type="text" name="code_prefix" id="code_prefix"
                                           class="form-control @error('code_prefix') is-invalid @enderror"
                                           value="{{ old('code_prefix', $appointmentType->code_prefix) }}"
                                           maxlength="10" placeholder="e.g. INS, WiE, SUP, SHI"
                                           style="text-transform:uppercase" required>
                                    <small class="form-text text-muted">Used to number tickets for this type, e.g. SUP-1, SUP-2.</small>
                                    @error('code_prefix')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type_status">Status <span class="text-danger">*</span></label>
                                    <select name="type_status" id="type_status"
                                            class="form-control @error('type_status') is-invalid @enderror"
                                            required>
                                        <option value="Active" {{ old('type_status', $appointmentType->type_status) == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ old('type_status', $appointmentType->type_status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('type_status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="type_description">Description</label>
                            <textarea name="type_description" id="type_description"
                                     class="form-control @error('type_description') is-invalid @enderror"
                                     rows="3">{{ old('type_description', $appointmentType->type_description) }}</textarea>
                            @error('type_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="{{ route('appointment.types.index') }}" class="btn btn-secondary btn-xs">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

