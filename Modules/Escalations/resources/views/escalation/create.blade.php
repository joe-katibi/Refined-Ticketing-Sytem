@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Create New Escalation')


@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create New Escalation</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('escalations.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="name">Account Number <span class="text-danger">*</span></label>
                            <input type="text" name="acccount_number" id="acccount_number" class="form-control @error('acccount_number') is-invalid @enderror" value="{{ old('acccount_number') }}" required>
                            @error('acccount_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                          <label for="name">Category <span class="text-danger">*</span></label>
                          <input type="text" name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" value="{{ old('category_id') }}" required>
                          @error('category_id')
                              <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                      </div>
                      <div class="form-group">
                        <label for="name">Sub Category <span class="text-danger">*</span></label>
                        <input type="text" name="sub_category_id" id="sub_category_id" class="form-control @error('sub_category_id') is-invalid @enderror" value="{{ old('sub_category_id') }}" required>
                        @error('sub_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Source <span class="text-danger">*</span></label>
                        <input type="text" name="source_id" id="source_id" class="form-control @error('source_id') is-invalid @enderror" value="{{ old('source_id') }}" required>
                        @error('source_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="priority">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                <option value="">Select Priority</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="">Select Status</option>
                                <option value="Escalated-Open" {{ old('status') == 'Escalated-Open' ? 'selected' : '' }}>Escalated Open</option>
                                <option value="Escalated-Closed" {{ old('status') == 'Escalated-Closed' ? 'selected' : '' }}>Escalated Closed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="fas fa-save"></i> Save Escalation
                            </button>
                            <a href="{{ route('escalations.index') }}" class="btn btn-secondary btn-xs">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

