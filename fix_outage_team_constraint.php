<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===== Fixing Outage Team Constraint =====\n\n";

try {
  // Start transaction
  DB::beginTransaction();

  echo "1. Checking current constraint...\n";

  // Check current constraint
  $currentConstraints = DB::select("
        SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = 'outages'
        AND COLUMN_NAME = 'assigned_team_id'
        AND CONSTRAINT_NAME = 'outages_assigned_team_id_foreign'
    ");

  if (!empty($currentConstraints)) {
    $constraint = $currentConstraints[0];
    echo "   Current constraint references: {$constraint->REFERENCED_TABLE_NAME}\n";

    if ($constraint->REFERENCED_TABLE_NAME === 'team_types') {
      echo "   ✅ Constraint already references team_types. No changes needed.\n";
      DB::rollBack();
      return;
    }
  }

  echo "2. Dropping existing foreign key constraint...\n";

  // Drop the existing foreign key constraint
  DB::statement('ALTER TABLE outages DROP FOREIGN KEY outages_assigned_team_id_foreign');
  echo "   ✓ Dropped foreign key constraint\n";

  echo "3. Creating new foreign key constraint to team_types...\n";

  // Add new foreign key constraint to team_types
  DB::statement(
    'ALTER TABLE outages ADD CONSTRAINT outages_assigned_team_id_foreign FOREIGN KEY (assigned_team_id) REFERENCES team_types(id)'
  );
  echo "   ✓ Created new foreign key constraint\n";

  echo "4. Verifying constraint...\n";

  // Check if constraint exists
  $constraints = DB::select("
        SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = 'outages'
        AND COLUMN_NAME = 'assigned_team_id'
        AND CONSTRAINT_NAME = 'outages_assigned_team_id_foreign'
    ");

  if (!empty($constraints)) {
    $constraint = $constraints[0];
    echo "   ✓ Constraint exists and references: {$constraint->REFERENCED_TABLE_NAME}\n";

    if ($constraint->REFERENCED_TABLE_NAME === 'team_types') {
      echo "   ✅ Constraint correctly references team_types table\n";
    } else {
      throw new Exception("Constraint references wrong table: {$constraint->REFERENCED_TABLE_NAME}");
    }
  } else {
    throw new Exception('Constraint not found after creation');
  }

  // Commit transaction
  DB::commit();

  echo "\n✅ Successfully updated foreign key constraint!\n";
  echo "The outages.assigned_team_id now references team_types.id\n";
  echo "\nYou can now test the outage team assignment functionality.\n";
} catch (Exception $e) {
  // Rollback transaction
  DB::rollBack();
  echo "\n❌ Error: {$e->getMessage()}\n";
  echo "Transaction rolled back.\n";
}

echo "\n===== Process Complete =====\n";
