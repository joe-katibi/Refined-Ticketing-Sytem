<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupportAddressToEscalationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escalation_histories', function (Blueprint $table) {
            // Check if the column doesn't exist before adding it
            if (!Schema::hasColumn('escalation_histories', 'support_address')) {
                $table->string('support_address', 255)->nullable()->after('support_time');
            }
            
            // Add other missing columns that might be referenced in the controller
            if (!Schema::hasColumn('escalation_histories', 'support_date')) {
                $table->date('support_date')->nullable()->after('appointment_type_id');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'support_time')) {
                $table->string('support_time', 50)->nullable()->after('support_date');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'support_notes')) {
                $table->text('support_notes')->nullable()->after('support_address');
            }
            
            // Shifting appointment fields
            if (!Schema::hasColumn('escalation_histories', 'shifting_date')) {
                $table->date('shifting_date')->nullable()->after('support_notes');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'shifting_time')) {
                $table->string('shifting_time', 50)->nullable()->after('shifting_date');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'shifting_address')) {
                $table->string('shifting_address', 255)->nullable()->after('shifting_time');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'shifting_notes')) {
                $table->text('shifting_notes')->nullable()->after('shifting_address');
            }
            
            // Installation appointment fields
            if (!Schema::hasColumn('escalation_histories', 'installation_date')) {
                $table->date('installation_date')->nullable()->after('shifting_notes');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'installation_time')) {
                $table->string('installation_time', 50)->nullable()->after('installation_date');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'installation_address')) {
                $table->string('installation_address', 255)->nullable()->after('installation_time');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'installation_notes')) {
                $table->text('installation_notes')->nullable()->after('installation_address');
            }
            
            // WiFi Extender appointment fields
            if (!Schema::hasColumn('escalation_histories', 'wifi_extender_date')) {
                $table->date('wifi_extender_date')->nullable()->after('installation_notes');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'wifi_extender_time')) {
                $table->string('wifi_extender_time', 50)->nullable()->after('wifi_extender_date');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'wifi_extender_address')) {
                $table->string('wifi_extender_address', 255)->nullable()->after('wifi_extender_time');
            }
            
            if (!Schema::hasColumn('escalation_histories', 'wifi_extender_notes')) {
                $table->text('wifi_extender_notes')->nullable()->after('wifi_extender_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escalation_histories', function (Blueprint $table) {
            // Remove all columns added in the up method
            $columns = [
                'support_date', 'support_time', 'support_address', 'support_notes',
                'shifting_date', 'shifting_time', 'shifting_address', 'shifting_notes',
                'installation_date', 'installation_time', 'installation_address', 'installation_notes',
                'wifi_extender_date', 'wifi_extender_time', 'wifi_extender_address', 'wifi_extender_notes'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('escalation_histories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
