<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Modules\Appointment\Exports\AppointmentReportExport;
use Modules\Appointment\Models\Appointment;

// Set up the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Appointment Reports Test Script ===\n\n";

// Test date range
$startDate = now()
  ->subDays(30)
  ->format('Y-m-d');
$endDate = now()->format('Y-m-d');

echo "Testing date range: {$startDate} to {$endDate}\n\n";

// Test 1: Check if appointments exist in the date range
$appointmentCount = Appointment::whereBetween('created_at', [$startDate, $endDate])->count();
echo "1. Appointments in date range: {$appointmentCount}\n";

// Test 2: Check SLA data
$slaData = DB::table('appointments')
  ->select(
    DB::raw('DATE(created_at) as date'),
    DB::raw('COUNT(*) as total'),
    DB::raw('SUM(CASE WHEN status IN ("Completed", "Closed") THEN 1 ELSE 0 END) as closed'),
    DB::raw(
      'SUM(CASE WHEN status IN ("Completed", "Closed") AND TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2 THEN 1 ELSE 0 END) as within_sla'
    ),
    DB::raw(
      'SUM(CASE WHEN status IN ("Completed", "Closed") AND TIMESTAMPDIFF(HOUR, created_at, completed_date) > 2 THEN 1 ELSE 0 END) as outside_sla'
    )
  )
  ->whereBetween('created_at', [$startDate, $endDate])
  ->groupBy(DB::raw('DATE(created_at)'))
  ->orderBy('date')
  ->get();

echo '2. SLA data count: ' . count($slaData) . "\n";
if (count($slaData) > 0) {
  echo '   Sample SLA data for first date: ' . json_encode($slaData[0]) . "\n";
}

// Test 3: Check team productivity data
$teamData = DB::table('appointments')
  ->join('operational_teams', 'appointments.assigned_team_id', '=', 'operational_teams.id')
  ->select(
    'operational_teams.team_name',
    DB::raw('COUNT(*) as total_assigned'),
    DB::raw('SUM(CASE WHEN appointments.status IN ("Completed", "Closed") THEN 1 ELSE 0 END) as total_closed'),
    DB::raw(
      'SUM(CASE WHEN appointments.status IN ("Completed", "Closed") AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) <= 2 THEN 1 ELSE 0 END) as closed_within_sla'
    ),
    DB::raw(
      'SUM(CASE WHEN appointments.status IN ("Completed", "Closed") AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) > 2 THEN 1 ELSE 0 END) as closed_outside_sla'
    )
  )
  ->whereBetween('appointments.created_at', [$startDate, $endDate])
  ->whereNotNull('appointments.assigned_team_id')
  ->groupBy('operational_teams.id', 'operational_teams.team_name')
  ->orderByDesc('total_closed')
  ->get();

echo '3. Team productivity data count: ' . count($teamData) . "\n";
if (count($teamData) > 0) {
  echo '   Sample team data: ' . json_encode($teamData[0]) . "\n";
}

// Test 4: Check sub team productivity data
$subTeamData = DB::table('appointments')
  ->join('sub_team_types', 'appointments.sub_team_type_id', '=', 'sub_team_types.id')
  ->select(
    'sub_team_types.sub_type_name',
    DB::raw('COUNT(*) as total_assigned'),
    DB::raw('SUM(CASE WHEN appointments.status IN ("Completed", "Closed") THEN 1 ELSE 0 END) as total_closed')
  )
  ->whereBetween('appointments.created_at', [$startDate, $endDate])
  ->whereNotNull('appointments.sub_team_type_id')
  ->groupBy('sub_team_types.id', 'sub_team_types.sub_type_name')
  ->orderByDesc('total_closed')
  ->get();

echo '4. Sub team productivity data count: ' . count($subTeamData) . "\n";
if (count($subTeamData) > 0) {
  echo '   Sample sub team data: ' . json_encode($subTeamData[0]) . "\n";
}

// Test 5: Check assigned team productivity data
$assignedTeamData = DB::table('appointments')
  ->join('users', 'appointments.closed_by', '=', 'users.id')
  ->select(
    'users.name',
    DB::raw('COUNT(*) as total_assigned'),
    DB::raw(
      'SUM(CASE WHEN appointments.status IN ("Completed", "Closed", "Scheduled-Closed") THEN 1 ELSE 0 END) as total_closed'
    )
  )
  ->whereBetween('appointments.created_at', [$startDate, $endDate])
  ->whereNotNull('appointments.closed_by')
  ->whereIn('appointments.status', ['Completed', 'Closed', 'Scheduled-Closed'])
  ->groupBy('users.id', 'users.name')
  ->orderByDesc('total_closed')
  ->get();

echo '5. Assigned team productivity data count: ' . count($assignedTeamData) . "\n";
if (count($assignedTeamData) > 0) {
  echo '   Sample assigned team data: ' . json_encode($assignedTeamData[0]) . "\n";
}

// Test 6: Check final reason data
$finalReasonData = DB::table('appointments')
  ->join('appointment_final_reasons', 'appointments.final_reason_id', '=', 'appointment_final_reasons.id')
  ->select('appointment_final_reasons.final_reason_name', DB::raw('COUNT(*) as total_appointments'))
  ->whereBetween('appointments.created_at', [$startDate, $endDate])
  ->whereNotNull('appointments.final_reason_id')
  ->groupBy('appointment_final_reasons.id', 'appointment_final_reasons.final_reason_name')
  ->orderByDesc('total_appointments')
  ->get();

echo '6. Final reason data count: ' . count($finalReasonData) . "\n";
if (count($finalReasonData) > 0) {
  echo '   Sample final reason data: ' . json_encode($finalReasonData[0]) . "\n";
}

// Test 7: Test Excel export class
try {
  echo "\n7. Testing Excel export class:\n";
  $exportTypes = [
    'sla',
    'team_productivity',
    'sub_team_productivity',
    'assigned_team_productivity',
    'final_reason',
    'appointments',
  ];

  foreach ($exportTypes as $type) {
    $export = new AppointmentReportExport($startDate, $endDate, $type);
    $data = $export->collection();
    echo "   - {$type}: " . count($data) . " records found\n";
  }

  echo "   Excel export class tests passed!\n";
} catch (Exception $e) {
  echo '   Error testing Excel export: ' . $e->getMessage() . "\n";
}

echo "\n=== Test completed ===\n";
