@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit Final Reason')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Final Reason</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('appointment.final-reasons.update', $final_reason->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="final_reason_name">Reason Name <span class="text-danger">*</span></label>
                            <input type="text" name="final_reason_name" id="final_reason_name"
                                   class="form-control @error('final_reason_name') is-invalid @enderror"
                                   value="{{ old('final_reason_name', $final_reason->final_reason_name) }}" required>
                            @error('final_reason_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="final_reason_description">Description</label>
                            <textarea name="final_reason_description" id="final_reason_description"
                                     class="form-control @error('final_reason_description') is-invalid @enderror"
                                     rows="3">{{ old('final_reason_description', $final_reason->final_reason_description) }}</textarea>
                            @error('final_reason_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="final_reason_status">Status <span class="text-danger">*</span></label>
                            <select name="final_reason_status" id="final_reason_status"
                                    class="form-control @error('final_reason_status') is-invalid @enderror" required>
                                <option value="active" {{ old('final_reason_status', $final_reason->final_reason_status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('final_reason_status', $final_reason->final_reason_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('final_reason_status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="{{ route('appointment.final-reasons.index') }}" class="btn btn-secondary btn-xs">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

