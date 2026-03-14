<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check teams in the database
$teams = \App\Models\Team::all();
echo "All Teams:\n";
echo "==========\n";
foreach ($teams as $team) {
  echo "ID: {$team->id}, Name: {$team->team_name}, Type ID: {$team->team_type_id}, Status: {$team->status}\n";
}

echo "\n\nActive Teams by Type:\n";
echo "===================\n";
$teamTypes = \App\Models\TeamType::all();
foreach ($teamTypes as $type) {
  echo "Team Type: {$type->name} (ID: {$type->id})\n";

  $activeTeams = \App\Models\Team::where('team_type_id', $type->id)
    ->where('status', 'Active')
    ->get();

  if ($activeTeams->count() > 0) {
    foreach ($activeTeams as $team) {
      echo "  - Team: {$team->team_name} (ID: {$team->id})\n";
    }
  } else {
    echo "  - No active teams for this type\n";
  }
  echo "\n";
}

// Check outages with assigned teams
echo "\nOutages with Assigned Teams:\n";
echo "==========================\n";
$outages = \Modules\Outages\Models\Outage::whereNotNull('assigned_team_id')->get();
foreach ($outages as $outage) {
  echo "Outage #{$outage->ticket_number} (ID: {$outage->id})\n";
  echo "  - Assigned Team ID: {$outage->assigned_team_id}\n";

  if ($outage->assignedTeam) {
    echo "  - Team Name: {$outage->assignedTeam->team_name}\n";
    echo "  - Team Type ID: {$outage->assignedTeam->team_type_id}\n";

    $teamType = \App\Models\TeamType::find($outage->assignedTeam->team_type_id);
    if ($teamType) {
      echo "  - Team Type Name: {$teamType->name}\n";
    }
  } else {
    echo "  - No team found for this ID\n";
  }
  echo "\n";
}
