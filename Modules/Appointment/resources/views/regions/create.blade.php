@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Create Region')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Region</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('appointment.regions.store') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="name">Region Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="olts">OLTs</label>
                            <select class="form-select @error('olts') is-invalid @enderror"
                                    id="olts" name="olts[]" multiple size="8">
                                @foreach($olts as $olt)
                                    <option value="{{ $olt->id }}"
                                            {{ in_array($olt->id, old('olts', [])) ? 'selected' : '' }}>
                                        {{ $olt->name }}{{ $olt->region_id ? ' (currently in another region)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple OLTs. An OLT belongs to only one region — selecting one here moves it out of any other region.</div>
                            @error('olts')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status"
                                    class="form-control @error('status') is-invalid @enderror" required>
                                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-xs">
                                <i class="bx bx-save"></i> Save
                            </button>
                            <a href="{{ route('appointment.regions.index') }}" class="btn btn-secondary btn-xs">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-style')
<style>
.form-select[multiple] { min-height: 160px; }
</style>
@endsection
