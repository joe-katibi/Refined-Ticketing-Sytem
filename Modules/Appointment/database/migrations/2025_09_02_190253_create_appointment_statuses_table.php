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
        if (Schema::hasTable('appointment_statuses')) {
            return;
        }

        Schema::create('appointment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#3B82F6'); // Default blue color
            $table->string('badge_class', 50)->nullable(); // For CSS styling
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->boolean('is_system')->default(false); // To mark system-defined statuses
            $table->integer('sort_order')->default(0); // For custom ordering
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('edited_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_statuses');
    }
};
