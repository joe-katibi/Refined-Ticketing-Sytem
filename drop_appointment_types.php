<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    if (Schema::hasTable('appointment_types')) {
        Schema::dropIfExists('appointment_types');
        echo "Table 'appointment_types' dropped successfully.\n";
    } else {
        echo "Table 'appointment_types' does not exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
