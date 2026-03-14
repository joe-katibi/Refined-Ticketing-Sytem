<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Appointment\Models\AppointmentType;
use Modules\Appointment\Models\Appointment;

echo "Checking Appointment Data\n";
echo "========================\n\n";

// Check appointment types
$appointmentTypes = AppointmentType::with('subTypes')->get();
echo "Appointment Types: " . $appointmentTypes->count() . "\n";

if ($appointmentTypes->count() === 0) {
    echo "❌ No appointment types found - this is why the list page is empty\n";
    echo "Creating sample appointment type...\n";
    
    $sampleType = AppointmentType::create([
        'type_name' => 'Installation',
        'description' => 'Installation appointments',
        'status' => 'Active'
    ]);
    echo "✓ Created sample appointment type: {$sampleType->type_name}\n";
} else {
    foreach ($appointmentTypes as $type) {
        echo "- {$type->type_name} (SubTypes: {$type->subTypes->count()})\n";
    }
}

// Check appointments
$appointments = Appointment::count();
echo "\nAppointments: {$appointments}\n";

if ($appointments === 0) {
    echo "❌ No appointments found\n";
} else {
    echo "✓ Appointments exist\n";
}

echo "\nDone!\n";
