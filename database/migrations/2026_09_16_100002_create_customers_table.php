<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// New end-customer/subscriber table — nothing like this existed before
// (the `users` table is staff/agents only). A customer's ONU physically
// terminates at a FAT, so fat_id is the natural link back into the
// OLT -> Slot -> PON Port -> FDT -> FAT hierarchy. Nullable/no FK
// constraint on fat_id so a customer can be recorded before their FAT
// is known, matching how olt_id/slot_id are handled loosely elsewhere
// in this app (outages/appointments/escalations all reference OLT
// entities by nullable column, not a hard FK).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->string('name');
            $table->string('mobile_number')->nullable();
            $table->string('alternative_number')->nullable();
            $table->string('address')->nullable();
            $table->string('onu_type')->nullable();
            $table->string('onu_physical_address')->nullable();
            $table->string('bandwidth_profile')->nullable();
            $table->foreignId('fat_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('Active'); // Active, Inactive, Suspended
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
