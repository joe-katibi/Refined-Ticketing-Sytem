<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->string('account_number');
            $table->string('ticket_id')->unique();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('sub_department_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('sub_category_id');
            $table->text('description');
            $table->string('priority');
            $table->string('olt_id')->nullable();
            $table->string('slot_id')->nullable();
            $table->enum('status', ['Escalated-Open', 'Escalated-Closed']);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lists');
    }
};
