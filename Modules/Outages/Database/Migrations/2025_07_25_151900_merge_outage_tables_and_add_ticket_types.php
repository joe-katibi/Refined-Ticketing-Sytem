<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MergeOutageTablesAndAddTicketTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, add new columns to outages table to merge functionality
        Schema::table('outages', function (Blueprint $table) {
            // Add ticket type for numbering system
            $table->enum('ticket_type', ['regular', 'emergency', 'planned_maintenance'])->default('regular')->after('ticket_number');
            
            // Add technical fields (OLT, Slot, Port)
            $table->string('olt_id')->nullable()->after('description');
            $table->string('slot_id')->nullable()->after('olt_id');
            $table->string('port_id')->nullable()->after('slot_id');
            
            // Add impact and urgency fields from outage_tickets
            $table->enum('impact', ['Low', 'Medium', 'High', 'Critical'])->default('Medium')->after('priority');
            $table->enum('urgency', ['Low', 'Medium', 'High', 'Critical'])->default('Medium')->after('impact');
            
            // Add resolution notes
            $table->text('resolution_notes')->nullable()->after('resolution');
            
            // Add indexes for better performance
            $table->index(['ticket_type', 'status']);
            $table->index(['olt_id', 'slot_id', 'port_id']);
        });

        // Migrate data from outage_tickets to outages if there are any existing records
        if (Schema::hasTable('outage_tickets')) {
            // Get all outage tickets and merge them into outages
            $outageTickets = DB::table('outage_tickets')->get();
            
            foreach ($outageTickets as $ticket) {
                // Check if parent outage exists
                $outage = DB::table('outages')->where('id', $ticket->outage_id)->first();
                
                if ($outage) {
                    // Update the outage with ticket data
                    DB::table('outages')->where('id', $ticket->outage_id)->update([
                        'impact' => $ticket->impact,
                        'urgency' => $ticket->urgency,
                        'resolution_notes' => $ticket->resolution_notes,
                        'updated_at' => now(),
                    ]);
                } else {
                    // Create a new outage record from the ticket data
                    DB::table('outages')->insert([
                        'ticket_number' => $ticket->ticket_number,
                        'ticket_type' => 'regular', // Default type for migrated data
                        'title' => $ticket->title,
                        'description' => $ticket->description,
                        'status' => $ticket->status,
                        'priority' => $ticket->priority,
                        'impact' => $ticket->impact,
                        'urgency' => $ticket->urgency,
                        'start_time' => $ticket->start_time,
                        'end_time' => $ticket->end_time,
                        'resolution' => $ticket->resolution,
                        'resolution_notes' => $ticket->resolution_notes,
                        'assigned_team_id' => $ticket->assigned_team_id,
                        'assigned_to' => $ticket->assigned_to,
                        'reported_by' => $ticket->reported_by,
                        'sla_breached' => $ticket->sla_breached,
                        'sla_breach_time' => $ticket->sla_breach_time,
                        'created_by' => $ticket->created_by,
                        'updated_by' => $ticket->updated_by,
                        'created_at' => $ticket->created_at,
                        'updated_at' => $ticket->updated_at,
                        'deleted_at' => $ticket->deleted_at,
                    ]);
                }
            }
        }

        // Update related tables to reference outages instead of outage_tickets
        if (Schema::hasTable('outage_reasons')) {
            Schema::table('outage_reasons', function (Blueprint $table) {
                // Drop the ticket_id foreign key if it exists
                if (Schema::hasColumn('outage_reasons', 'ticket_id')) {
                    $table->dropForeign(['ticket_id']);
                    $table->dropColumn('ticket_id');
                }
                
                // Ensure outage_id exists and is properly constrained
                if (!Schema::hasColumn('outage_reasons', 'outage_id')) {
                    $table->foreignId('outage_id')->after('id')->constrained('outages')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('outage_attachments')) {
            Schema::table('outage_attachments', function (Blueprint $table) {
                // Drop the ticket_id foreign key if it exists
                if (Schema::hasColumn('outage_attachments', 'ticket_id')) {
                    $table->dropForeign(['ticket_id']);
                    $table->dropColumn('ticket_id');
                }
                
                // Ensure outage_id exists and is properly constrained
                if (!Schema::hasColumn('outage_attachments', 'outage_id')) {
                    $table->foreignId('outage_id')->after('id')->constrained('outages')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('outage_progress')) {
            Schema::table('outage_progress', function (Blueprint $table) {
                // Drop the ticket_id foreign key if it exists
                if (Schema::hasColumn('outage_progress', 'ticket_id')) {
                    $table->dropForeign(['ticket_id']);
                    $table->dropColumn('ticket_id');
                }
                
                // Ensure outage_id exists and is properly constrained
                if (!Schema::hasColumn('outage_progress', 'outage_id')) {
                    $table->foreignId('outage_id')->after('id')->constrained('outages')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the new columns from outages table
        Schema::table('outages', function (Blueprint $table) {
            $table->dropIndex(['ticket_type', 'status']);
            $table->dropIndex(['olt_id', 'slot_id', 'port_id']);
            
            $table->dropColumn([
                'ticket_type',
                'olt_id',
                'slot_id', 
                'port_id',
                'impact',
                'urgency',
                'resolution_notes'
            ]);
        });

        // Note: We don't recreate the outage_tickets table or restore data
        // as this would be a destructive operation. If rollback is needed,
        // it should be done manually with proper data backup.
    }
}
