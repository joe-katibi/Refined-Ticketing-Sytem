@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Create Subcategory')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create Subcategory for {{ $category->category_name }}</div>
                <div class="card-body">
                    <form action="{{ route('escalation-category.subcategories.store', $category) }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="sub_category_name">Subcategory Name <span class="text-danger">*</span></label>
                            <input type="text" name="sub_category_name" id="sub_category_name" class="form-control @error('sub_category_name') is-invalid @enderror" value="{{ old('sub_category_name') }}" required>
                            @error('sub_category_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-xs">Create</button>
                        <a href="{{ route('escalation-category.subcategories.index', $category) }}" class="btn btn-secondary btn-xs">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

