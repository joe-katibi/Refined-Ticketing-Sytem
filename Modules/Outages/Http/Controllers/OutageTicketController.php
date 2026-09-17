<?php

namespace Modules\Outages\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Outages\Models\Outage;
use Modules\Outages\Models\OutageProgress;
use Modules\Outages\Models\OutageTicket;

class OutageTicketController extends OutagesController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = OutageTicket::with(['outage', 'assignedTeam', 'assignee', 'reporter'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_team_id')) {
            $query->where('assigned_team_id', $request->assigned_team_id);
        }

        if ($request->filled('outage_id')) {
            $query->where('outage_id', $request->outage_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15);

        // Get filter options
        $teams = Team::where('status', 'Active')->get();
        $outages = Outage::where('status', '!=', 'Closed')->get();
        $statuses = ['Open', 'In Progress', 'On Hold', 'Resolved', 'Closed'];
        $priorities = ['Low', 'Medium', 'High', 'Critical'];

        return view($this->view('tickets.index'), compact('tickets', 'teams', 'outages', 'statuses', 'priorities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $outageId = $request->get('outage_id');
        $outage = $outageId ? Outage::find($outageId) : null;

        $outages = Outage::where('status', '!=', 'Closed')->get();
        $teams = Team::where('status', 'Active')->get();
        $users = User::where('user_status', 1)->get();
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $impacts = ['Low', 'Medium', 'High', 'Critical'];
        $urgencies = ['Low', 'Medium', 'High', 'Critical'];

        return view($this->view('tickets.create'), compact(
            'outage', 'outages', 'teams', 'users', 'priorities', 'impacts', 'urgencies'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'outage_id' => 'required|exists:outages,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'impact' => 'required|in:Low,Medium,High,Critical',
            'urgency' => 'required|in:Low,Medium,High,Critical',
            'start_time' => 'required|date',
            'assigned_team_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Generated BEFORE the transaction below on purpose — see the
        // identical fix/comment on Outage::generateTicketNumber()'s caller
        // in OutageController::store().
        $ticketNumber = OutageTicket::generateTicketNumber();

        DB::beginTransaction();
        try {
            $ticket = OutageTicket::create([
                'outage_id' => $request->outage_id,
                'ticket_number' => $ticketNumber,
                'title' => $request->title,
                'description' => $request->description,
                'status' => 'Open',
                'priority' => $request->priority,
                'impact' => $request->impact,
                'urgency' => $request->urgency,
                'start_time' => $request->start_time,
                'assigned_team_id' => $request->assigned_team_id,
                'assigned_to' => $request->assigned_to,
                'reported_by' => Auth::id(),
                'created_by' => Auth::id(),
            ]);

            // Create initial progress entry
            OutageProgress::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'status' => 'Open',
                'notes' => 'Ticket created and opened',
                'is_major_update' => true,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('outage-tickets.show', $ticket)
                ->with('success', 'Ticket created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Failed to create ticket: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OutageTicket $outageTicket)
    {
        $outageTicket->load([
            'outage', 'assignedTeam', 'assignee', 'reporter',
            'progress.user', 'attachments.uploader', 'reasons.resolver',
        ]);

        $teams = Team::where('status', 'Active')->get();
        $users = User::where('user_status', 1)->get();

        return view($this->view('tickets.show'), compact('outageTicket', 'teams', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OutageTicket $outageTicket)
    {
        $outages = Outage::where('status', '!=', 'Closed')->get();
        $teams = Team::where('status', 'Active')->get();
        $users = User::where('user_status', 1)->get();
        $priorities = ['Low', 'Medium', 'High', 'Critical'];
        $impacts = ['Low', 'Medium', 'High', 'Critical'];
        $urgencies = ['Low', 'Medium', 'High', 'Critical'];
        $statuses = ['Open', 'In Progress', 'On Hold', 'Resolved', 'Closed'];

        return view($this->view('tickets.edit'), compact(
            'outageTicket', 'outages', 'teams', 'users', 'priorities',
            'impacts', 'urgencies', 'statuses'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OutageTicket $outageTicket)
    {
        $request->validate([
            'outage_id' => 'required|exists:outages,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Open,In Progress,On Hold,Resolved,Closed',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'impact' => 'required|in:Low,Medium,High,Critical',
            'urgency' => 'required|in:Low,Medium,High,Critical',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'resolution' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
            'assigned_team_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $outageTicket->status;
            $statusChanged = $oldStatus !== $request->status;

            $outageTicket->update([
                'outage_id' => $request->outage_id,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'priority' => $request->priority,
                'impact' => $request->impact,
                'urgency' => $request->urgency,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'resolution' => $request->resolution,
                'resolution_notes' => $request->resolution_notes,
                'assigned_team_id' => $request->assigned_team_id,
                'assigned_to' => $request->assigned_to,
                'updated_by' => Auth::id(),
            ]);

            // Create progress entry if status changed
            if ($statusChanged) {
                OutageProgress::create([
                    'ticket_id' => $outageTicket->id,
                    'user_id' => Auth::id(),
                    'status' => $request->status,
                    'notes' => "Status changed from {$oldStatus} to {$request->status}",
                    'is_major_update' => true,
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('outage-tickets.show', $outageTicket)
                ->with('success', 'Ticket updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Failed to update ticket: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OutageTicket $outageTicket)
    {
        try {
            $outageTicket->delete();

            return redirect()->route('outage-tickets.index')
                ->with('success', 'Ticket deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete ticket: '.$e->getMessage());
        }
    }

    /**
     * Update the progress of the specified ticket.
     */
    public function updateProgress(Request $request, OutageTicket $outageTicket)
    {
        $request->validate([
            'status' => 'nullable|in:Open,In Progress,On Hold,Resolved,Closed',
            'notes' => 'required|string',
            'action_taken' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'is_major_update' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update ticket status if provided
            if ($request->filled('status') && $request->status !== $outageTicket->status) {
                $outageTicket->update([
                    'status' => $request->status,
                    'end_time' => in_array($request->status, ['Resolved', 'Closed']) ? now() : $outageTicket->end_time,
                    'updated_by' => Auth::id(),
                ]);
            }

            // Create progress entry
            OutageProgress::create([
                'ticket_id' => $outageTicket->id,
                'user_id' => Auth::id(),
                'status' => $request->status ?? $outageTicket->status,
                'notes' => $request->notes,
                'action_taken' => $request->action_taken,
                'next_steps' => $request->next_steps,
                'is_major_update' => $request->boolean('is_major_update', false),
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress: '.$e->getMessage(),
            ], 500);
        }
    }
}
