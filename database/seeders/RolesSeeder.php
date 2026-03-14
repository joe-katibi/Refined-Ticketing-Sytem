<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
/** @package Database\Seeders */
class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Super-admin
        try {
            $role = Role::create(['name' => 'Super-Admin', 'description' => 'Super Admin']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $role = Role::findByName('Super-Admin');
        $role->syncPermissions(Permission::all());


         // Sales-Agent
         try {
            $roleSalesAgent = Role::create(['name' => 'Sales-Agent', 'description' => 'agent who handles Sales']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleSalesAgent= Role::findByName('Sales-Agent');
          // Find or create specific permissions
          $permissions = ['view-dashboard-escalation','view-escalation-menu','view-create-escalation','view-edit-escalation','view-view-escalation','view-history-escalation','view-escalation-notification-mark-read',
		                  'view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu','view-appointment-noc-menu','view-appointment-noc-history-menu',
						  'view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-menu','view-dashboard-outage','view-outage-menu'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleSalesAgent->givePermissionTo($permission);
           }
		    // Team-Leader-Sales
         try {
            $roleTeamLeaderSales = Role::create(['name' => 'Team-Leader-Sales', 'description' => 'role for sales team leader']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleTeamLeaderSales = Role::findByName('Team-Leader-Sales');
         // Find or create specific permissions
         $permissions = ['view-dashboard-escalation','view-escalation-menu','view-escalations-menu','view-create-escalation','view-edit-escalation','view-view-escalation','view-history-escalation','view-escalation-notification-mark-read',
		                  'view-history-escalation','view-handle-edit-escalation','view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu',
						  'view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-menu','view-dashboard-outage','view-outage-menu','view-outage-notification-menu','view-outage-notification-mark-read'];
         foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleTeamLeaderSales->givePermissionTo($permission);
         }
		 // Management
         try {
            $roleManagement = Role::create(['name' => 'Management', 'description' => 'role for Management']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleManagement = Role::findByName('Management');
         // Find or create specific permissions
         $permissions = ['view-escalation-menu','view-dashboard-escalation','view-escalate-menu','view-escalations-menu','view-escalation-categories-menu','view-escalation-notification-menu','view-escalation-report-menu','view-create-escalation',
		                 'view-edit-escalation','view-view-escalation','view-history-escalation','view-handle-edit-escalation','view-escalation-create-categories','view-escalation-edit-categories','view-escalation-sub-categories','view-escalation-create-sub-categories',
						 'view-escalation-edit-sub-categories','view-escalation-view-sub-categories','view-escalation-notification-mark-read','view-escalation-download-reports','view-appointment-menu','view-dashboard-appointment','view-appointments-menu',
						 'view-appointment-list-menu','view-assigned-appointments-menu','view-my-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu','view-appointment-infrastructure-history-view',
						 'view-appointment-infrastructure-new-menu','view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-noc-new-menu','view-appointment-type-menu','view-appointment-final-reasons-menu',
						 'view-appointment-notification-menu','view-appointment-reports-menu','view-appointment-create','view-appointment-edit','view-appointment-view','view-appointment-history','view-appointment-handle-list', 'view-appointment-handle-list-edit',
						 'view-appointment-assigned-edit','view-my-appointment-edit','view-appointment-infrastructure-history-view','view-appointment-infrastructure-history-edit','view-appointment-noc-history-view','view-appointment-noc-history-edit',
						 'view-appointment-type-create','view-appointment-type-Edit','view-appointment-type-create-sub','view-appointment-final-reasons-create','view-appointment-final-reasons-edit','view-appointment-type-Edit-sub','view-appointment-notification-mark-read',
						 'view-appointment-type-Edit-sub','view-appointment-download-reports','view-dashboard-outage','view-outage-menu','view-assigned-outage-menu','view-my-outage-menu','view-olt-management-menu','view-affected-areas-menu','view-affected-services-menu', 'view-outage-final-reasons-menu',
						 'view-outage-download-reports-menu','view-create-outage','view-edit-outage','view-view-outage','view-history-outage','view-assigned-edit-outage','view-assigned-view-outage','view-assigned-history-outage','view-assigned-my-outage-edit',
						 'view-assigned-my-outage-edit','view-olt-management-edit','view-olt-management-create','view-olt-management-create','view-affected-areas-create','view-affected-areas-edit','view-affected-services-create','view-affected-services-edit',
						 'view-outage-final-reasons-create','view-outage-final-reasons-edit','view-outage-download-reports','view-outage-notification-menu','view-outage-notification-mark-read'];
         foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();
          // Assign the permission to the role
          $roleManagement->givePermissionTo($permission);
         }
         // Call-Center-Agent
         try {
            $roleCallCenterAgent = Role::create(['name' => 'Call-Center-Agent', 'description' => 'agent who handles chats/calls']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleCallCenterAgent= Role::findByName('Call-Center-Agent');
          // Find or create specific permissions
          $permissions = ['view-dashboard-escalation','view-escalation-menu','view-create-escalation','view-edit-escalation','view-view-escalation','view-history-escalation','view-escalation-notification-mark-read',
		                  'view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu','view-appointment-noc-menu','view-appointment-noc-history-menu',
						  'view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-menu','view-dashboard-outage','view-outage-menu','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleCallCenterAgent->givePermissionTo($permission);
           }
		   // Service-Support-Agent
         try {
            $roleServiceSupportAgent = Role::create(['name' => 'Service-Support-Agent', 'description' => 'Service Support Agent teir II']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleServiceSupportAgent= Role::findByName('Service-Support-Agent');
          // Find or create specific permissions
          $permissions = ['view-escalation-menu','view-dashboard-escalation','view-escalate-menu','view-escalations-menu','view-escalation-notification-menu','view-create-escalation','view-edit-escalation','view-view-escalation',
		  'view-history-escalation','view-handle-edit-escalation','view-escalation-notification-mark-read','view-appointment-menu','view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu',
		  'view-appointment-infrastructure-history-menu','view-appointment-infrastructure-history-view','view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-notification-menu',
		  'view-appointment-create','view-appointment-view','view-appointment-history','view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-mark-read','view-dashboard-outage',
		  'view-outage-menu','view-view-outage','view-history-outage','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleServiceSupportAgent->givePermissionTo($permission);
           }
		   // Back-office-Agent
         try {
            $roleBackOfficeAgent = Role::create(['name' => 'Back-office-Agent', 'description' => 'Back-office-Agent']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleBackOfficeAgent= Role::findByName('Back-office-Agent');
          // Find or create specific permissions
          $permissions = ['view-escalation-menu','view-dashboard-escalation','view-escalate-menu','view-escalations-menu','view-escalation-notification-menu','view-create-escalation','view-edit-escalation','view-view-escalation',
		  'view-history-escalation','view-handle-edit-escalation','view-escalation-notification-mark-read','view-appointment-menu','view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu',
		  'view-appointment-infrastructure-history-menu','view-appointment-infrastructure-history-view','view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-notification-menu',
		  'view-appointment-create','view-appointment-view','view-appointment-history','view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-mark-read','view-dashboard-outage',
		  'view-outage-menu','view-view-outage','view-history-outage','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleBackOfficeAgent->givePermissionTo($permission);
           }

		  // Team leader Call Center
         try {
            $roleTeamLeaderCallCenter = Role::create(['name' => 'Team-Leader-Call-Center', 'description' => 'role for team leader call center']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleTeamLeaderCallCenter= Role::findByName('Team-Leader-Call-Center');
          // Find or create specific permissions
          $permissions = ['view-escalation-menu','view-dashboard-escalation','view-escalate-menu','view-escalations-menu','view-escalation-categories-menu','view-escalation-create-categories','view-escalation-notification-menu','view-create-escalation',
		  'view-escalation-edit-categories','view-escalation-sub-categories','view-escalation-create-sub-categories','view-escalation-edit-sub-categories','view-escalation-download-reports','view-edit-escalation','view-view-escalation','view-history-escalation',
		  'view-handle-edit-escalation','view-escalation-notification-mark-read','view-appointment-menu','view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu',
		  'view-appointment-infrastructure-history-view','view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-notification-menu','view-appointment-create','view-appointment-view',
		  'view-appointment-history','view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-mark-read','view-dashboard-outage','view-outage-menu','view-view-outage','view-history-outage','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleTeamLeaderCallCenter->givePermissionTo($permission);
           }
		   // Team leader Service Delivery
         try {
            $roleTeamLeaderServiceDelivery = Role::create(['name' => 'Team-leader-Service-Delivery', 'description' => 'Role for team leader service delivery']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleTeamLeaderServiceDelivery= Role::findByName('Team-leader-Service-Delivery');
          // Find or create specific permissions
          $permissions = ['view-escalation-menu','view-dashboard-escalation','view-escalate-menu','view-escalations-menu','view-escalation-notification-menu','view-create-escalation','view-edit-escalation','view-view-escalation',
		  'view-history-escalation','view-handle-edit-escalation','view-escalation-notification-mark-read','view-appointment-menu','view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu',
		  'view-appointment-infrastructure-history-menu','view-appointment-infrastructure-history-view','view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-notification-menu',
		  'view-appointment-list-menu','view-assigned-appointments-menu','view-appointment-type-menu','view-appointment-final-reasons-menu','view-appointment-reports-menu','view-appointment-edit','view-appointment-handle-list','view-appointment-handle-list-edit',
		   'view-appointment-assigned-edit','view-appointment-type-create','view-appointment-type-Edit','view-appointment-type-create-sub','view-appointment-type-Edit-sub','view-appointment-final-reasons-create','view-appointment-final-reasons-edit',
		   'view-appointment-type-Edit-sub','view-appointment-download-reports','view-appointment-create','view-appointment-view','view-appointment-history','view-appointment-infrastructure-history-view','view-appointment-noc-history-view',
		   'view-appointment-notification-mark-read','view-dashboard-outage','view-outage-menu','view-view-outage','view-history-outage','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleTeamLeaderServiceDelivery->givePermissionTo($permission);
           }

		  // Dispatcher Service Delivery
         try {
            $roleDispatcherServiceDelivery = Role::create(['name' => 'Dispatcher-Service-Delivery', 'description' => 'role for Dispatcher in service Delivery']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleDispatcherServiceDelivery= Role::findByName('Dispatcher-Service-Delivery');
          // Find or create specific permissions
          $permissions = ['view-escalation-menu','view-dashboard-escalation','view-escalate-menu','view-escalations-menu','view-escalation-notification-menu','view-create-escalation','view-edit-escalation','view-view-escalation',
		  'view-history-escalation','view-handle-edit-escalation','view-escalation-notification-mark-read','view-appointment-menu','view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu',
		  'view-appointment-infrastructure-history-menu','view-appointment-infrastructure-history-view','view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-notification-menu',
		  'view-appointment-create','view-appointment-view','view-appointment-history','view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-mark-read','view-dashboard-outage',
		  'view-outage-menu','view-view-outage','view-history-outage','view-appointment-download-reports','view-appointment-assigned-edit','view-appointment-handle-list-edit','view-appointment-handle-list','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleDispatcherServiceDelivery->givePermissionTo($permission);
           }
		   // Dispatcher infrastructure and Tier II NOC
         try {
            $roleDispatcherInfrastructureTierIINoc = Role::create(['name' => 'Dispatcher-Infrastructure-Tier-II-Noc', 'description' => 'role for Dispatcher in infrastructure and Tier II NOC']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleDispatcherInfrastructureTierIINoc = Role::findByName('Dispatcher-Infrastructure-Tier-II-Noc');
          // Find or create specific permissions
          $permissions = ['view-escalation-menu','view-dashboard-escalation','view-escalate-menu','view-escalations-menu','view-escalation-notification-menu','view-create-escalation','view-edit-escalation','view-view-escalation',
		  'view-history-escalation','view-handle-edit-escalation','view-escalation-notification-mark-read','view-appointment-menu','view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu',
		  'view-appointment-infrastructure-history-menu','view-appointment-infrastructure-history-view','view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-notification-menu',
		  'view-appointment-create','view-appointment-view','view-appointment-history','view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-mark-read','view-dashboard-outage',
		  'view-outage-menu','view-view-outage','view-history-outage','view-assigned-outage-menu','view-olt-management-menu','view-create-outage','view-edit-outage','view-assigned-edit-outage','view-assigned-view-outage','view-assigned-history-outage','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleDispatcherInfrastructureTierIINoc->givePermissionTo($permission);
           }

		   // Team Leader infrastructure/NOC
         try {
            $roleTeamLeaderNocInfrastructure = Role::create(['name' => 'Team-leader-noc-Infrastructure', 'description' => 'role for Team leader both in NOC and infrastructure']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleTeamLeaderNocInfrastructure = Role::findByName('Team-leader-noc-Infrastructure');
          // Find or create specific permissions
          $permissions = ['view-escalation-menu','view-dashboard-escalation','view-escalate-menu','view-escalations-menu','view-escalation-notification-menu','view-create-escalation','view-edit-escalation','view-view-escalation',
		  'view-history-escalation','view-handle-edit-escalation','view-escalation-notification-mark-read','view-appointment-menu','view-dashboard-appointment','view-appointments-menu','view-appointment-infrastructure-menu',
		  'view-appointment-infrastructure-history-menu','view-appointment-infrastructure-history-view','view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-notification-menu',
		  'view-appointment-create','view-appointment-view','view-appointment-history','view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-mark-read','view-dashboard-outage',
		  'view-outage-menu','view-view-outage','view-history-outage','view-assigned-outage-menu','view-olt-management-menu','view-create-outage','view-edit-outage','view-assigned-edit-outage','view-assigned-view-outage','view-assigned-history-outage',
		  'view-outage-download-reports','view-outage-final-reasons-edit','view-outage-final-reasons-create','view-affected-services-edit','view-affected-services-create','view-affected-areas-edit','view-affected-areas-create','view-olt-management-create',
		  'view-olt-management-create','view-olt-management-edit','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();

          // Assign the permission to the role
          $roleTeamLeaderNocInfrastructure->givePermissionTo($permission);
           }

		   // Field Technician Both appointment, Noc and infrastructure
         try {
            $roleFieldTechnician = Role::create(['name' => 'Field-Technician', 'description' => 'Field Technician for appointments, NOC and infrastructure']);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Ignore
        }
        $roleFieldTechnician = Role::findByName('Field-Technician');
          // Find or create specific permissions
          $permissions = ['view-appointment-menu','view-dashboard-appointment','view-my-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu','view-appointment-infrastructure-history-view',
		  'view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-noc-history-view','view-appointment-notification-menu','view-my-appointment-edit','view-dashboard-outage','view-outage-menu','view-my-outage-menu',
		  'view-assigned-my-outage-edit','view-outage-notification-menu','view-outage-notification-mark-read'];
          foreach ($permissions as $permissionName) {
          $permission = Permission::where('name', $permissionName)->first();
          $roleFieldTechnician->givePermissionTo($permission);
           }

    }
}
