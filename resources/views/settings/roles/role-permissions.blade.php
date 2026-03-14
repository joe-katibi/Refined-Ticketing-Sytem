@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'role-permission')
@section('content_header')

@stop

@section('content')
<div class="container-fluid">
  <div class="row mb-2">
      <div class="col-sm-6">
          <h4 class="pull-left">Assign Roles - Permissions </h4>
      </div>
      <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right justify-content-md-end mt-n6">
              <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/settings/roles') }}">Roles</a></li>
              <li class="breadcrumb-item active"> Assign Roles - Permissions </li>
          </ol>
      </div>
  </div>
</div>
<!-- /.container-fluid -->
<div class="card">
  <div class="card-header with-border">
    Assign Role - Permission <strong>{{ $role->description }}</strong>
  </div>
  <div class="card-body">
    @if (Session::has('message'))
    <div class="alert alert-{{ Session::has('message_type')? Session::get('message_type'): 'success' }}">
        {{ Session::get('message') }}
    </div>
    @endif
    <form id="assignRolePermissionForm" action="{{ route('roles.permissions.store', $role->id ) }}" method="POST" class="row g-3">
      {{ csrf_field() }}
      <input type="hidden" name="role_id" value="{{ $role->id  }}">
      <input type="hidden" name="edited_by" value="{{ Auth::user()->id }}">

      <div class="row">
          <div class="col-6 col-md-6">
              <label class="form-label" for="module_permissions">Module</label>
              <select id="module_permissions" name="module" class="select2 form-control">
                  <option value="" disabled selected>---Select Module---</option>
                  @foreach ($permission_modules as $module)
                  <option value="{{ $module }}">{{ $module }}</option>
                  @endforeach
              </select>
          </div>

          <!-- Sub-Module Dropdown -->
          <div class="col-6 col-md-6">
              <label class="form-label" for="sub_module_permissions">Sub-Module</label>
              <select id="sub_module_permissions" name="sub_module" class="select2 form-control">
                  <option value="" disabled selected>---Select Sub-Module---</option>
              </select>
          </div>
      </div>

      <!-- Permissions Section -->
      <div class="row mt-3">
          <div id="role_permissions" class="row col-md-12">
              <!-- Dynamic permissions will be inserted here -->
          </div>
      </div>

      <!-- Buttons -->
      <div class="col-12 text-center mt-4">
          <button type="reset" class="btn btn-label-secondary btn-xs" data-bs-dismiss="modal" aria-label="Close">
              Cancel
          </button>
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
      </div>
  </form>
  </div>
</div>

@endsection

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<style>
.select2-container--open {
    z-index: 9999999;
}
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
// Debug: Log the role data
console.log('Role Object:', @json($role));
console.log('Role ID:', @json($role->id));
console.log('Role Permissions:', @json($role_permissions));
let rolePermissions = @json($role_permissions->pluck('name'));
console.log('Role Permission Names:', rolePermissions);

$(document).ready(function() {
    // Initialize Select2
    $(".select2").select2();

    // Handle module selection
    $('#module_permissions').on('change', function() {
        var selectedModule = $(this).val();
        console.log('Selected Module:', selectedModule);

        if (selectedModule) {
            $.ajax({
                url: '/settings/permissions/sub-modules/' + selectedModule,
                type: 'GET',
                success: function(data) {
                    console.log('Sub-modules received:', data);

                    // Populate sub-module dropdown
                    var subModuleDropdown = $('#sub_module_permissions');
                    subModuleDropdown.empty();
                    subModuleDropdown.append('<option value="" disabled selected>---Select Sub-Module---</option>');

                    if (Array.isArray(data)) {
                        data.forEach(function(subModule) {
                            subModuleDropdown.append(
                                $('<option>', {
                                    value: subModule.id,
                                    text: subModule.sub_module
                                })
                            );
                        });
                    }

                    // Reinitialize Select2 for the updated dropdown
                    subModuleDropdown.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching sub-modules:", error);
                    console.error("Status:", status);
                    console.error("Response:", xhr.responseText);
                }
            });
        }
    });
});

// Fetch permissions
function fetchPermissions(module, subModule) {
    console.log('Fetching permissions for:', { module, subModule });
    $.ajax({
        url: '/settings/permissions/filter',
        type: 'GET',
        data: {
            module: module,
            sub_module: subModule
        },
        success: function(data) {
            console.log('Permissions received:', data);
            updatePermissions(data);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching permissions:", error);
            console.error("Status:", status);
            console.error("Response:", xhr.responseText);
        }
    });
}

// Update Permissions Checkboxes
function updatePermissions(permissions) {
    console.log('Updating permissions UI with:', permissions);
    const container = $("#role_permissions");
    container.empty();

    if (permissions && permissions.length) {
        permissions.forEach(permission => {
            const isChecked = rolePermissions.includes(permission.name) ? 'checked' : '';
            container.append(`
                <div class="form-group col-md-6 row">
                    <input type="checkbox" name="permissions[]" class="col-md-1 permission" ${isChecked} value="${permission.name}">
                    <dl class="col-md-10">
                        <dt>${permission.description}</dt>
                        <dd>${permission.name}</dd>
                    </dl>
                </div>
            `);
        });
    }
}
</script>
@endsection

