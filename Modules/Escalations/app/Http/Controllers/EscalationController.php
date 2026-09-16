<?php

namespace Modules\Escalations\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Escalations\App\Models\Escalation;
use Modules\Escalations\App\Models\EscalationHistory;
use App\Http\Controllers\Controller;
use Modules\Escalations\App\Services\EscalationHistoryService;
use Modules\Escalations\Services\NotificationService;

class EscalationController extends Controller
{
    protected $historyService;
    protected $notificationService;

    public function __construct(EscalationHistoryService $historyService, NotificationService $notificationService)
    {
        $this->historyService = $historyService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display the escalation history.
     *
     * @param  int  $escalationId
     * @return \Illuminate\Http\Response
     */
    public function history($escalationId)
    {
        $escalation = Escalation::with([
            'history', 
            'history.actionBy', 
            'history.assignedTo', 
            'history.subDepartment',
            'subDepartment'  // Add this line to load the escalation's subDepartment
        ])->findOrFail($escalationId);

        return view('escalations::escalations.history.index', [
            'escalation' => $escalation,
            'history' => $escalation->history
        ]);
    }

    /**
     * Update the status of an escalation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $escalationId
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $escalationId)
    {
        $request->validate([
            'status' => 'required|in:assigned,in_progress,resolved,closed,reopened',
            'notes' => 'nullable|string|max:1000',
        ]);

        $escalation = Escalation::findOrFail($escalationId);
        $oldStatus = $escalation->status;
        $escalation->status = $request->status;
        $escalation->edited_by = Auth::id();
        $escalation->save();
        
        // Log the status change
        $this->historyService->logStatusChange(
            $escalation, 
            $request->status, 
            $request->notes
        );
        
        // Create direct toast notification for status update
        $editor = \App\Models\User::find(Auth::id());
        $editorName = $editor ? $editor->name : 'Unknown user';
        $toastMessage = "{$editorName} just updated status of ticket {$escalation->ticket_id} to {$request->status}";
        $this->notificationService->setToastNotification('info', $toastMessage);
        
        // Also create database notification
        $this->notificationService->notifyUpdate($escalation, ['status' => [$oldStatus, $request->status]]);

        return response()->json([
            'message' => 'Status updated successfully',
            'history' => $escalation->history()->latest()->first()
        ]);
    }

    /**
     * Assign an escalation to a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $escalationId
     * @return \Illuminate\Http\Response
     */
    public function assign(Request $request, $escalationId)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $escalation = Escalation::findOrFail($escalationId);
        
        // Update the assignment
        $oldAssignedTo = $escalation->assigned_to;
        $escalation->assigned_to = $request->assigned_to;
        $escalation->status = 'assigned';
        $escalation->save();
        
        // Log the assignment
        $this->historyService->logAssignment(
            $escalation, 
            $request->assigned_to, 
            $request->notes
        );
        
        // Create notification for assignment
        $this->notificationService->notifyUpdate($escalation, ['assigned_to' => [$oldAssignedTo, $request->assigned_to]]);

        return response()->json([
            'message' => 'Escalation assigned successfully',
            'history' => $escalation->history()->latest()->first()
        ]);
    }

    /**
     * Change the department of an escalation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $escalationId
     * @return \Illuminate\Http\Response
     */
    public function changeDepartment(Request $request, $escalationId)
    {
        $request->validate([
            'sub_department_id' => 'required|exists:departments,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $escalation = Escalation::findOrFail($escalationId);
        
        // Update the department
        $oldDepartment = $escalation->sub_department_id;
        $escalation->sub_department_id = $request->sub_department_id;
        $escalation->save();
        
        // Log the department change
        $this->historyService->logDepartmentChange(
            $escalation, 
            $request->sub_department_id, 
            $request->notes
        );
        
        // Create notification for department change
        $this->notificationService->notifyUpdate($escalation, ['sub_department_id' => [$oldDepartment, $request->sub_department_id]]);

        return response()->json([
            'message' => 'Department changed successfully',
            'history' => $escalation->history()->latest()->first()
        ]);
    }

    /**
     * Add a note to the escalation history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $escalationId
     * @return \Illuminate\Http\Response
     */
    public function addNote(Request $request, $escalationId)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
            'action_description' => 'nullable|string|max:255',
        ]);

        $escalation = Escalation::findOrFail($escalationId);
        
        // Add a note to the history
        $history = $this->historyService->logCustomAction(
            $escalation,
            $request->action_description ?? 'Note added',
            $request->note
        );
        
        // Create notification for note addition
        $this->notificationService->notifyUpdate($escalation, ['note' => ['', $request->note]]);

        return response()->json([
            'message' => 'Note added successfully',
            'history' => $history
        ]);
    }
}
