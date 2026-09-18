<?php

namespace database\seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [

            // User management - Menu Permissions
            [
                'module' => 'User Management',
                'sub_module' => 'User Management Menu',
                'description' => 'View User management Menu',
                'name' => 'view-users-management-menu',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Dashboard',
                'description' => 'View Users Dashboard',
                'name' => 'view-dashboard-user',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'User list',
                'description' => 'View Users List',
                'name' => 'view-users-list-menu',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Department',
                'description' => 'View Department',
                'name' => 'view-user-department-menu',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Roles',
                'description' => 'View Roles',
                'name' => 'view-user-roles-menu',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Permissions',
                'description' => 'View Permissions',
                'name' => 'view-user-permissions-menu',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Team Types',
                'description' => 'View Team Types',
                'name' => 'view-user-team-types-menu',
            ],
            // User management - Action Permissions
            [
                'module' => 'User Management',
                'sub_module' => 'Create User',
                'description' => 'Create A User',
                'name' => 'view-create-user',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Edit User',
                'description' => 'Edit A User',
                'name' => 'view-edit-user',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'View User',
                'description' => 'View A User',
                'name' => 'view-view-user',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'List Users',
                'description' => 'List All Users',
                'name' => 'view-list-user',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Edit User Status',
                'description' => 'Edit User Status',
                'name' => 'view-edit-user-status',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Create Department',
                'description' => 'Create A Department',
                'name' => 'view-user-create-department',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Edit Department',
                'description' => 'Edit A Department',
                'name' => 'view-user-edit-department',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'View Department',
                'description' => 'View A Department',
                'name' => 'view-user-view-Department',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Create Sub Department',
                'description' => 'Create A Sub Department',
                'name' => 'view-user-create-sub-department',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Edit Sub Department',
                'description' => 'Edit A Sub Department',
                'name' => 'view-user-edit-Sub-department',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Create Role',
                'description' => 'Create a Role',
                'name' => 'view-user-role',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Edit Role',
                'description' => 'Edit a Role',
                'name' => 'view-user-edit-role',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Assign Role Permissions',
                'description' => 'Assign a Role Permissions to user',
                'name' => 'view-user-role-permissions',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Create Team Types',
                'description' => 'Create a Team Type',
                'name' => 'view-user-create-team-types',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Edit Team Types',
                'description' => 'Edit a Team Type',
                'name' => 'view-user-Edit-team-types',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'View Team Types',
                'description' => 'View a Team Type',
                'name' => 'view-user-view-team-types',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Create Sub Team Types',
                'description' => 'Create a Sub Team Type',
                'name' => 'view-user-create-sub-team-types',
            ],
            [
                'module' => 'User Management',
                'sub_module' => 'Edit Sub Team Types',
                'description' => 'Edit a Sub Team Type',
                'name' => 'view-user-edit-sub-team-types',
            ],
            // Escalation Module
            [
                'module' => 'Escalation',
                'sub_module' => 'Escalation Menu',
                'description' => 'View Escalation Menu',
                'name' => 'view-escalation-menu',
            ],
            [
                'module' => 'Escalations',
                'sub_module' => 'Escalations Dashboard',
                'description' => 'View Escalations Dashboard',
                'name' => 'view-dashboard-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Escalate',
                'description' => 'View Escalate',
                'name' => 'view-escalate-menu',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Escalations',
                'description' => 'View Escalations',
                'name' => 'view-escalations-menu',
            ],
            [
                'module' => 'Escalations',
                'sub_module' => 'Escalations Reports',
                'description' => 'View Escalations Reports',
                'name' => 'view-reports-escalation',
            ],
            [
                'module' => 'Escalations',
                'sub_module' => 'Escalations Notifications',
                'description' => 'View Escalations Notifications',
                'name' => 'view-notifications-escalation',
            ],
            [
                'module' => 'Escalations',
                'sub_module' => 'Categories Management',
                'description' => 'Manage Escalation Categories',
                'name' => 'view-categories-escalation',
            ],
            [
                'module' => 'Escalations',
                'sub_module' => 'Sources Management',
                'description' => 'Manage Escalation Sources',
                'name' => 'view-sources-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Escalate',
                'description' => 'View Escalate',
                'name' => 'view-escalate-menu',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Escalations',
                'description' => 'View Escalations',
                'name' => 'view-escalations-menu',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Categories',
                'description' => 'View Categories',
                'name' => 'view-escalation-categories-menu',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Notification',
                'description' => 'View Notification',
                'name' => 'view-escalation-notification-menu',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Reports',
                'description' => 'View Reports',
                'name' => 'view-escalation-report-menu',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Create an Escalation',
                'description' => 'Create an Escalation',
                'name' => 'view-create-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Edit an Escalation',
                'description' => 'Edit an Escalation',
                'name' => 'view-edit-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'View an Escalation',
                'description' => 'View an Escalation',
                'name' => 'view-view-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'List Escalation',
                'description' => 'List All Escalations',
                'name' => 'view-list-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'History Escalation',
                'description' => 'History Escalation',
                'name' => 'view-history-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Handle Edit Escalation',
                'description' => 'Handle Edit Escalation',
                'name' => 'view-handle-edit-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Delete an Escalation',
                'description' => 'Delete an Escalation',
                'name' => 'view-delete-escalation',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Create Categories',
                'description' => 'Create A Category',
                'name' => 'view-escalation-create-categories',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Create Escalation Category',
                'description' => 'Create Escalation Category',
                'name' => 'view-create-escalation-category',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Edit Categories',
                'description' => 'Edit A Category',
                'name' => 'view-escalation-edit-categories',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'View Sub Categories',
                'description' => 'View Sub Categories',
                'name' => 'view-escalation-sub-categories',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Create Sub Categories',
                'description' => 'Create Sub Categories',
                'name' => 'view-escalation-create-sub-categories',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Edit Sub Categories',
                'description' => 'Edit Sub Categories',
                'name' => 'view-escalation-edit-sub-categories',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'View Sub Categories',
                'description' => 'View Sub Categories',
                'name' => 'view-escalation-view-sub-categories',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'View Escalation Notification',
                'description' => 'View Escalation Notification',
                'name' => 'view-escalation-notification-mark-read',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Download Escalation Reports',
                'description' => 'Download Escalation Reports',
                'name' => 'view-escalation-download-reports',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'Delete An Escalation',
                'description' => 'Delete An Escalation',
                'name' => 'view-delete-escalation',
            ],
            // Appointment Module
            [
                'module' => 'Appointment',
                'sub_module' => 'View Appointment Menu',
                'description' => 'View Appointment Menu',
                'name' => 'view-appointment-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Dashboard',
                'description' => 'View Appointment Dashboard',
                'name' => 'view-dashboard-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Notifications',
                'description' => 'View Appointment Notifications',
                'name' => 'view-notifications-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment List',
                'description' => 'View Appointment List',
                'name' => 'view-appointment-list-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Reports',
                'description' => 'View Appointment Reports',
                'name' => 'view-reports-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Types',
                'description' => 'Manage Appointment Types',
                'name' => 'view-types-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Status Management',
                'description' => 'View Appointment Statuses',
                'name' => 'view-statuses-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Status Management',
                'description' => 'Create Appointment Statuses',
                'name' => 'create-statuses-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Status Management',
                'description' => 'Edit Appointment Statuses',
                'name' => 'edit-statuses-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Status Management',
                'description' => 'Delete Appointment Statuses',
                'name' => 'delete-statuses-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Final Reasons',
                'description' => 'Manage Final Reasons',
                'name' => 'view-final-reasons-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Site Visit',
                'description' => 'Manage Site Visits',
                'name' => 'view-site-visit-appointment',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointments',
                'description' => 'Appointments',
                'name' => 'view-appointments-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment List',
                'description' => 'View Appointment List',
                'name' => 'view-appointment-list-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Assigned Appointments',
                'description' => 'Assigned Appointments',
                'name' => 'view-assigned-appointments-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'My Appointments',
                'description' => 'My Appointments',
                'name' => 'view-my-appointments-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Infrastructure menu',
                'description' => 'Infrastructure menu',
                'name' => 'view-appointment-infrastructure-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Infrastructure History menu',
                'description' => 'Infrastructure History menu',
                'name' => 'view-appointment-infrastructure-history-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Infrastructure History view',
                'description' => 'Infrastructure History view',
                'name' => 'view-appointment-infrastructure-history-view',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Infrastructure New menu',
                'description' => 'Infrastructure New menu',
                'name' => 'view-appointment-infrastructure-new-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'NOC Menu',
                'description' => 'NOC Menu',
                'name' => 'view-appointment-noc-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Noc History menu',
                'description' => 'NOC History menu',
                'name' => 'view-appointment-noc-history-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Noc History view',
                'description' => 'NOC History view',
                'name' => 'view-appointment-noc-history-view',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'NoC New Menu',
                'description' => 'NOC New Menu',
                'name' => 'view-appointment-noc-new-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Type',
                'description' => 'Appointment Type',
                'name' => 'view-appointment-type-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Final Reasons',
                'description' => 'Appointment Final Reasons',
                'name' => 'view-appointment-final-reasons-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Notification',
                'description' => 'Appointment Notification',
                'name' => 'view-appointment-notification-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Reports',
                'description' => 'Appointment Reports',
                'name' => 'view-appointment-reports-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Create An Appointment',
                'description' => 'Create An Appointment',
                'name' => 'view-appointment-create',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Edit An Appointment',
                'description' => 'Edit An Appointment',
                'name' => 'view-appointment-edit',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'View An Appointment',
                'description' => 'View An Appointment',
                'name' => 'view-appointment-view',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment History',
                'description' => 'Appointment History',
                'name' => 'view-appointment-history',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Handle Appointment List',
                'description' => 'Handle Appointment List',
                'name' => 'view-appointment-handle-list',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Handle Appointment List Edit',
                'description' => 'Handle Appointment List Edit',
                'name' => 'view-appointment-handle-list-edit',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Assigned Appointment Edit',
                'description' => 'Assigned Appointment Edit',
                'name' => 'view-appointment-assigned-edit',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'My Appointment Edit',
                'description' => 'My Appointment Edit',
                'name' => 'view-my-appointment-edit',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Infrastructure History View',
                'description' => 'Infrastructure History View',
                'name' => 'view-appointment-infrastructure-history-view',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Infrastructure History Edit',
                'description' => 'Infrastructure History Edit',
                'name' => 'view-appointment-infrastructure-history-edit',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Noc History View',
                'description' => 'Noc History View',
                'name' => 'view-appointment-noc-history-view',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Noc History Edit',
                'description' => 'Noc History Edit',
                'name' => 'view-appointment-noc-history-edit',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Type Create',
                'description' => 'Appointment Type Create',
                'name' => 'view-appointment-type-create',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Type Edit',
                'description' => 'Appointment Type Edit',
                'name' => 'view-appointment-type-Edit',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Type Create Sub',
                'description' => 'Appointment Type Create Sub',
                'name' => 'view-appointment-type-create-sub',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Type Edit Sub',
                'description' => 'Appointment Type Edit Sub',
                'name' => 'view-appointment-type-Edit-sub',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Final Reasons Create',
                'description' => 'Appointment Final Reasons Create',
                'name' => 'view-appointment-final-reasons-create',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Final Reasons Edit',
                'description' => 'Appointment Final Reasons Edit',
                'name' => 'view-appointment-final-reasons-edit',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Notification',
                'description' => 'Appointment Notification',
                'name' => 'view-appointment-notification-mark-read',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Download Reports',
                'description' => 'Appointment Download Reports',
                'name' => 'view-appointment-download-reports',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Delete An Appointment',
                'description' => 'Delete An Appointment',
                'name' => 'view-appointment-delete',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Regions Menu',
                'description' => 'View Appointment Regions Menu',
                'name' => 'view-appointment-regions-menu',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Regions Create',
                'description' => 'Create An Appointment Region',
                'name' => 'view-appointment-regions-create',
            ],
            [
                'module' => 'Appointment',
                'sub_module' => 'Appointment Regions Edit',
                'description' => 'Edit An Appointment Region',
                'name' => 'view-appointment-regions-edit',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage Dashboard Menu',
                'description' => 'Outage Dashboard Menu',
                'name' => 'view-outage-dashboard-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage Dashboard',
                'description' => 'View Outage Dashboard',
                'name' => 'view-dashboard-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outages',
                'description' => 'View Outages',
                'name' => 'view-outage-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Assigned Outages',
                'description' => 'View Assigned Outages',
                'name' => 'view-assigned-outage-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'My Outage',
                'description' => 'View My Outage',
                'name' => 'view-my-outage-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'OLT Management',
                'description' => 'OLT Management',
                'name' => 'view-olt-management-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Affected Areas',
                'description' => 'View Affected Areas',
                'name' => 'view-affected-areas-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Affected Service',
                'description' => 'View Affected Service',
                'name' => 'view-affected-services-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage Final Reasons',
                'description' => 'View Outage Final Reasons',
                'name' => 'view-outage-final-reasons-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage download Reports',
                'description' => 'Outage download Reports',
                'name' => 'view-outage-download-reports-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Create Outage',
                'description' => 'Create Outage',
                'name' => 'view-create-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Edit Outage',
                'description' => 'Edit Outage',
                'name' => 'view-edit-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'view Outage',
                'description' => 'View Outage',
                'name' => 'view-view-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'History Outage',
                'description' => 'History Outage',
                'name' => 'view-history-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Assigned Outage Edit',
                'description' => 'Assigned Outage Edit',
                'name' => 'view-assigned-edit-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Assigned view Outage',
                'description' => 'Assigned view Outage',
                'name' => 'view-assigned-view-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Assigned History Outage',
                'description' => 'Assigned History Outage',
                'name' => 'view-assigned-history-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'My Outage Edit',
                'description' => 'My Outage Edit',
                'name' => 'view-assigned-my-outage-edit',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'OLT Management Edit',
                'description' => 'OLT Management Edit',
                'name' => 'view-olt-management-edit',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'OLT Management Create',
                'description' => 'OLT Management Create',
                'name' => 'view-olt-management-create',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Affected Areas Create',
                'description' => 'Affected Areas Create',
                'name' => 'view-affected-areas-create',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Affected Areas edit',
                'description' => 'Affected Areas edit',
                'name' => 'view-affected-areas-edit',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Affected Service Create',
                'description' => 'Affected Service Create',
                'name' => 'view-affected-services-create',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Affected Service edit',
                'description' => 'Affected Service edit',
                'name' => 'view-affected-services-edit',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage Final Reasons Create',
                'description' => 'Outage Final Reasons Create',
                'name' => 'view-outage-final-reasons-create',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage Final Reasons Edit',
                'description' => 'Outage Final Reasons Edit',
                'name' => 'view-outage-final-reasons-edit',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage Download Reports',
                'description' => 'Outage Download Reports',
                'name' => 'view-outage-download-reports',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Delete Outage',
                'description' => 'Delete Outage',
                'name' => 'view-delete-outage',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage Notification Menu',
                'description' => 'View Outage Notification Menu',
                'name' => 'view-outage-notification-menu',
            ],
            [
                'module' => 'Outage',
                'sub_module' => 'Outage Notification Mark Read',
                'description' => 'Mark Outage Notifications as Read',
                'name' => 'view-outage-notification-mark-read',
            ],
            [
                'module' => 'Escalation',
                'sub_module' => 'List Escalation',
                'description' => 'List All Escalations',
                'name' => 'view-list-escalation',
            ],

        ];
        foreach ($permissions as $permission) {
            $permission = array_merge($permission, ['guard_name' => config('auth.defaults.guard')]);

            Permission::firstOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                $permission
            );
        }
    }
}
