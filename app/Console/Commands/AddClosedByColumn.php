<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AddClosedByColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escalations:add-closed-by';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add closed_by column to escalations table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking if closed_by column exists...');
        
        if (!Schema::hasColumn('escalations', 'closed_by')) {
            $this->info('Adding closed_by column to escalations table...');
            
            try {
                Schema::table('escalations', function (Blueprint $table) {
                    $table->unsignedBigInteger('closed_by')->nullable()->after('closed_at');
                    $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
                });
                
                $this->info('Successfully added closed_by column to escalations table.');
            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
                $this->line('Trying alternative approach...');
                
                try {
                    DB::statement('ALTER TABLE escalations ADD COLUMN closed_by BIGINT UNSIGNED NULL AFTER closed_at');
                    DB::statement('ALTER TABLE escalations ADD CONSTRAINT escalations_closed_by_foreign FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL');
                    $this->info('Successfully added closed_by column using raw SQL.');
                } catch (\Exception $e2) {
                    $this->error('Error with alternative approach: ' . $e2->getMessage());
                    return 1;
                }
            }
        } else {
            $this->info('Column closed_by already exists in escalations table.');
        }
        
        return 0;
    }
}
