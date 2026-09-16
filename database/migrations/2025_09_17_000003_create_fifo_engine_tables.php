<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIFO work-allocation engine (spec section 5). MVP scope: queue_entries
 * carries queue age + priority + region and is assigned via a locked,
 * transactional pick — never `SELECT ... ORDER BY id DESC` — same concurrency
 * discipline as the ticket-numbering fix. Rooted in Escalations first, reused
 * by Appointments and Outages via the same `work_type` discriminator.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_queues', function (Blueprint $table) {
            $table->id();
            $table->string('module', 30); // escalation | appointment | outage
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('module');
        });

        Schema::create('queue_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_queue_id')->constrained('work_queues')->cascadeOnDelete();
            $table->string('work_type', 30); // escalation | appointment | outage
            $table->unsignedBigInteger('work_id');
            $table->string('priority', 20)->default('Medium'); // Critical/High/Medium/Low
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('status', 20)->default('waiting'); // waiting|assigned|returned|cancelled
            $table->timestamp('queue_entered_at');
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['work_type', 'work_id']);
            $table->index(['work_queue_id', 'status', 'priority', 'queue_entered_at'], 'queue_entries_dispatch_idx');
        });

        Schema::create('agent_workloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('module', 30); // which queue this agent is eligible for
            $table->unsignedInteger('active_count')->default(0);
            $table->unsignedInteger('capacity')->nullable(); // null = unlimited
            $table->boolean('is_available')->default(true);
            $table->timestamp('last_assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'module']);
        });

        Schema::create('assignment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_entry_id')->constrained('queue_entries')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('result', 20); // assigned|no_eligible_agent|manual_override
            $table->string('reason')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); // null = system/automatic
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_attempts');
        Schema::dropIfExists('agent_workloads');
        Schema::dropIfExists('queue_entries');
        Schema::dropIfExists('work_queues');
    }
};
