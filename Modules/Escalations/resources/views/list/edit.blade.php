@extends('layouts/layoutMaster')

@php
$configData = Helper::appClasses();
@endphp

@section('title', 'Edit Escalation Ticket')

@section('content')
<div class="container-xxxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Edit Escalation Ticket</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('list.update', $list->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- First row: Account, Category, Subcategory -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Account Number</label>
                            <input type="text" name="account_number" class="form-control" value="{{ $list->account_number }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $list->category_id == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Subcategory</label>
                            <select name="sub_category_id" id="sub_category_id" class="form-control" required>
                                <option value="">Select Subcategory</option>
                                <!-- Options populated via JS -->
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Second row: Department, Sub Department, Priority -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Department</label>
                            <select name="department_id" id="department_id" class="form-control" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ $list->department_id == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Sub Department</label>
                            <select name="sub_department_id" id="sub_department_id" class="form-control" required>
                                <option value="">Select Sub Department</option>
                                <!-- Options populated via JS -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label>Priority</label>
                            <select name="priority" class="form-control" required>
                                <option value="Low" {{ $list->priority == 'Low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ $list->priority == 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ $list->priority == 'High' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" required>{{ $list->description }}</textarea>
                </div>

                <!-- Status -->
                <div class="form-group mb-4">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Escalated-Open" {{ $list->status == 'Escalated-Open' ? 'selected' : '' }}>Escalated-Open</option>
                        <option value="Escalated-Closed" {{ $list->status == 'Escalated-Closed' ? 'selected' : '' }}>Escalated-Closed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success btn-xs">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection

<!-- AJAX logic for loading subcategories and sub departments -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function() {
    // Function to load subcategories based on category selection
    function loadSubcategories(categoryId, selectedSubcategoryId) {
        var subCategorySelect = $('#sub_category_id');
        subCategorySelect.empty().append('<option value="">Select Subcategory</option>');
        if (categoryId) {
            $.get('/list/subcategories/' + categoryId, function(data) {
                if (!data.length) {
                    subCategorySelect.append('<option value="">No subcategories found</option>');
                } else {
                    $.each(data, function(key, subcategory) {
                        var selected = (subcategory.id == selectedSubcategoryId) ? 'selected' : '';
                        subCategorySelect.append('<option value="'+subcategory.id+'" '+selected+'>'+subcategory.sub_category_name+'</option>');
                    });
                }
            }).fail(function() {
                console.error('Error loading subcategories');
                subCategorySelect.append('<option value="">Error loading subcategories</option>');
            });
        }
    }

    // Function to load sub departments based on department selection
    function loadSubDepartments(departmentId, selectedSubDepartmentId) {
        var subDepartmentSelect = $('#sub_department_id');
        subDepartmentSelect.empty().append('<option value="">Loading...</option>');
        
        if (departmentId) {
            $.get('/teamtypes/sub-departments', {
                department_id: departmentId
            }, function(data) {
                subDepartmentSelect.empty().append('<option value="">Select Sub Department</option>');
                if (!data.length) {
                    subDepartmentSelect.append('<option value="">No sub departments found</option>');
                } else {
                    $.each(data, function(key, subDepartment) {
                        var selected = (subDepartment.id == selectedSubDepartmentId) ? 'selected' : '';
                        subDepartmentSelect.append('<option value="'+subDepartment.id+'" '+selected+'>'+subDepartment.name+'</option>');
                    });
                }
            }).fail(function() {
                console.error('Error loading sub departments');
                subDepartmentSelect.empty().append('<option value="">Error loading sub departments</option>');
            });
        } else {
            subDepartmentSelect.empty().append('<option value="">Select Department First</option>');
        }
    }

    // Initialize subcategories on page load
    var initialCategoryId = $('select[name="category_id"]').val();
    var initialSubcategoryId = "{{ $list->sub_category_id }}";
    if (initialCategoryId) {
        loadSubcategories(initialCategoryId, initialSubcategoryId);
    }

    // Initialize sub departments on page load
    var initialDepartmentId = $('select[name="department_id"]').val();
    var initialSubDepartmentId = "{{ $list->sub_department_id }}";
    if (initialDepartmentId) {
        loadSubDepartments(initialDepartmentId, initialSubDepartmentId);
    }

    // Event handlers
    $('select[name="category_id"]').change(function() {
        loadSubcategories($(this).val(), null);
    });

    $('select[name="department_id"]').change(function() {
        loadSubDepartments($(this).val(), null);
    });
});
</script>

