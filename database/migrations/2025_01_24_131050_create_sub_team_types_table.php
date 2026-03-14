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
        Schema::create('sub_team_types', function (Blueprint $table) {
            $table->id();
            $table->string('sub_type_name');
            $table->text('sub_type_description')->nullable();
            $table->enum('sub_type_status', ['Active', 'Inactive'])->default('Active');
            $table->foreignId('team_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Add index for better performance
            $table->index('team_type_id');
            $table->index('sub_type_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_team_types');
    }
};
