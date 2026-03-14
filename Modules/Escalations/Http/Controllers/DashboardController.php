<?php

namespace Modules\Escalations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Escalations\Entities\Escalation;
use App\Models\SubDepartment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the escalation dashboard.
     */
    public function index()
    {
        // Get all active sub-departments (status = 1 means active)
        $subDepartments = SubDepartment::where('sub_department_status', 1)->get();
        
        $dashboardData = [];
        
        foreach ($subDepartments as $subDepartment) {
            // Get metrics for this sub-department
            $metrics = $this->getSubDepartmentMetrics($subDepartment->id);
            $dashboardData[$subDepartment->id] = [
                'name' => $subDepartment->sub_department_name,
                'metrics' => $metrics
            ];
        }
        
        // Get overall metrics
        $overallMetrics = $this->getOverallMetrics();
        
        return view('escalations::dashboard.index', compact('dashboardData', 'overallMetrics', 'subDepartments'));
    }
    
    /**
     * Get metrics for a specific sub-department.
     */
    private function getSubDepartmentMetrics($subDepartmentId)
    {
        // Today's metrics
        $todayReceived = Escalation::bySubDepartment($subDepartmentId)->createdToday()->count();
        $todayClosed = Escalation::bySubDepartment($subDepartmentId)->closed()->whereDate('closed_at', now()->toDateString())->count();
        $todayClosedWithinSla = Escalation::bySubDepartment($subDepartmentId)->closedWithinSla()->whereDate('closed_at', now()->toDateString())->count();
        $todayClosedOutsideSla = Escalation::bySubDepartment($subDepartmentId)->closedOutsideSla()->whereDate('closed_at', now()->toDateString())->count();
        
        // Calculate backlog (open escalations from previous days)
        $backlog = Escalation::bySubDepartment($subDepartmentId)->open()->whereDate('created_at', '<', now()->toDateString())->count();
        
        // Calculate SLA compliance percentage
        $slaCompliancePercentage = $todayClosed > 0 
            ? round(($todayClosedWithinSla / $todayClosed) * 100, 2) 
            : 0;
        
        return [
            'today_received' => $todayReceived,
            'today_closed' => $todayClosed,
            'today_closed_within_sla' => $todayClosedWithinSla,
            'today_closed_outside_sla' => $todayClosedOutsideSla,
            'backlog' => $backlog,
            'sla_compliance_percentage' => $slaCompliancePercentage
        ];
    }
    
    /**
     * Get overall metrics across all sub-departments.
     */
    private function getOverallMetrics()
    {
        // Today's metrics
        $todayReceived = Escalation::createdToday()->count();
        $todayClosed = Escalation::closed()->whereDate('closed_at', now()->toDateString())->count();
        $todayClosedWithinSla = Escalation::closedWithinSla()->whereDate('closed_at', now()->toDateString())->count();
        $todayClosedOutsideSla = Escalation::closedOutsideSla()->whereDate('closed_at', now()->toDateString())->count();
        
        // Calculate backlog (open escalations from previous days)
        $backlog = Escalation::open()->whereDate('created_at', '<', now()->toDateString())->count();
        
        // Calculate SLA compliance percentage
        $slaCompliancePercentage = $todayClosed > 0 
            ? round(($todayClosedWithinSla / $todayClosed) * 100, 2) 
            : 0;
            
        // Get sub-department with highest backlog
        $highestBacklogDept = DB::table('escalations')
            ->select('sub_department_id', DB::raw('COUNT(*) as backlog_count'))
            ->whereNotIn('status', ['Scheduled-Closed', 'Escalated-Closed'])
            ->whereDate('created_at', '<', now()->toDateString())
            ->groupBy('sub_department_id')
            ->orderByDesc('backlog_count')
            ->first();
            
        $highestBacklogDeptName = null;
        if ($highestBacklogDept) {
            $subDept = SubDepartment::find($highestBacklogDept->sub_department_id);
            $highestBacklogDeptName = $subDept ? $subDept->sub_department_name : 'Unknown';
        }
        
        return [
            'today_received' => $todayReceived,
            'today_closed' => $todayClosed,
            'today_closed_within_sla' => $todayClosedWithinSla,
            'today_closed_outside_sla' => $todayClosedOutsideSla,
            'backlog' => $backlog,
            'sla_compliance_percentage' => $slaCompliancePercentage,
            'highest_backlog_dept' => $highestBacklogDeptName
        ];
    }
    
    /**
     * Display detailed metrics for a specific sub-department.
     */
    public function subDepartmentDetails($id)
    {
        $subDepartment = SubDepartment::findOrFail($id);
        
        // Get today's metrics
        $metrics = $this->getSubDepartmentMetrics($id);
        
        // Get open escalations for this sub-department
        $openEscalations = Escalation::bySubDepartment($id)
            ->open()
            ->with(['creator', 'assignedUser'])
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Get recently closed escalations
        $closedEscalations = Escalation::bySubDepartment($id)
            ->closed()
            ->with(['creator', 'closer'])
            ->orderBy('closed_at', 'desc')
            ->limit(10)
            ->get();
            
        return view('escalations::dashboard.sub_department_details', compact(
            'subDepartment', 
            'metrics', 
            'openEscalations', 
            'closedEscalations'
        ));
    }
}
