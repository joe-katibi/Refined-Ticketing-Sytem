<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix status column in lists table
        if (Schema::hasTable('lists')) {
            // Use raw SQL to modify column type directly
            DB::statement('ALTER TABLE lists MODIFY status VARCHAR(50)'); 
        }

        // Fix status column in escalations table
        if (Schema::hasTable('escalations')) {
            // Use raw SQL to modify column type directly
            DB::statement('ALTER TABLE escalations MODIFY status VARCHAR(50)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status column in lists table
        if (Schema::hasTable('lists')) {
            // Use raw SQL to modify column type directly
            DB::statement('ALTER TABLE lists MODIFY status VARCHAR(20)');
        }

        // Revert status column in escalations table
        if (Schema::hasTable('escalations')) {
            // Use raw SQL to modify column type directly
            DB::statement('ALTER TABLE escalations MODIFY status VARCHAR(20)');
        }
    }
};
