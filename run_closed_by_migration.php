<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run the migration
$migrator = $app->make('migrator');
$migrator->run([__DIR__.'/database/migrations'], ['pretend' => false]);

echo "Migration completed successfully!\n";
