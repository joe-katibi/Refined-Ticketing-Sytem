@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Create New Escalation')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Create Escalation Ticket</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('list.store') }}" method="POST">
                @csrf

                <!-- First row: Account Number, Category, Subcategory -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="account_number">Account Number</label>
                            <input type="text" name="account_number" id="account_number" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="sub_category_id">Subcategory</label>
                            <select name="sub_category_id" id="sub_category_id" class="form-control" required>
                                <option value="">Select Subcategory</option>
                                <!-- Options will be populated by JS -->
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Second row: Department, Sub Department, Priority, Region -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="department_id">Department</label>
                            <select name="department_id" id="department_id" class="form-control" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="sub_department_id">Sub Department</label>
                            <select name="sub_department_id" id="sub_department_id" class="form-control" required>
                                <option value="">Select Department First</option>
                                <!-- Options will be populated by JS based on selected department -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="priority">Priority</label>
                            <select name="priority" id="priority" class="form-control" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="region_id">Region</label>
                            <select name="region_id" id="region_id" class="form-control">
                                <option value="">Select Region (optional)</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Used for region-aware FIFO assignment.</small>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group mb-3">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" required></textarea>
                </div>

                <!-- Status -->
                <div class="form-group mb-4">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Escalated-Open">Escalated-Open</option>
                        <option value="Escalated-Closed">Escalated-Closed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success btn-xs">Create</button>
            </form>
        </div>
    </div>
</div>
@endsection


<!-- jQuery for AJAX subcategory and sub department loading -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function() {
    // Handle category change for subcategories
    $('#category_id').change(function() {
        var categoryId = $(this).val();
        var subCategorySelect = $('#sub_category_id');
        subCategorySelect.empty();
        subCategorySelect.append('<option value="">Select Subcategory</option>');
        if(categoryId) {
            $.get('/list/subcategories/' + categoryId, function(data) {
                if (data.length === 0) {
                    subCategorySelect.append('<option value="">No subcategories found</option>');
                }
                $.each(data, function(key, subcategory) {
                    subCategorySelect.append('<option value="'+subcategory.id+'">'+subcategory.sub_category_name+'</option>');
                });
            });
        }
    });

    // Handle department change for sub departments
    $('#department_id').change(function() {
        var departmentId = $(this).val();
        var subDepartmentSelect = $('#sub_department_id');
        
        // Clear existing options
        subDepartmentSelect.empty();
        subDepartmentSelect.append('<option value="">Select Sub Department</option>');
        
        if(departmentId) {
            console.log('Loading sub departments for department ID:', departmentId);
            
            // Use the existing API endpoint for getting sub departments
            $.get('/teamtypes/sub-departments?department_id=' + departmentId, function(data) {
                console.log('Sub departments response:', data);
                
                if (data.length === 0) {
                    subDepartmentSelect.append('<option value="">No sub departments found</option>');
                } else {
                    $.each(data, function(key, subDepartment) {
                        subDepartmentSelect.append('<option value="'+subDepartment.id+'">'+subDepartment.name+'</option>');
                    });
                    console.log('Loaded', data.length, 'sub departments');
                }
            }).fail(function() {
                console.error('Error loading sub departments');
                subDepartmentSelect.append('<option value="">Error loading sub departments</option>');
            });
        } else {
            subDepartmentSelect.append('<option value="">Select Department First</option>');
        }
    });
});
</script>

