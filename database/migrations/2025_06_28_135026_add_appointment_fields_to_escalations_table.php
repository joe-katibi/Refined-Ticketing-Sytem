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
        Schema::table('escalations', function (Blueprint $table) {
            // Support fields
            if (!Schema::hasColumn('escalations', 'support_date')) {
                $table->date('support_date')->nullable()->after('edited_by');
            }
            if (!Schema::hasColumn('escalations', 'support_time')) {
                $table->time('support_time')->nullable()->after('support_date');
            }
            if (!Schema::hasColumn('escalations', 'support_address')) {
                $table->text('support_address')->nullable()->after('support_time');
            }
            if (!Schema::hasColumn('escalations', 'support_notes')) {
                $table->text('support_notes')->nullable()->after('support_address');
            }
            
            // Shifting fields
            if (!Schema::hasColumn('escalations', 'shifting_date')) {
                $table->date('shifting_date')->nullable()->after('support_notes');
            }
            if (!Schema::hasColumn('escalations', 'shifting_time')) {
                $table->time('shifting_time')->nullable()->after('shifting_date');
            }
            if (!Schema::hasColumn('escalations', 'shifting_address')) {
                $table->text('shifting_address')->nullable()->after('shifting_time');
            }
            if (!Schema::hasColumn('escalations', 'shifting_notes')) {
                $table->text('shifting_notes')->nullable()->after('shifting_address');
            }
            
            // Installation fields
            if (!Schema::hasColumn('escalations', 'installation_date')) {
                $table->date('installation_date')->nullable()->after('shifting_notes');
            }
            if (!Schema::hasColumn('escalations', 'installation_time')) {
                $table->time('installation_time')->nullable()->after('installation_date');
            }
            if (!Schema::hasColumn('escalations', 'installation_address')) {
                $table->text('installation_address')->nullable()->after('installation_time');
            }
            if (!Schema::hasColumn('escalations', 'installation_notes')) {
                $table->text('installation_notes')->nullable()->after('installation_address');
            }
            
            // WiFi Extender fields
            if (!Schema::hasColumn('escalations', 'wifi_extender_date')) {
                $table->date('wifi_extender_date')->nullable()->after('installation_notes');
            }
            if (!Schema::hasColumn('escalations', 'wifi_extender_time')) {
                $table->time('wifi_extender_time')->nullable()->after('wifi_extender_date');
            }
            if (!Schema::hasColumn('escalations', 'wifi_extender_address')) {
                $table->text('wifi_extender_address')->nullable()->after('wifi_extender_time');
            }
            if (!Schema::hasColumn('escalations', 'wifi_extender_notes')) {
                $table->text('wifi_extender_notes')->nullable()->after('wifi_extender_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escalations', function (Blueprint $table) {
            // Support fields
            $table->dropColumn([
                'support_date',
                'support_time',
                'support_address',
                'support_notes',
                // Shifting fields
                'shifting_date',
                'shifting_time',
                'shifting_address',
                'shifting_notes',
                // Installation fields
                'installation_date',
                'installation_time',
                'installation_address',
                'installation_notes',
                // WiFi Extender fields
                'wifi_extender_date',
                'wifi_extender_time',
                'wifi_extender_address',
                'wifi_extender_notes',
            ]);
        });
    }
};
