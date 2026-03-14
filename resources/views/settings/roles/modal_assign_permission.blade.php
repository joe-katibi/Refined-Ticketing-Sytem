<!-- Assign Role Permission Modal -->
<div class="modal fade" id="assignRolePermission" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-simple modal-edit-Role">
        <div class="modal-content p-2 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2">
                        Assign Role - Permission <strong>{{ $row->name }}</strong>
                    </h3>
                </div>

                <form id="assignRolePermissionForm" action="{{ route('roles.permissions.store', $row->id) }}" method="POST" class="row g-3">
                    {{ csrf_field() }}
                    <input type="hidden" name="role_id" value="{{ $row->id }}">
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
    </div>
</div>

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
console.log('Role Object:', @json($row));
console.log('Role ID:', @json($row->id));
console.log('Role Permissions:', @json($row->permissions));
let rolePermissions = @json($row->permissions->pluck('name'));
console.log('Role Permission Names:', rolePermissions);

$(document).ready(function() {
    // Initialize Select2
    $(".select2").select2();

    // Initialize Select2 for sub-module dropdown
    $("#sub_module_permissions").select2({
        dropdownParent: $('#assignRolePermission')
    });

    // Handle modal show to reinitialize Select2 and log data
    $('#assignRolePermission').on('shown.bs.modal', function() {
        console.log('Modal Shown - Role Permissions:', rolePermissions);
        $(".select2").select2();
    });

    // Fetch sub-modules when module changes
    $('#module_permissions').on('change', function() {
        var selectedModule = $(this).val();
        console.log('Selected Module:', selectedModule);

        if (selectedModule) {
            $.ajax({
                url: '/settings/permissions/sub-modules/' + selectedModule,
                type: 'GET',
                success: function(data) {
                    console.log('Sub-modules received:', data);
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
                    
                    // Trigger Select2 to update
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

    // Fetch permissions when sub-module changes
    $("#sub_module_permissions").on('change', function() {
        const module = $("#module_permissions").val();
        const subModule = $(this).val();
        console.log('Selected Sub-Module:', subModule);
        
        if (module && subModule) {
            fetchPermissions(module, subModule);
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

