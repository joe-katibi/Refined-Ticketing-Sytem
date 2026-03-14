@php
$configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Create Sub-Appointment Type')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Create New Sub-Appointment Type for: {{ $appointmentType->type_name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('appointment.types.show', $appointmentType->id) }}" class="btn btn-secondary btn-xs">
                            <i class="fas fa-arrow-left"></i> Back to Type
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('appointment.types.sub-types.store', $appointmentType->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sub_type_name">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="sub_type_name" id="sub_type_name"
                                           class="form-control @error('sub_type_name') is-invalid @enderror"
                                           value="{{ old('sub_type_name') }}"
                                           required>
                                    @error('sub_type_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sub_type_status">Status <span class="text-danger">*</span></label>
                                    <select name="sub_type_status" id="sub_type_status"
                                            class="form-control @error('sub_type_status') is-invalid @enderror"
                                            required>
                                        <option value="Active" {{ old('sub_type_status') == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ old('sub_type_status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('sub_type_status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sub_type_description">Description</label>
                            <textarea name="sub_type_description" id="sub_type_description"
                                     class="form-control @error('sub_type_description') is-invalid @enderror"
                                     rows="3">{{ old('sub_type_description') }}</textarea>
                            @error('sub_type_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <a href="{{ route('appointment.types.show', $appointmentType->id) }}" class="btn btn-secondary btn-xs">
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

