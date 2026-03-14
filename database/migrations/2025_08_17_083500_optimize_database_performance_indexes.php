<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['user_status'], 'idx_users_status');
            $table->index(['department_id'], 'idx_users_department');
            $table->index(['sub_department_id'], 'idx_users_sub_department');
            $table->index(['team_type_id'], 'idx_users_team_type');
            $table->index(['created_at'], 'idx_users_created_at');
            $table->index(['email'], 'idx_users_email');
            $table->index(['username'], 'idx_users_username');
            $table->index(['name'], 'idx_users_name');
            // Composite indexes for common queries
            $table->index(['user_status', 'department_id'], 'idx_users_status_dept');
            $table->index(['department_id', 'created_at'], 'idx_users_dept_created');
        });

        // Outages table indexes (if exists)
        if (Schema::hasTable('outages')) {
            Schema::table('outages', function (Blueprint $table) {
                $table->index(['status'], 'idx_outages_status');
                $table->index(['priority'], 'idx_outages_priority');
                $table->index(['ticket_type'], 'idx_outages_ticket_type');
                $table->index(['assigned_team_id'], 'idx_outages_assigned_team');
                $table->index(['assigned_to'], 'idx_outages_assigned_to');
                $table->index(['reported_by'], 'idx_outages_reported_by');
                $table->index(['resolved_by'], 'idx_outages_resolved_by');
                $table->index(['olt_id'], 'idx_outages_olt');
                $table->index(['created_at'], 'idx_outages_created_at');
                $table->index(['start_time'], 'idx_outages_start_time');
                $table->index(['end_time'], 'idx_outages_end_time');
                $table->index(['sla_breached'], 'idx_outages_sla_breached');
                // Composite indexes for common filter combinations
                $table->index(['status', 'priority'], 'idx_outages_status_priority');
                $table->index(['assigned_team_id', 'status'], 'idx_outages_team_status');
                $table->index(['created_at', 'status'], 'idx_outages_created_status');
                $table->index(['start_time', 'end_time'], 'idx_outages_time_range');
            });
        }

        // Appointments table indexes (if exists)
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->index(['status'], 'idx_appointments_status');
                $table->index(['priority'], 'idx_appointments_priority');
                $table->index(['assigned_team_id'], 'idx_appointments_assigned_team');
                $table->index(['created_by'], 'idx_appointments_created_by');
                $table->index(['created_at'], 'idx_appointments_created_at');
                $table->index(['scheduled_date'], 'idx_appointments_scheduled_date');
                $table->index(['scheduled_time'], 'idx_appointments_scheduled_time');
                $table->index(['appointment_type_id'], 'idx_appointments_type');
                $table->index(['team_type_id'], 'idx_appointments_team_type');
                // Composite indexes
                $table->index(['status', 'priority'], 'idx_appointments_status_priority');
                $table->index(['assigned_team_id', 'status'], 'idx_appointments_team_status');
                $table->index(['scheduled_date', 'status'], 'idx_appointments_date_status');
            });
        }

        // Escalations table indexes (if exists)
        if (Schema::hasTable('escalations')) {
            Schema::table('escalations', function (Blueprint $table) {
                $table->index(['status'], 'idx_escalations_status');
                $table->index(['priority'], 'idx_escalations_priority');
                $table->index(['assigned_to'], 'idx_escalations_assigned_to');
                $table->index(['created_by'], 'idx_escalations_created_by');
                $table->index(['created_at'], 'idx_escalations_created_at');
                $table->index(['appointment_id'], 'idx_escalations_appointment');
                $table->index(['category_id'], 'idx_escalations_category');
                $table->index(['sub_department_id'], 'idx_escalations_sub_dept');
                // Composite indexes
                $table->index(['status', 'priority'], 'idx_escalations_status_priority');
                $table->index(['assigned_to', 'status'], 'idx_escalations_assigned_status');
                $table->index(['created_at', 'status'], 'idx_escalations_created_status');
            });
        }

        // Departments table indexes
        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->index(['department_status'], 'idx_departments_status');
                $table->index(['department_name'], 'idx_departments_name');
            });
        }

        // Team types table indexes
        if (Schema::hasTable('team_types')) {
            Schema::table('team_types', function (Blueprint $table) {
                $table->index(['status'], 'idx_team_types_status');
                $table->index(['department_id'], 'idx_team_types_department');
                $table->index(['type_name'], 'idx_team_types_name');
                $table->index(['department_id', 'status'], 'idx_team_types_dept_status');
            });
        }

        // Teams table indexes (if exists)
        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->index(['status'], 'idx_teams_status');
                $table->index(['team_type_id'], 'idx_teams_type');
                $table->index(['name'], 'idx_teams_name');
            });
        }

        // Roles table indexes
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->index(['name'], 'idx_roles_name');
            });
        }

        // Permissions table indexes
        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->index(['name'], 'idx_permissions_name');
                $table->index(['guard_name'], 'idx_permissions_guard');
            });
        }

        // Model has roles table indexes
        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->index(['model_type', 'model_id'], 'idx_model_roles_model');
                $table->index(['role_id'], 'idx_model_roles_role');
            });
        }

        // Model has permissions table indexes
        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->index(['model_type', 'model_id'], 'idx_model_permissions_model');
                $table->index(['permission_id'], 'idx_model_permissions_permission');
            });
        }

        // OLTs table indexes (if exists)
        if (Schema::hasTable('olts')) {
            Schema::table('olts', function (Blueprint $table) {
                $table->index(['status'], 'idx_olts_status');
                $table->index(['name'], 'idx_olts_name');
            });
        }

        // Report downloads table indexes
        if (Schema::hasTable('report_downloads')) {
            Schema::table('report_downloads', function (Blueprint $table) {
                $table->index(['status'], 'idx_report_downloads_status');
                $table->index(['report_type'], 'idx_report_downloads_type');
                $table->index(['requested_at'], 'idx_report_downloads_requested');
                $table->index(['completed_at'], 'idx_report_downloads_completed');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_department');
            $table->dropIndex('idx_users_sub_department');
            $table->dropIndex('idx_users_team_type');
            $table->dropIndex('idx_users_created_at');
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_username');
            $table->dropIndex('idx_users_name');
            $table->dropIndex('idx_users_status_dept');
            $table->dropIndex('idx_users_dept_created');
        });

        if (Schema::hasTable('outages')) {
            Schema::table('outages', function (Blueprint $table) {
                $table->dropIndex('idx_outages_status');
                $table->dropIndex('idx_outages_priority');
                $table->dropIndex('idx_outages_ticket_type');
                $table->dropIndex('idx_outages_assigned_team');
                $table->dropIndex('idx_outages_assigned_to');
                $table->dropIndex('idx_outages_reported_by');
                $table->dropIndex('idx_outages_resolved_by');
                $table->dropIndex('idx_outages_olt');
                $table->dropIndex('idx_outages_created_at');
                $table->dropIndex('idx_outages_start_time');
                $table->dropIndex('idx_outages_end_time');
                $table->dropIndex('idx_outages_sla_breached');
                $table->dropIndex('idx_outages_status_priority');
                $table->dropIndex('idx_outages_team_status');
                $table->dropIndex('idx_outages_created_status');
                $table->dropIndex('idx_outages_time_range');
            });
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex('idx_appointments_status');
                $table->dropIndex('idx_appointments_priority');
                $table->dropIndex('idx_appointments_assigned_team');
                $table->dropIndex('idx_appointments_created_by');
                $table->dropIndex('idx_appointments_created_at');
                $table->dropIndex('idx_appointments_scheduled_date');
                $table->dropIndex('idx_appointments_scheduled_time');
                $table->dropIndex('idx_appointments_type');
                $table->dropIndex('idx_appointments_team_type');
                $table->dropIndex('idx_appointments_status_priority');
                $table->dropIndex('idx_appointments_team_status');
                $table->dropIndex('idx_appointments_date_status');
            });
        }

        if (Schema::hasTable('escalations')) {
            Schema::table('escalations', function (Blueprint $table) {
                $table->dropIndex('idx_escalations_status');
                $table->dropIndex('idx_escalations_priority');
                $table->dropIndex('idx_escalations_assigned_to');
                $table->dropIndex('idx_escalations_created_by');
                $table->dropIndex('idx_escalations_created_at');
                $table->dropIndex('idx_escalations_appointment');
                $table->dropIndex('idx_escalations_category');
                $table->dropIndex('idx_escalations_sub_dept');
                $table->dropIndex('idx_escalations_status_priority');
                $table->dropIndex('idx_escalations_assigned_status');
                $table->dropIndex('idx_escalations_created_status');
            });
        }

        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropIndex('idx_departments_status');
                $table->dropIndex('idx_departments_name');
            });
        }

        if (Schema::hasTable('team_types')) {
            Schema::table('team_types', function (Blueprint $table) {
                $table->dropIndex('idx_team_types_status');
                $table->dropIndex('idx_team_types_department');
                $table->dropIndex('idx_team_types_name');
                $table->dropIndex('idx_team_types_dept_status');
            });
        }

        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropIndex('idx_teams_status');
                $table->dropIndex('idx_teams_type');
                $table->dropIndex('idx_teams_name');
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropIndex('idx_roles_name');
            });
        }

        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropIndex('idx_permissions_name');
                $table->dropIndex('idx_permissions_guard');
            });
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropIndex('idx_model_roles_model');
                $table->dropIndex('idx_model_roles_role');
            });
        }

        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropIndex('idx_model_permissions_model');
                $table->dropIndex('idx_model_permissions_permission');
            });
        }

        if (Schema::hasTable('olts')) {
            Schema::table('olts', function (Blueprint $table) {
                $table->dropIndex('idx_olts_status');
                $table->dropIndex('idx_olts_name');
            });
        }

        if (Schema::hasTable('report_downloads')) {
            Schema::table('report_downloads', function (Blueprint $table) {
                $table->dropIndex('idx_report_downloads_status');
                $table->dropIndex('idx_report_downloads_type');
                $table->dropIndex('idx_report_downloads_requested');
                $table->dropIndex('idx_report_downloads_completed');
            });
        }
    }
};
