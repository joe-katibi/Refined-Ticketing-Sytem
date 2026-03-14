<?php

// Bootstrap Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import necessary classes
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    echo "Checking if closed_by column exists...\n";
    
    if (!Schema::hasColumn('escalations', 'closed_by')) {
        echo "Adding closed_by column to escalations table...\n";
        
        Schema::table('escalations', function (Blueprint $table) {
            $table->unsignedBigInteger('closed_by')->nullable()->after('closed_at');
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
        });
        
        echo "Successfully added closed_by column to escalations table.\n";
    } else {
        echo "Column closed_by already exists in escalations table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
