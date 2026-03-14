<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Outages\Models\Outage;
use Modules\Escalations\App\Models\Escalation;
use Modules\Appointment\Models\Appointment;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        
        // Escalations Statistics
        $escalationsTotal = Escalation::count();
        $escalationsOpen = Escalation::whereIn('status', ['Open', 'In Progress', 'Pending'])->count();
        $escalationsClosed = Escalation::where('status', 'Closed')->count();
        $escalationsHigh = Escalation::where('priority', 'High')->count();
        $escalationsCritical = Escalation::where('priority', 'Critical')->count();
        $escalationsToday = Escalation::whereDate('created_at', today())->count();
        $escalationsThisWeek = Escalation::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        
        // Appointments Statistics
        $appointmentsTotal = Appointment::count();
        $appointmentsScheduled = Appointment::where('status', 'Scheduled')->count();
        $appointmentsCompleted = Appointment::where('status', 'Completed')->count();
        $appointmentsCancelled = Appointment::where('status', 'Cancelled')->count();
        $appointmentsToday = Appointment::whereDate('scheduled_date', today())->count();
        $appointmentsThisWeek = Appointment::whereBetween('scheduled_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $appointmentsPending = Appointment::whereIn('status', ['Scheduled', 'In Progress'])->count();
        
        // Outages Statistics
        $outagesTotal = Outage::count();
        $outagesActive = Outage::whereNotIn('status', ['infra-resolved', 'noc-restore-confirmed', 'support-closed'])->count();
        $outagesResolved = Outage::whereIn('status', ['infra-resolved', 'noc-restore-confirmed', 'support-closed'])->count();
        $outagesCritical = Outage::where('priority', 'Critical')->count();
        $outagesHigh = Outage::where('priority', 'High')->count();
        $outagesEmergency = Outage::where('ticket_type', 'emergency')->count();
        $outagesPlanned = Outage::where('ticket_type', 'planned_maintenance')->count();
        $outagesUnplanned = Outage::where('ticket_type', 'regular')->count();
        $outagesAssignedToUser = Outage::where('assigned_to', $user->id)->whereNotIn('status', ['infra-resolved', 'noc-restore-confirmed'])->count();
        $outagesTeamAssigned = $user->team_id ? Outage::where('assigned_team_id', $user->team_id)->whereNotIn('status', ['infra-resolved', 'noc-restore-confirmed'])->count() : 0;
        
        // Recent Activity (Last 7 days)
        $recentEscalations = Escalation::where('created_at', '>=', now()->subDays(7))->orderBy('created_at', 'desc')->limit(5)->get();
        $recentAppointments = Appointment::where('created_at', '>=', now()->subDays(7))->orderBy('created_at', 'desc')->limit(5)->get();
        $recentOutages = Outage::where('created_at', '>=', now()->subDays(7))->orderBy('created_at', 'desc')->limit(5)->get();
        
        // Monthly trends (last 6 months)
        $monthlyData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyData->push([
                'month' => $date->format('M Y'),
                'escalations' => Escalation::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'appointments' => Appointment::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'outages' => Outage::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ]);
        }

        return view('home', compact(
            // Escalations data
            'escalationsTotal', 'escalationsOpen', 'escalationsClosed', 'escalationsHigh', 'escalationsCritical',
            'escalationsToday', 'escalationsThisWeek',
            
            // Appointments data
            'appointmentsTotal', 'appointmentsScheduled', 'appointmentsCompleted', 'appointmentsCancelled',
            'appointmentsToday', 'appointmentsThisWeek', 'appointmentsPending',
            
            // Outages data
            'outagesTotal', 'outagesActive', 'outagesResolved', 'outagesCritical', 'outagesHigh',
            'outagesEmergency', 'outagesPlanned', 'outagesUnplanned', 'outagesAssignedToUser', 'outagesTeamAssigned',
            
            // Recent activity
            'recentEscalations', 'recentAppointments', 'recentOutages',
            
            // Monthly trends
            'monthlyData'
        ));

        $nowTime = Carbon::now();
        $currentYear = $nowTime->year;
        $previousYear = $currentYear - 1;

        $monthData = collect([
            $nowTime->copy()->subMonths(3),
            $nowTime->copy()->subMonths(2),
            $nowTime->copy()->subMonths(1),
            $nowTime->copy()
        ])->map(function ($date) {
            return [
                'month' => $date->shortMonthName,
                'year' => $date->year
            ];
        });

        $currentMonthNames = $monthData->pluck('month')->toArray();
        $monthYears = $monthData->pluck('year')->toArray();
        $currentWeek = $nowTime->week;

        $now = Carbon::now();

        $startOf3rdMonth = (clone $now)->subMonths(3)->startOfMonth()->toDateTimeString();
        $endOf3rdMonth = (clone $now)->subMonths(3)->endOfMonth()->toDateTimeString();

        $startOf2ndMonth = (clone $now)->subMonths(2)->startOfMonth()->toDateTimeString();
        $endOf2ndMonth = (clone $now)->subMonths(2)->endOfMonth()->toDateTimeString();

        $startOf1stMonth = (clone $now)->subMonth()->startOfMonth()->toDateTimeString();
        $endOf1stMonth = (clone $now)->subMonth()->endOfMonth()->toDateTimeString();
                                    $startOfCurrentMonth = (clone $now)->startOfMonth()->toDateTimeString();
                                    $endOfCurrentMonth = (clone $now)->endOfMonth()->toDateTimeString();

                                    $startOfCurrentWeek = (clone $now)->startOfWeek()->toDateTimeString();
                                    $endOfCurrentWeek = (clone $now)->endOfWeek()->toDateTimeString();

          $categoryAuditQuery = AuditResult::select(
                                        'services.id as service_id',
                                        'services.service_name',
                                        'countries.id as country_id',
                                        'countries.country_name',
                                        'categories.id as category_id',
                                        'categories.category_name',
                                        DB::raw("COALESCE(AVG(CASE WHEN audit_results.created_at BETWEEN '$startOf3rdMonth' AND '$endOf3rdMonth' THEN audit_results.final_results ELSE NULL END), NULL) as average_3rd_month"),
                                        DB::raw("COALESCE(AVG(CASE WHEN audit_results.created_at BETWEEN '$startOf2ndMonth' AND '$endOf2ndMonth' THEN audit_results.final_results ELSE NULL END), NULL) as average_2nd_month"),
                                        DB::raw("COALESCE(AVG(CASE WHEN audit_results.created_at BETWEEN '$startOf1stMonth' AND '$endOf1stMonth' THEN audit_results.final_results ELSE NULL END), NULL) as average_1st_month"),
                                        DB::raw("COALESCE(AVG(CASE WHEN audit_results.created_at BETWEEN '$startOfCurrentMonth' AND '$endOfCurrentMonth' THEN audit_results.final_results ELSE NULL END), NULL) as average_current_month"),
                                        DB::raw("COALESCE(AVG(CASE WHEN audit_results.created_at BETWEEN '$startOfCurrentWeek' AND '$endOfCurrentWeek' THEN audit_results.final_results ELSE NULL END), NULL) as average_current_week")
                                    )
                                    ->join('categories', 'categories.id', '=', 'audit_results.category_id')
                                    ->join('user_categories', 'user_categories.category_id', '=', 'audit_results.category_id')
                                    ->join('users', 'users.id', '=', 'user_categories.user_id')
                                    ->join('countries', 'countries.id', '=', 'users.country')
                                    ->join('services', 'services.id', '=', 'users.services')
                                    ->groupBy(
                                        'services.id',
                                        'services.service_name',
                                        'countries.id',
                                        'countries.country_name',
                                        'categories.id',
                                        'categories.category_name'
                                    )
                                    ->paginate(20);
                                    $categoryExamQuery = ExamResult::select(
                                      'services.id as service_id',
                                      'services.service_name',
                                      'countries.id as country_id',
                                      'countries.country_name',
                                      'categories.id as category_id',
                                      'categories.category_name',
                                      DB::raw("COALESCE(SUM(exam_results.marks_achieved), 0) as total_marks_archived"),
                                      DB::raw("COALESCE((
                                          SELECT COALESCE(SUM(weight), 0)
                                          FROM answers
                                          WHERE answers.question_id IN (
                                              SELECT question_id
                                              FROM schedule_exams
                                              WHERE schedule_exams.id = exam_results.conduct_id
                                          )
                                      ), 0) as total_marks"),
                                      DB::raw("CASE
                                          WHEN exam_results.created_at BETWEEN '$startOf3rdMonth' AND '$endOf3rdMonth' THEN '3rd_month'
                                          WHEN exam_results.created_at BETWEEN '$startOf2ndMonth' AND '$endOf2ndMonth' THEN '2nd_month'
                                          WHEN exam_results.created_at BETWEEN '$startOf1stMonth' AND '$endOf1stMonth' THEN '1st_month'
                                          WHEN exam_results.created_at BETWEEN '$startOfCurrentWeek' AND '$endOfCurrentWeek' THEN 'current_week'
                                          ELSE 'current_month'
                                      END as time_period"),
                                      DB::raw("LEAST(COALESCE(
                                          ROUND(
                                              (SELECT COALESCE(SUM(marks_achieved), 0)
                                              FROM exam_results as er1
                                              WHERE er1.created_at BETWEEN '$startOf3rdMonth' AND '$endOf3rdMonth'
                                              AND er1.conduct_id = exam_results.conduct_id) * 100.0 /
                                              NULLIF((SELECT COALESCE(SUM(weight), 0)
                                              FROM answers
                                              WHERE answers.question_id IN (
                                                  SELECT question_id
                                                  FROM schedule_exams
                                                  WHERE schedule_exams.id = exam_results.conduct_id
                                              )), 0)
                                          , 2)
                                      , 0), 100) as correct_percentage_3rd_month"),
                                      DB::raw("LEAST(COALESCE(
                                          ROUND(
                                              (SELECT COALESCE(SUM(marks_achieved), 0)
                                              FROM exam_results as er2
                                              WHERE er2.created_at BETWEEN '$startOf2ndMonth' AND '$endOf2ndMonth'
                                              AND er2.conduct_id = exam_results.conduct_id) * 100.0 /
                                              NULLIF((SELECT COALESCE(SUM(weight), 0)
                                              FROM answers
                                              WHERE answers.question_id IN (
                                                  SELECT question_id
                                                  FROM schedule_exams
                                                  WHERE schedule_exams.id = exam_results.conduct_id
                                              )), 0)
                                          , 2)
                                      , 0), 100) as correct_percentage_2nd_month"),
                                      DB::raw("LEAST(COALESCE(
                                          ROUND(
                                              (SELECT COALESCE(SUM(marks_achieved), 0)
                                              FROM exam_results as er3
                                              WHERE er3.created_at BETWEEN '$startOf1stMonth' AND '$endOf1stMonth'
                                              AND er3.conduct_id = exam_results.conduct_id) * 100.0 /
                                              NULLIF((SELECT COALESCE(SUM(weight), 0)
                                              FROM answers
                                              WHERE answers.question_id IN (
                                                  SELECT question_id
                                                  FROM schedule_exams
                                                  WHERE schedule_exams.id = exam_results.conduct_id
                                              )), 0)
                                          , 2)
                                      , 0), 100) as correct_percentage_1st_month"),
                                      DB::raw("LEAST(COALESCE(
                                          ROUND(
                                              (SELECT COALESCE(SUM(marks_achieved), 0)
                                              FROM exam_results as er4
                                              WHERE er4.created_at BETWEEN '$startOfCurrentMonth' AND '$endOfCurrentMonth'
                                              AND er4.conduct_id = exam_results.conduct_id) * 100.0 /
                                              NULLIF((SELECT COALESCE(SUM(weight), 0)
                                              FROM answers
                                              WHERE answers.question_id IN (
                                                  SELECT question_id
                                                  FROM schedule_exams
                                                  WHERE schedule_exams.id = exam_results.conduct_id
                                              )), 0)
                                          , 2)
                                      , 0), 100) as correct_percentage_current_month"),
                                      DB::raw("LEAST(COALESCE(
                                          ROUND(
                                              (SELECT COALESCE(SUM(marks_achieved), 0)
                                              FROM exam_results as er5
                                              WHERE er5.created_at BETWEEN '$startOfCurrentWeek' AND '$endOfCurrentWeek'
                                              AND er5.conduct_id = exam_results.conduct_id) * 100.0 /
                                              NULLIF((SELECT COALESCE(SUM(weight), 0)
                                              FROM answers
                                              WHERE answers.question_id IN (
                                                  SELECT question_id
                                                  FROM schedule_exams
                                                  WHERE schedule_exams.id = exam_results.conduct_id
                                              )), 0)
                                          , 2)
                                      , 0), 100) as correct_percentage_current_week")
                                  )
                                  ->join('schedule_exams', 'schedule_exams.id', '=', 'exam_results.conduct_id')
                                  ->join('categories', 'categories.id', '=', 'schedule_exams.category_id')
                                  ->join('courses', 'courses.id', '=', 'schedule_exams.course_id')
                                  ->join('users', 'users.id', '=', 'exam_results.created_by')
                                  ->join('services', 'services.id', '=', 'schedule_exams.service_id')
                                  ->join('countries', 'countries.id', '=', 'users.country')
                                  ->groupBy(
                                      'services.id',
                                      'services.service_name',
                                      'countries.id',
                                      'countries.country_name',
                                      'categories.id',
                                      'categories.category_name',
                                      'exam_results.conduct_id',
                                      'exam_results.created_at'
                                  )
                                  ->paginate(20);


                                  $results = DB::table('audit_results')->get();

                                  $monthlyResults = $results->groupBy(function($result) {
                                    return \Carbon\Carbon::parse($result->created_at)->startOfMonth();
                                })->map(function($group) {
                                    return round($group->avg('final_results')); // Round the average to a whole number
                                });
                                

                                  $monthlyData = $monthlyResults->values();
                                  $monthlyLabels = $monthlyResults->keys()->map(function($date) {
                                   return Carbon::parse($date)->format('M-y');
                                   })->values();

                                 // Group the exam results by month
                               // First, get individual exam results with their percentages
        $monthlyResultsExams = ExamResult::selectRaw('
        DATE_FORMAT(exam_results.created_at, "%Y-%m") as month,
          exam_results.conduct_id,
          SUM(exam_results.marks_achieved) as exam_marks_achieved,
         (
              SELECT SUM(weight)
                FROM answers
                 WHERE answers.question_id IN (
                    SELECT question_id
                  FROM exam_banks
                    WHERE exam_banks.id = answers.question_id
                )
           ) as exam_total_weight
           ')
          ->groupBy('month', 'exam_results.conduct_id')
            ->orderBy('month')
           ->get();

              // Compute question weight uniquely per conduct_id
              $questionWeightsExam = ExamResult::selectRaw('
               exam_results.conduct_id,
                SUM(answers.weight) as total_question_weight
              ')
               ->join('answers', 'answers.question_id', '=', 'exam_results.question_id')
                  ->groupBy('exam_results.conduct_id')
                 ->pluck('total_question_weight', 'conduct_id');

           // Calculate individual percentages and group by month
           $monthlyDataExams = $monthlyResultsExams
           ->map(function ($result) {
               // Calculate percentage for each exam
                 $percentage = ($result->exam_total_weight > 0)
                            ? round(($result->exam_marks_achieved / $result->exam_total_weight) * 100)
                     : 0;

            return [
                  'month' => $result->month,
                 'conduct_id' => $result->conduct_id,
                   'marks_achieved' => $result->exam_marks_achieved,
                   'total_weight' => $result->exam_total_weight,
                  'percentage' => $percentage,
             ];
             })
              ->groupBy('month')
                        ->map(function ($monthGroup) { 
                  // Calculate monthly averages
                  $avgPercentage = $monthGroup->avg('percentage');
                $totalMarksAchieved = $monthGroup->sum('marks_achieved');
                 $totalWeight = $monthGroup->sum('total_weight');

                   return [
                      'month' => $monthGroup->first()['month'],
                   'questionWeight' => $totalWeight,
                    'marksAchieved' => $totalMarksAchieved,
                    'percentage' => round($avgPercentage),
                     'exam_count' => $monthGroup->count() // Optional: track number of exams per month
                  ];
              })
         ->values();

                    // Extract labels and values for the graph
                    $monthlyLabelsExams = $monthlyDataExams->pluck('month')->map(function ($month) {
                         return Carbon::parse($month . '-01')->format('M-y'); // Convert "2025-01" to "Jan-25"
                    });

                  $questionWeights = $monthlyDataExams->pluck('questionWeight');
                  $marksAchieved = $monthlyDataExams->pluck('marksAchieved');
                  $percentages = $monthlyDataExams->pluck('percentage');


                 // Get the current month in YYYY-MM format
                 $currentMonth = now()->format('Y-m');
    
                   // Get all records grouped by month and classification
                   $classifications = AuditResult::select(
                    DB::raw("DATE_FORMAT(audit_results.created_at, '%Y-%m') as month"),
                    DB::raw('COUNT(*) as total_rows'), // Count all rows per month
                    DB::raw('COUNT(DISTINCT audit_results.classification_id) as unique_classifications'), // Count unique classification_id per month
                    DB::raw('MIN(audit_results.created_at) as created_at'), // Optional: to keep a representative date per group
                    'general_issues.general_name' // Include the general_name for classifications
                )
                ->join('general_issues', 'general_issues.id', '=', 'audit_results.classification_id')
                ->groupBy('month', 'classification_id', 'general_issues.general_name') // Group by month and classification name
                ->orderBy('month')
                ->get();
                
                // Group by month and calculate percentages
                $monthlyDataClassification = $classifications->groupBy('month')->map(function ($monthGroup) {
                    $totalForMonth = $monthGroup->sum('total_rows'); // Sum total rows for the month
                
                    return $monthGroup->map(function ($item) use ($totalForMonth) {
                        return [
                            'name' => $item->general_name,
                            'count' => $item->total_rows, // Use total_rows as the count
                            'percentage' => round(($item->total_rows / $totalForMonth) * 100), // Calculate percentage
                        ];
                    });
                });
                
               // Get available months for the dropdown
               $availableMonths = $classifications->pluck('month')
               ->unique() // Get unique months
               ->sort() // Sort the months
               ->values() // Reset the keys
               ->map(function ($month) {
                   return [
                       'value' => $month,
                       'label' => Carbon::createFromFormat('Y-m', $month)->format('M-y') // Format the month
                   ];
               });
           

            // Get current month's data for initial chart
                $currentMonthData = $monthlyDataClassification[$currentMonth] ?? collect();

                // Get the current month in YYYY-MM format
                $currentMonthDisposition = now()->format('Y-m');

                       // Get all records grouped by month and disposition
                       $disposition = AuditResult::select(
                        DB::raw("DATE_FORMAT(audit_results.created_at, '%Y-%m') as month"),
                        DB::raw('COUNT(*) as total_rows'), // Count all rows per month
                        DB::raw('COUNT(DISTINCT audit_results.disposition_id) as unique_disposition'), // Count unique disposition_id per month
                        DB::raw('MIN(audit_results.created_at) as created_at'), // Optional: to keep a representative date per group
                        'dispositions.disposition_name' // Include the disposition_name for disposition
                    )
                    ->join('dispositions', 'dispositions.id', '=', 'audit_results.disposition_id')
                    ->groupBy('month', 'disposition_id', 'dispositions.disposition_name') // Group by month and classification name
                    ->orderBy('month')
                    ->get();

                   
                    
                    // Group by month and calculate percentages
                    $monthlyDataDisposition= $disposition->groupBy('month')->map(function ($monthGroup) {
                        $totalForMonth = $monthGroup->sum('total_rows'); // Sum total rows for the month
                    
                        return $monthGroup->map(function ($item) use ($totalForMonth) {
                            return [
                                'name' => $item->disposition_name,
                                'count' => $item->total_rows, // Use total_rows as the count
                                'percentage' => round(($item->total_rows / $totalForMonth) * 100), // Calculate percentage
                            ];
                        });
                    });
                   
                   // Get available months for the dropdown
                   $availableMonthsDisposition = $disposition->pluck('month')
                   ->unique() // Get unique months
                   ->sort() // Sort the months
                   ->values() // Reset the keys
                   ->map(function ($month) {
                       return [
                           'value' => $month,
                           'label' => Carbon::createFromFormat('Y-m', $month)->format('M-y') // Format the month
                       ];
                   });
               
                   
                // Get current month's data for initial chart
                    $currentMonthDataDisposition = $monthlyDataDisposition[$currentMonthDisposition] ?? collect();

                  

                         
      $data['auditSlipping'] = $auditSlipping;
      $data['auditPending'] = $auditPending;
      $data['auditCompleted'] = $auditCompleted;
      $data['auditTotal'] = $auditTotal;
      $data['examTotalDone'] = $examTotalDone;
      $data['courseTotal'] = $courseTotal;
      $data['totalExams'] = $totalExams;
      $data['totalQuestions'] = $totalQuestions;
      $data['coachingTotal'] = $coachingTotal;
      $data['coachingCompleted'] = $coachingCompleted;
      $data['coachingPending'] = $coachingPending;
      $data['coachingSlipping'] = $coachingSlipping;
      $data['autoSlipping'] = $autoSlipping;
      $data['autoPending'] = $autoPending;
      $data['autoCompleted'] = $autoCompleted;
      $data['autoTotal'] = $autoTotal;
      $data['data'] = $categoryAuditQuery;
      $data['currentMonthNames'] = $currentMonthNames;
      $data['currentYear'] = $currentYear;
      $data['currentWeek'] = $currentWeek;
      $data['categoryExamQuery'] = $categoryExamQuery;
      $data['monthYears'] = $monthYears;
      $data['monthlyData'] = $monthlyData;
      $data['monthlyLabels'] = $monthlyLabels;
      $data['monthlyLabelsExams'] = $monthlyLabelsExams;
      $data['percentages'] = $percentages;
      $data['availableMonths'] = $availableMonths;
      $data['currentMonth'] = $currentMonth;
      $data['monthlyDataClassification'] = $monthlyDataClassification;
      $data['currentMonthData'] = $currentMonthData;
      $data['availableMonthsDisposition'] = $availableMonthsDisposition;
      $data['monthlyDataDisposition'] = $monthlyDataDisposition;
      $data['currentMonthDataDisposition'] = $currentMonthDataDisposition;
      $data['currentMonthDisposition'] = $currentMonthDisposition;
      

      



       return view('dashboard')->with($data);
    }
}
