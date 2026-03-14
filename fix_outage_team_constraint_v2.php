<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===== Fixing Outage Team Constraint (v2) =====\n\n";

try {
  echo "1. Checking all constraints on outages table...\n";

  // Get all foreign key constraints on the outages table
  $constraints = DB::select("
        SELECT
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = 'outages'
        AND TABLE_SCHEMA = DATABASE()
        AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY CONSTRAINT_NAME
    ");

  echo '   Found ' . count($constraints) . " foreign key constraints:\n";
  foreach ($constraints as $constraint) {
    echo "   - {$constraint->CONSTRAINT_NAME}: {$constraint->COLUMN_NAME} -> {$constraint->REFERENCED_TABLE_NAME}.{$constraint->REFERENCED_COLUMN_NAME}\n";
  }

  // Find constraints that reference operational_teams
  $operationalTeamConstraints = array_filter($constraints, function ($c) {
    return $c->REFERENCED_TABLE_NAME === 'operational_teams';
  });

  if (empty($operationalTeamConstraints)) {
    echo "\n✅ No constraints reference operational_teams. Checking for team_types...\n";

    $teamTypeConstraints = array_filter($constraints, function ($c) {
      return $c->REFERENCED_TABLE_NAME === 'team_types' && $c->COLUMN_NAME === 'assigned_team_id';
    });

    if (!empty($teamTypeConstraints)) {
      echo "✅ Constraint already references team_types correctly!\n";
      return;
    } else {
      echo "❌ No constraint found for assigned_team_id -> team_types\n";
      echo "Creating new constraint...\n";
    }
  } else {
    echo "\n2. Dropping constraints that reference operational_teams...\n";

    foreach ($operationalTeamConstraints as $constraint) {
      if ($constraint->COLUMN_NAME === 'assigned_team_id') {
        echo "   Dropping constraint: {$constraint->CONSTRAINT_NAME}\n";
        DB::statement("ALTER TABLE outages DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
        echo "   ✓ Dropped {$constraint->CONSTRAINT_NAME}\n";
      }
    }
  }

  echo "\n3. Creating new foreign key constraint to team_types...\n";

  // Create new constraint with a unique name
  $newConstraintName = 'fk_outages_assigned_team_type';

  try {
    DB::statement(
      "ALTER TABLE outages ADD CONSTRAINT {$newConstraintName} FOREIGN KEY (assigned_team_id) REFERENCES team_types(id) ON DELETE SET NULL ON UPDATE CASCADE"
    );
    echo "   ✓ Created constraint: {$newConstraintName}\n";
  } catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
      echo "   ⚠️  Constraint already exists, checking if it's correct...\n";
    } else {
      throw $e;
    }
  }

  echo "\n4. Final verification...\n";

  // Check final state
  $finalConstraints = DB::select("
        SELECT
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = 'outages'
        AND TABLE_SCHEMA = DATABASE()
        AND COLUMN_NAME = 'assigned_team_id'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");

  $teamTypeConstraint = null;
  foreach ($finalConstraints as $constraint) {
    echo "   Found: {$constraint->CONSTRAINT_NAME} -> {$constraint->REFERENCED_TABLE_NAME}\n";
    if ($constraint->REFERENCED_TABLE_NAME === 'team_types') {
      $teamTypeConstraint = $constraint;
    }
  }

  if ($teamTypeConstraint) {
    echo "\n✅ SUCCESS! Constraint now references team_types table\n";
    echo "Constraint name: {$teamTypeConstraint->CONSTRAINT_NAME}\n";
    echo "References: {$teamTypeConstraint->REFERENCED_TABLE_NAME}.{$teamTypeConstraint->REFERENCED_COLUMN_NAME}\n";
  } else {
    throw new Exception('Failed to create constraint referencing team_types');
  }
} catch (Exception $e) {
  echo "\n❌ Error: {$e->getMessage()}\n";
  echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n===== Process Complete =====\n";
