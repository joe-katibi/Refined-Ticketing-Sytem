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
                            <label class="d-flex justify-content-between align-items-center">
                                <span>Account Number</span>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Account number entry mode">
                                    <button type="button" class="btn btn-outline-primary active" id="mode-search-btn">Search Customer</button>
                                    <button type="button" class="btn btn-outline-primary" id="mode-manual-btn">Manual Entry</button>
                                </div>
                            </label>

                            <!-- Search Customer mode: looks up existing customers uploaded/managed
                                 under OLT Management; selecting one fills account_number below. -->
                            <div id="account-search-mode" class="position-relative">
                                <input type="text" id="customer_search" class="form-control"
                                       placeholder="Type an account number, name, or mobile number..." autocomplete="off">
                                <div id="customer_search_results" class="list-group position-absolute w-100 shadow-sm"
                                     style="z-index: 1000; max-height: 220px; overflow-y: auto; display: none;"></div>
                                <div id="selected_customer_info" class="form-text text-success mt-1" style="display: none;"></div>
                            </div>

                            <!-- Manual Entry mode: the original free-text field, for accounts
                                 that haven't been uploaded to the Customers list yet. -->
                            <div id="account-manual-mode" style="display: none;">
                                <input type="text" name="account_number_manual" id="account_number_manual"
                                       class="form-control" placeholder="Enter account number">
                            </div>

                            <input type="hidden" name="account_number" id="account_number">
                            <div class="text-danger small mt-1" id="account_number_error" style="display: none;"></div>
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

    // ---- Account number: search an existing customer, or fall back to
    // manual entry ---------------------------------------------------------
    var accountMode = 'search';
    var searchTimer = null;
    var selectedCustomer = null; // {account_number, label}, set only by an actual click on a result

    function setAccountMode(mode) {
        accountMode = mode;
        var isSearch = mode === 'search';
        $('#account-search-mode').toggle(isSearch);
        $('#account-manual-mode').toggle(!isSearch);
        $('#mode-search-btn').toggleClass('active', isSearch);
        $('#mode-manual-btn').toggleClass('active', !isSearch);
        $('#account_number_error').hide();

        if (isSearch) {
            // Restore the last actual selection, if any — never leave a stale
            // "Selected: ..." banner showing a customer whose account number
            // isn't what's actually in the hidden field.
            if (selectedCustomer) {
                $('#account_number').val(selectedCustomer.account_number);
                $('#selected_customer_info').text('Selected: ' + selectedCustomer.label).show();
            } else {
                $('#account_number').val('');
                $('#selected_customer_info').hide();
            }
        } else {
            // Manual mode drives the hidden field directly from its own input.
            $('#selected_customer_info').hide();
            $('#account_number').val($('#account_number_manual').val());
        }
    }

    $('#mode-search-btn').on('click', function () { setAccountMode('search'); });
    $('#mode-manual-btn').on('click', function () { setAccountMode('manual'); });

    $('#account_number_manual').on('input', function () {
        $('#account_number').val($(this).val());
    });

    $('#customer_search').on('input', function () {
        var term = $(this).val().trim();
        selectedCustomer = null;
        $('#selected_customer_info').hide();
        $('#account_number').val('');

        clearTimeout(searchTimer);
        if (term.length < 2) {
            $('#customer_search_results').hide().empty();
            return;
        }

        searchTimer = setTimeout(function () {
            $.get('{{ route('customers.search') }}', { q: term }, function (results) {
                var $list = $('#customer_search_results').empty();
                if (!results.length) {
                    $list.append('<div class="list-group-item text-muted">No matching customers — try Manual Entry instead.</div>');
                } else {
                    $.each(results, function (i, customer) {
                        var $item = $('<a href="javascript:void(0)" class="list-group-item list-group-item-action"></a>')
                            .text(customer.label)
                            .data('customer', customer);
                        $list.append($item);
                    });
                }
                $list.show();
            });
        }, 300);
    });

    $(document).on('click', '#customer_search_results a', function () {
        var customer = $(this).data('customer');
        selectedCustomer = customer;
        $('#account_number').val(customer.account_number);
        $('#customer_search').val(customer.label);
        $('#customer_search_results').hide().empty();
        $('#selected_customer_info').text('Selected: ' + customer.label).show();
    });

    // Hide the results dropdown when clicking elsewhere on the page.
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#account-search-mode').length) {
            $('#customer_search_results').hide();
        }
    });

    setAccountMode('search');

    $('form').on('submit', function (e) {
        if (!$('#account_number').val()) {
            e.preventDefault();
            var message = accountMode === 'search'
                ? 'Search for and select a customer, or switch to Manual Entry.'
                : 'Enter an account number.';
            $('#account_number_error').text(message).show();
            (accountMode === 'search' ? $('#customer_search') : $('#account_number_manual')).trigger('focus');
        }
    });
});
</script>

