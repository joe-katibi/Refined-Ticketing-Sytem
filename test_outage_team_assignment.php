<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===== Outage Team Assignment Test =====\n\n";

// 1. Check available team types
echo "Available Team Types:\n";
echo "--------------------\n";
$teamTypes = \App\Models\TeamType::where('status', 'Active')->get();
foreach ($teamTypes as $teamType) {
  echo "ID: {$teamType->id}, Name: {$teamType->type_name}\n";

  // Check if there are active teams for this team type
  $teams = \App\Models\Team::where('team_type_id', $teamType->id)
    ->where('status', 'Active')
    ->get();

  if ($teams->count() > 0) {
    echo "  Teams:\n";
    foreach ($teams as $team) {
      echo "  - ID: {$team->id}, Name: {$team->team_name}\n";
    }
  } else {
    echo "  No active teams for this type\n";
  }
  echo "\n";
}

// 2. Get an existing outage for testing
echo "Finding an outage for testing...\n";
$outage = \Modules\Outages\Models\Outage::first();

if (!$outage) {
  echo "No outages found in the database. Test cannot continue.\n";
  exit();
}

echo "Selected Outage: #{$outage->ticket_number} (ID: {$outage->id})\n";
echo 'Current assigned_team_id: ' . ($outage->assigned_team_id ?? 'NULL') . "\n";

if ($outage->assignedTeam) {
  echo "Current assigned team: {$outage->assignedTeam->team_name}\n";
  echo "Current team type: {$outage->assignedTeam->teamType->type_name}\n";
} else {
  echo "No team currently assigned\n";
}

// 3. Select a team type for testing
if ($teamTypes->count() == 0) {
  echo "No active team types found. Test cannot continue.\n";
  exit();
}

$testTeamType = $teamTypes->first();
echo "\nSelecting team type for test: {$testTeamType->type_name} (ID: {$testTeamType->id})\n";

// 4. Find an active team for this team type
$testTeam = \App\Models\Team::where('team_type_id', $testTeamType->id)
  ->where('status', 'Active')
  ->first();

if (!$testTeam) {
  echo "No active teams found for team type {$testTeamType->type_name}. Creating one for testing...\n";

  // Create a test team
  $testTeam = new \App\Models\Team();
  $testTeam->team_type_id = $testTeamType->id;
  $testTeam->team_name = "Test Team for {$testTeamType->type_name}";
  $testTeam->description = 'Auto-created test team';
  $testTeam->status = 'Active';
  $testTeam->created_by = 1; // Assuming user ID 1 exists
  $testTeam->save();

  echo "Created test team: {$testTeam->team_name} (ID: {$testTeam->id})\n";
} else {
  echo "Found existing team: {$testTeam->team_name} (ID: {$testTeam->id})\n";
}

// 5. Test updating the outage with the selected team type
echo "\nUpdating outage #{$outage->ticket_number} with team type ID: {$testTeamType->id}...\n";

try {
  // Start a database transaction
  \DB::beginTransaction();

  // Find the team based on team type
  $team = \App\Models\Team::where('team_type_id', $testTeamType->id)
    ->where('status', 'Active')
    ->first();

  if ($team) {
    $assignedTeamId = $team->id;
    echo "Found team for assignment: {$team->team_name} (ID: {$team->id})\n";
  } else {
    $assignedTeamId = null;
    echo "No active team found for team type ID: {$testTeamType->id}\n";
  }

  // Update the outage
  $outage->assigned_team_id = $assignedTeamId;
  $outage->save();

  // Commit the transaction
  \DB::commit();

  echo "Update successful!\n";
} catch (\Exception $e) {
  // Rollback the transaction if an error occurs
  \DB::rollBack();
  echo "Error updating outage: {$e->getMessage()}\n";
}

// 6. Verify the update
$updatedOutage = \Modules\Outages\Models\Outage::find($outage->id);
echo "\nVerifying update...\n";
echo 'Updated assigned_team_id: ' . ($updatedOutage->assigned_team_id ?? 'NULL') . "\n";

if ($updatedOutage->assignedTeam) {
  echo "Updated assigned team: {$updatedOutage->assignedTeam->team_name}\n";
  echo "Updated team type: {$updatedOutage->assignedTeam->teamType->type_name}\n";

  if ($updatedOutage->assigned_team_id == $testTeam->id) {
    echo "\n✅ TEST PASSED: Team assignment was successful!\n";
  } else {
    echo "\n❌ TEST FAILED: Team assignment did not match expected team ID\n";
    echo "Expected: {$testTeam->id}, Actual: {$updatedOutage->assigned_team_id}\n";
  }
} else {
  echo "No team assigned after update\n";
  echo "\n❌ TEST FAILED: Team assignment was not successful\n";
}

echo "\n===== Test Complete =====\n";
