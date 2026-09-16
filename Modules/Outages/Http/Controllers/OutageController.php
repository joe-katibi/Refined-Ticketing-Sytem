<?php

namespace Modules\Outages\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\OptimizedController;
use Modules\Outages\Models\Outage;
use Modules\Outages\Models\OutageActivity;
use Modules\Outages\Models\OutageProgress;
use Modules\Outages\Models\Olt;
use Modules\Outages\Models\OltSlot;
use Modules\Outages\Models\PonPort;
use Modules\Outages\Models\OutageFinalReason;
use Modules\Outages\Models\AffectedArea;
use Modules\Outages\Models\AffectedService;
use Modules\Outages\Services\OutageNotificationService;
use App\Models\TeamType;
use App\Models\Team;
use App\Models\SubTeamType;
use App\Models\User;

class OutageController extends OutagesController
{
  protected $notificationService;

  public function __construct(OutageNotificationService $notificationService)
  {
    parent::__construct();
    $this->notificationService = $notificationService;
  }
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = Outage::withOptimizedRelations(['assignedTeam', 'assignee', 'reporter', 'resolver'])->orderBy(
      'created_at',
      'desc'
    );

    // Apply optimized filters
    $filterableColumns = [
      'search' => ['title', 'ticket_number', 'description'],
      'status' => 'status',
      'ticket_type' => 'ticket_type',
      'priority' => 'priority',
      'assigned_team_id' => 'assigned_team_id',
      'olt_id' => 'olt_id',
    ];

    // Apply filters manually since we're not extending OptimizedController
    if ($request->filled('search')) {
      $searchTerm = $request->search;
      $query->where(function ($q) use ($searchTerm) {
        $q->where('title', 'like', "%{$searchTerm}%")
          ->orWhere('ticket_number', 'like', "%{$searchTerm}%")
          ->orWhere('description', 'like', "%{$searchTerm}%");
      });
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('ticket_type')) {
      $query->where('ticket_type', $request->ticket_type);
    }

    if ($request->filled('priority')) {
      $query->where('priority', $request->priority);
    }

    if ($request->filled('assigned_team_id')) {
      $query->where('assigned_team_id', $request->assigned_team_id);
    }

    if ($request->filled('olt_id')) {
      $query->where('olt_id', $request->olt_id);
    }

    // Get paginated results
    $outages = $query->paginate(15);

    // Cache filter options for better performance
    $teams = cache()->remember('active_teams', 1800, function () {
      return Team::where('status', 'Active')->get();
    });

    $olts = cache()->remember('active_olts', 1800, function () {
      return Olt::where('status', 'Active')
        ->orderBy('name')
        ->get();
    });

    $priorities = ['Low', 'Medium', 'High', 'Critical'];

    return view($this->view('index'), compact('outages', 'teams', 'olts', 'priorities'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $teamTypes = TeamType::where('status', 'Active')->get();
    $teams = Team::where('status', 'Active')->get();
    $users = User::where('user_status', 1)->get();
    $priorities = ['Low', 'Medium', 'High', 'Critical'];
    $impacts = ['Low', 'Medium', 'High', 'Critical'];
    $urgencies = ['Low', 'Medium', 'High', 'Critical'];

    // Get OLTs for cascading dropdown
    $olts = Olt::where('status', 'active')
      ->orderBy('name')
      ->get(['id', 'name', 'ip_address', 'location']);

    // Get active affected areas and services from database
    $areas = AffectedArea::where('area_status', 'Active')
      ->orderBy('area_name')
      ->get();

    $services = AffectedService::where('service_status', 'Active')
      ->orderBy('service_name')
      ->get();

    return view(
      $this->view('create'),
      compact('teamTypes', 'teams', 'users', 'priorities', 'impacts', 'urgencies', 'areas', 'services', 'olts')
    );
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    \Log::info('=== OUTAGE STORE FUNCTION START ===');
    \Log::info('Request data:', $request->all());

    try {
      $statusOptions = array_keys(Outage::getStatusOptions());
      $request->validate([
        'ticket_type' => 'required|in:regular,emergency,planned_maintenance',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'status' => 'required|in:' . implode(',', $statusOptions),
        'impact' => 'required|in:Low,Medium,High,Critical',
        'urgency' => 'required|in:Low,Medium,High,Critical',
        'total_customers_affected' => 'nullable|integer|min:0',
        'olt_id' => 'required|string|max:50',
        'slot_id' => 'required|string|max:50',
        'port_id' => 'required|string|max:50',
        'start_date' => 'required|date',
        'start_time' => 'required|date_format:H:i',
        'resolution_notes' => 'nullable|string',
        'affected_areas' => 'nullable|array',
        'affected_services' => 'nullable|array',
        'assigned_team_type' => 'nullable|exists:team_types,id',
        'assigned_sub_team_type_id' => 'nullable|exists:sub_team_types,id',
        'assigned_to' => 'nullable|exists:users,id',
      ]);
      \Log::info('Validation passed successfully');
    } catch (\Illuminate\Validation\ValidationException $e) {
      \Log::error('Validation failed:', $e->errors());
      throw $e;
    }

    // Combine date and time for start_time
    $startDateTime = $request->start_date . ' ' . $request->start_time;
    \Log::info('Combined start datetime:', ['startDateTime' => $startDateTime]);

    DB::beginTransaction();
    \Log::info('Database transaction started');

    try {
      $ticketNumber = Outage::generateTicketNumber($request->ticket_type);
      \Log::info('Generated ticket number:', ['ticket_number' => $ticketNumber]);

      $outageData = [
        'ticket_number' => $ticketNumber,
        'ticket_type' => $request->ticket_type,
        'title' => $request->title,
        'description' => $request->description,
        'status' => $request->status,
        'impact' => $request->impact,
        'urgency' => $request->urgency,
        'total_customers_affected' => $request->total_customers_affected,
        'olt_id' => $request->olt_id,
        'slot_id' => $request->slot_id,
        'port_id' => $request->port_id,
        'start_time' => $startDateTime,
        'resolution_notes' => $request->resolution_notes,
        'impacted_areas' => $request->affected_areas ?? [],
        'impacted_services' => $request->affected_services ?? [],
        // assigned_team_id is a FK to team_types (see Outage::teamType()), not
        // teams — the form's field is 'assigned_team_type', not 'assigned_team'
        // (which doesn't exist), and sub_team_type_id was never read at all,
        // so a freshly created outage always showed "Not assigned" for both
        // Team Type and Sub Team Type even when the creator selected them.
        'assigned_team_id' => $request->assigned_team_type,
        'sub_team_type_id' => $request->assigned_sub_team_type_id,
        'assigned_to' => $request->assigned_to,
        'reported_by' => Auth::id(),
        'created_by' => Auth::id(),
      ];

      \Log::info('Outage data to be created:', $outageData);

      $outage = Outage::create($outageData);
      \Log::info('Outage created successfully:', [
        'outage_id' => $outage->id,
        'ticket_number' => $outage->ticket_number,
      ]);

      // The show page reads impacted areas/services from the affectedAreas()/
      // affectedServices() pivot relationships (as does update()), not from
      // the impacted_areas/impacted_services JSON columns set above — without
      // this sync, every newly created outage displayed "Not specified" even
      // when the creator selected areas/services.
      $outage->affectedAreas()->sync($request->affected_areas ?? []);
      $outage->affectedServices()->sync($request->affected_services ?? []);

      // Create initial progress entry
      \Log::info('Creating initial progress entry');
      $progressData = [
        'outage_id' => $outage->id,
        'user_id' => Auth::id(),
        'status' => $request->status,
        'notes' => 'Outage created with status: ' . Outage::getStatusOptions()[$request->status],
        'is_major_update' => true,
        'created_by' => Auth::id(),
      ];
      \Log::info('Progress data:', $progressData);

      $progress = OutageProgress::create($progressData);
      \Log::info('Progress created successfully:', ['progress_id' => $progress->id]);

      // FIFO: enter the unassigned queue. Dispatched via FifoQueueController
      // (Assign Next / Bulk Assign) or the scheduled fifo:dispatch command.
      (new \App\Services\Fifo\FifoQueueService())->enqueue(
        'outage',
        'outage',
        $outage->id,
        $outage->priority,
        $outage->region_id
      );

      DB::commit();
      \Log::info('Database transaction committed successfully');

      // Send notifications for outage creation
      $this->notificationService->notifyOutageCreated($outage);

      \Log::info('Redirecting to outage show page:', ['outage_id' => $outage->id]);
      return redirect()
        ->route('outages.show', $outage)
        ->with('success', 'Outage created successfully.');
    } catch (\Exception $e) {
      \Log::error('Exception occurred during outage creation:', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      DB::rollBack();
      \Log::info('Database transaction rolled back');
      return back()
        ->withInput()
        ->with('error', 'Failed to create outage: ' . $e->getMessage());
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Outage $outage)
  {
    $outage->load([
      'assignedTeam',
      'assignee',
      'reporter',
      'resolver',
      'reasons',
      'attachments',
      'progress' => function ($query) {
        $query->with('user')->latest();
      },
      'activities' => function ($query) {
        $query->with('user')->latest();
      },
    ]);

    return view($this->view('show'), compact('outage'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Outage $outage)
  {
    // Load relationships for the outage
    $outage->load(['olt', 'oltSlot', 'ponPort', 'assignedTeam', 'assignee', 'affectedAreas', 'affectedServices']);

    $teamTypes = TeamType::where('status', 'Active')->get();
    $teams = Team::where('status', 'Active')->get();
    $users = User::where('user_status', 1)->get();
    $priorities = ['Low', 'Medium', 'High', 'Critical'];
    $impacts = ['Low', 'Medium', 'High', 'Critical'];
    $urgencies = ['Low', 'Medium', 'High', 'Critical'];

    // Get all available status options
    $statuses = Outage::getStatusOptions();

    // Get OLTs for cascading dropdown
    $olts = Olt::where('status', 'active')
      ->orderBy('name')
      ->get(['id', 'name', 'ip_address', 'location']);

    // Get slots for current OLT if available
    $slots = [];
    if ($outage->olt_id) {
      $slots = OltSlot::where('olt_id', $outage->olt_id)
        ->where('status', 'active')
        ->orderBy('slot_number')
        ->get(['id', 'slot_number', 'slot_type']);
    }

    // Get ports for current slot if available
    $ports = [];
    if ($outage->slot_id) {
      $ports = PonPort::where('olt_slot_id', $outage->slot_id)
        ->where('status', 'active')
        ->orderBy('pon_port_number')
        ->get(['id', 'pon_port_number', 'pon_port_type']);
    }

    // Get active affected areas and services from database
    $areas = AffectedArea::where('area_status', 'Active')
      ->orderBy('area_name')
      ->get();

    $services = AffectedService::where('service_status', 'Active')
      ->orderBy('service_name')
      ->get();

    // Format dates for HTML inputs
    if ($outage->start_time) {
      $startDateTime = \Carbon\Carbon::parse($outage->start_time);
      $outage->start_date = $startDateTime->format('Y-m-d');
      $outage->start_time_formatted = $startDateTime->format('H:i');
    }

    if ($outage->end_time) {
      $endDateTime = \Carbon\Carbon::parse($outage->end_time);
      $outage->end_date = $endDateTime->format('Y-m-d');
      $outage->end_time_formatted = $endDateTime->format('H:i');
    }

    return view(
      $this->view('edit'),
      compact(
        'outage',
        'teamTypes',
        'teams',
        'users',
        'priorities',
        'impacts',
        'urgencies',
        'statuses',
        'areas',
        'services',
        'olts',
        'slots',
        'ports'
      )
    );
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Outage $outage)
  {
    $statusOptions = array_keys(Outage::getStatusOptions());
    $request->validate([
      'ticket_type' => 'required|in:regular,emergency,planned_maintenance',
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'status' => 'required|in:' . implode(',', $statusOptions),
      'priority' => 'required|in:Low,Medium,High,Critical',
      'impact' => 'required|in:Low,Medium,High,Critical',
      'urgency' => 'required|in:Low,Medium,High,Critical',
      'total_customers_affected' => 'nullable|integer|min:0',
      'olt_id' => 'required|exists:olts,id',
      'slot_id' => 'required|exists:olt_slots,id',
      'port_id' => 'required|exists:pon_ports,id',
      'start_date' => 'required|date',
      'start_time' => 'required|date_format:H:i',
      'end_date' => 'nullable|date|after_or_equal:start_date',
      'end_time' => 'nullable|date_format:H:i',
      'root_cause' => 'nullable|string',
      'resolution_notes' => 'nullable|string',
      'affected_areas' => 'nullable|array',
      'affected_areas.*' => 'exists:affected_areas,id',
      'affected_services' => 'nullable|array',
      'affected_services.*' => 'exists:affected_services,id',
      'assigned_team_type' => 'nullable|exists:team_types,id',
      'assigned_sub_team_type_id' => 'nullable|exists:sub_team_types,id',
      'assigned_to' => 'nullable|exists:users,id',
    ]);

    DB::beginTransaction();
    try {
      // Store old values for activity tracking
      $oldValues = $outage->toArray();
      $user = Auth::user();

      // Combine date and time fields
      $startDateTime = $request->start_date . ' ' . $request->start_time;
      $endDateTime = null;
      if ($request->end_date && $request->end_time) {
        $endDateTime = $request->end_date . ' ' . $request->end_time;
      }

      // Handle assignment logic - use team_type_id directly as assigned_team_id
      $assignedTeamId = $request->assigned_team_type;

      \Log::info('Updating outage assignment', [
        'outage_id' => $outage->id,
        'assigned_team_type' => $request->assigned_team_type,
        'assigned_sub_team_type_id' => $request->assigned_sub_team_type_id,
        'assigned_to' => $request->assigned_to,
        'old_assigned_team_id' => $outage->assigned_team_id,
        'old_assigned_to' => $outage->assigned_to,
      ]);

      // Update the outage
      $updateData = [
        'ticket_type' => $request->ticket_type,
        'title' => $request->title,
        'description' => $request->description,
        'status' => $request->status,
        'priority' => $request->priority,
        'impact' => $request->impact,
        'urgency' => $request->urgency,
        'total_customers_affected' => $request->total_customers_affected,
        'olt_id' => $request->olt_id,
        'slot_id' => $request->slot_id,
        'port_id' => $request->port_id,
        'start_time' => $startDateTime,
        'end_time' => $endDateTime,
        'root_cause' => $request->root_cause,
        'resolution_notes' => $request->resolution_notes,
        'assigned_team_id' => $assignedTeamId,
        'sub_team_type_id' => $request->assigned_sub_team_type_id,
        'assigned_to' => $request->assigned_to,
        'resolved_by' => in_array($request->status, ['infra-resolved', 'support-closed'])
          ? Auth::id()
          : $outage->resolved_by,
        'updated_by' => Auth::id(),
      ];

      \Log::info('Updating outage with data', [
        'outage_id' => $outage->id,
        'update_data' => $updateData,
      ]);

      $outage->update($updateData);

      // Update affected areas relationship
      if ($request->has('affected_areas')) {
        $outage->affectedAreas()->sync($request->affected_areas ?? []);
      }

      // Update affected services relationship
      if ($request->has('affected_services')) {
        $outage->affectedServices()->sync($request->affected_services ?? []);
      }

      // Get new values for activity tracking
      $newValues = $outage->fresh()->toArray();

      // Log comprehensive update activity (this will be handled by the model's updated event)
      // The model will automatically log the changes

      DB::commit();

      // Send notifications for outage update
      $this->notificationService->notifyOutageUpdated($outage, $user);

      return redirect()
        ->route('outages.show', $outage)
        ->with('success', 'Outage updated successfully.');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Outage update failed: ' . $e->getMessage(), [
        'outage_id' => $outage->id,
        'user_id' => Auth::id(),
        'request_data' => $request->all(),
      ]);
      return back()
        ->withInput()
        ->with('error', 'Failed to update outage: ' . $e->getMessage());
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Outage $outage)
  {
    try {
      $outage->delete();
      return redirect()
        ->route('outages.index')
        ->with('success', 'Outage updated successfully.');
    } catch (\Exception $e) {
      return back()->with('error', 'Failed to delete outage: ' . $e->getMessage());
    }
  }

  /**
   * Update the status of the specified outage.
   */
  public function updateStatus(Request $request, Outage $outage)
  {
    $statusOptions = array_keys(Outage::getStatusOptions());
    $request->validate([
      'status' => 'required|in:' . implode(',', $statusOptions),
      'notes' => 'nullable|string|max:1000',
    ]);

    $oldStatus = $outage->status;
    $outage->status = $request->status;

    // Set resolved_by if status is being changed to a resolved state
    if (
      in_array($request->status, ['infra-resolved', 'support-closed']) &&
      !in_array($oldStatus, ['infra-resolved', 'support-closed'])
    ) {
      $outage->resolved_by = Auth::id();
      $outage->end_time = now();
    }

    // Clear resolved_by if status is being changed from resolved state
    if (
      in_array($oldStatus, ['infra-resolved', 'support-closed']) &&
      !in_array($request->status, ['infra-resolved', 'support-closed'])
    ) {
      $outage->resolved_by = null;
      $outage->end_time = null;
    }

    $outage->updated_by = Auth::id();
    $outage->save();

    // Create progress entry for status change
    OutageProgress::create([
      'outage_id' => $outage->id,
      'user_id' => Auth::id(),
      'status' => $request->status,
      'notes' => $request->notes ?? "Status changed from {$oldStatus} to {$request->status}",
      'is_major_update' => true,
      'created_by' => Auth::id(),
    ]);

    // Log the activity
    OutageActivity::logStatusChange($outage, $oldStatus, $request->status, Auth::user(), $request->notes);

    // Send notifications for status change
    $this->notificationService->notifyOutageStatusChanged($outage, Auth::user(), $oldStatus, $request->status);

    return redirect()
      ->route('outages.show', $outage)
      ->with('success', 'Outage status updated successfully.');
  }

  /**
   * Store a new progress update for the outage.
   */
  public function storeProgress(Request $request, Outage $outage)
  {
    $request->validate([
      'notes' => 'required|string|max:1000',
      'action_taken' => 'nullable|string|max:1000',
      'next_steps' => 'nullable|string|max:1000',
      'is_major_update' => 'nullable|boolean',
    ]);

    // Create progress entry
    OutageProgress::create([
      'outage_id' => $outage->id,
      'user_id' => Auth::id(),
      'status' => $outage->status,
      'notes' => $request->notes,
      'action_taken' => $request->action_taken,
      'next_steps' => $request->next_steps,
      'is_major_update' => $request->boolean('is_major_update'),
      'created_by' => Auth::id(),
    ]);

    // Log the activity
    OutageActivity::logProgressAdded($outage, Auth::user(), $request->notes);

    return redirect()
      ->route('outages.show', $outage)
      ->with('success', 'Progress update added successfully.');
  }

  /**
   * Display the activity history for an outage.
   */
  public function activity(Outage $outage)
  {
    $outage->load([
      'activities' => function ($query) {
        $query->with('user')->latest();
      },
      'assignedTeam',
      'assignee',
      'reporter',
      'resolver',
    ]);

    return view($this->view('activity'), compact('outage'));
  }

  /**
   * Get slots for a specific OLT (AJAX endpoint)
   */
  public function getOltSlots($oltId)
  {
    try {
      $slots = OltSlot::where('olt_id', $oltId)
        ->where('status', 'active')
        ->orderBy('slot_number')
        ->get(['id', 'slot_number', 'slot_type', 'status']);

      return response()->json([
        'success' => true,
        'slots' => $slots->map(function ($slot) {
          return [
            'id' => $slot->id,
            'slot_number' => $slot->slot_number,
            'slot_type' => $slot->slot_type,
            'status' => $slot->status,
            'display_name' => "Slot {$slot->slot_number}" . ($slot->slot_type ? " ({$slot->slot_type})" : ''),
          ];
        }),
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Failed to fetch slots: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  /**
   * Get PON ports for a specific slot (AJAX endpoint)
   */
  public function getSlotPorts($slotId)
  {
    try {
      $ports = PonPort::where('olt_slot_id', $slotId)
        ->where('status', 'active')
        ->orderBy('pon_port_number')
        ->get(['id', 'pon_port_number', 'pon_port_type', 'status']);

      return response()->json([
        'success' => true,
        'ports' => $ports->map(function ($port) {
          return [
            'id' => $port->id,
            'pon_port_number' => $port->pon_port_number,
            'pon_port_type' => $port->pon_port_type,
            'status' => $port->status,
            'display_name' =>
              "Port {$port->pon_port_number}" . ($port->pon_port_type ? " ({$port->pon_port_type})" : ''),
          ];
        }),
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Failed to fetch ports: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  /**
   * Get sub team types for a specific team type (AJAX endpoint)
   */
  public function getSubTeamsByType($teamTypeId)
  {
    try {
      $subTeamTypes = SubTeamType::where('team_type_id', $teamTypeId)
        ->where('sub_type_status', 'Active')
        ->orderBy('sub_type_name')
        ->get(['id', 'sub_type_name', 'sub_type_description']);

      return response()->json([
        'success' => true,
        'subTeamTypes' => $subTeamTypes->map(function ($subTeamType) {
          return [
            'id' => $subTeamType->id,
            'sub_type_name' => $subTeamType->sub_type_name,
            'sub_type_description' => $subTeamType->sub_type_description,
            'display_name' => $subTeamType->sub_type_name,
          ];
        }),
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Failed to fetch sub team types: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  /**
   * Get users for a specific sub team type (AJAX endpoint)
   */
  public function getUsersBySubTeamType($subTeamTypeId)
  {
    try {
      // user_status is stored as 1/0, not the string 'Active' (see
      // HomeController, Admin\UserController) — this always matched zero
      // rows, so the "Assigned To" dropdown was permanently empty for every
      // sub-team-type regardless of how many technicians were configured.
      $users = User::where('sub_team_type_id', $subTeamTypeId)
        ->where('user_status', 1)
        ->orderBy('name')
        ->get(['id', 'name', 'email']);

      return response()->json([
        'success' => true,
        'users' => $users->map(function ($user) {
          return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => $user->name,
          ];
        }),
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Failed to fetch users: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  /**
   * Get sub team types for a specific team type (API endpoint)
   */
  public function getSubTeamTypes($teamTypeId)
  {
    try {
      $subTeamTypes = SubTeamType::where('team_type_id', $teamTypeId)
        ->where('sub_type_status', 'Active')
        ->orderBy('sub_type_name')
        ->get(['id', 'sub_type_name', 'sub_type_description']);

      return response()->json([
        'success' => true,
        'subTeamTypes' => $subTeamTypes->map(function ($subTeamType) {
          return [
            'id' => $subTeamType->id,
            'sub_type_name' => $subTeamType->sub_type_name,
            'sub_type_description' => $subTeamType->sub_type_description,
            'display_name' => $subTeamType->sub_type_name,
          ];
        }),
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Failed to fetch sub team types: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  /**
   * Display assigned outages - outages assigned to teams
   */
  public function assignedOutages(Request $request)
  {
    $query = Outage::with(['assignedTeam', 'assignee', 'reporter', 'resolver'])
      ->whereNotNull('assigned_team_id')
      ->orderBy('created_at', 'desc');

    // Apply filters
    if ($request->filled('search')) {
      $search = $request->get('search');
      $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('ticket_number', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    if ($request->filled('status')) {
      $query->where('status', $request->get('status'));
    }

    if ($request->filled('priority')) {
      $query->where('priority', $request->get('priority'));
    }

    if ($request->filled('assigned_team_id')) {
      $query->where('assigned_team_id', $request->get('assigned_team_id'));
    }

    $outages = $query->paginate(15);

    // Log page access activity for each outage viewed
    $user = Auth::user();
    foreach ($outages as $outage) {
      OutageActivity::logActivity([
        'outage_id' => $outage->id,
        'user_id' => $user->id,
        'activity_type' => 'viewed',
        'activity_title' => 'Assigned Outages Page Accessed',
        'activity_description' => "User {$user->name} accessed assigned outages page and viewed this ticket",
        'is_major_activity' => false,
        'is_system_generated' => false,
      ]);
    }

    // Get filter options
    $teams = Team::orderBy('team_name')->get();
    $priorities = Outage::select('priority')
      ->distinct()
      ->whereNotNull('priority')
      ->pluck('priority');
    $statusOptions = Outage::getStatusOptions();

    return view($this->view('assigned'), compact('outages', 'teams', 'priorities', 'statusOptions'));
  }

  /**
   * Display my outages - outages assigned to current user or their team
   */
  public function myOutages(Request $request)
  {
    $user = Auth::user();
    $userTeamId = $user->team_id ?? null;

    $query = Outage::with(['assignedTeam', 'assignee', 'reporter', 'resolver'])
      ->where(function ($q) use ($user, $userTeamId) {
        $q->where('assigned_to', $user->id);
        if ($userTeamId) {
          $q->orWhere('assigned_team_id', $userTeamId);
        }
      })
      // Was excluding 'noc-restore-confirmed' alongside 'infra-resolved',
      // but that isn't a terminal state — Outage::isResolved()/scopeResolved()
      // only treat infra-resolved and support-closed as "done". Hiding
      // noc-restore-confirmed here meant the moment a NOC tech confirmed
      // restoration, the ticket vanished from every My Outages queue
      // (including their own) with no way to reach the real final step,
      // Support Closed, through this view at all.
      ->whereNotIn('status', ['infra-resolved', 'support-closed'])
      ->orderBy('created_at', 'desc');

    // Apply filters
    if ($request->filled('search')) {
      $search = $request->get('search');
      $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('ticket_number', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    if ($request->filled('status')) {
      $query->where('status', $request->get('status'));
    }

    if ($request->filled('priority')) {
      $query->where('priority', $request->get('priority'));
    }

    $outages = $query->paginate(15);

    // Log page access activity for each outage viewed
    foreach ($outages as $outage) {
      OutageActivity::logActivity([
        'outage_id' => $outage->id,
        'user_id' => $user->id,
        'activity_type' => 'viewed',
        'activity_title' => 'My Outages Page Accessed',
        'activity_description' => "User {$user->name} accessed my outages page and viewed this ticket",
        'is_major_activity' => false,
        'is_system_generated' => false,
      ]);
    }

    // Get filter options
    $priorities = Outage::select('priority')
      ->distinct()
      ->whereNotNull('priority')
      ->pluck('priority');
    $statusOptions = Outage::getStatusOptions();

    return view($this->view('my-outages'), compact('outages', 'priorities', 'statusOptions'));
  }

  /**
   * Show the form for editing my outage with progress update functionality
   */
  public function editMyOutage(Outage $outage)
  {
    $user = Auth::user();
    $userTeamId = $user->team_id ?? null;

    // Check if user has permission to edit this outage
    if ($outage->assigned_to !== $user->id && $outage->assigned_team_id !== $userTeamId) {
      abort(403, 'You do not have permission to edit this outage.');
    }

    // Check if outage status allows editing (block resolved/completed statuses).
    // Same fix as myOutages() above: noc-restore-confirmed isn't a terminal
    // state per Outage::isResolved()/scopeResolved() — blocking edits here
    // meant a NOC tech who confirmed restoration could never then move the
    // ticket on to the real final state, support-closed.
    if (in_array($outage->status, ['infra-resolved', 'support-closed'])) {
      abort(403, 'This outage has been resolved and can no longer be edited.');
    }

    // Log edit form access activity
    OutageActivity::logActivity([
      'outage_id' => $outage->id,
      'user_id' => $user->id,
      'activity_type' => 'edit_form_accessed',
      'activity_title' => 'Edit Form Accessed',
      'activity_description' => "User {$user->name} accessed the edit form for progress updates",
      'is_major_activity' => false,
      'is_system_generated' => false,
    ]);

    // Get final reasons from database
    $finalReasons = \Modules\Outages\Models\OutageFinalReason::where('final_reason_status', 'Active')
      ->orderBy('final_reason_name')
      ->get();

    $statusOptions = Outage::getStatusOptions();
    $priorities = ['Low', 'Medium', 'High', 'Critical'];

    return view($this->view('edit-my-outage'), compact('outage', 'finalReasons', 'statusOptions', 'priorities'));
  }

  /**
   * Update my outage with progress and attachments
   */
  public function updateMyOutage(Request $request, Outage $outage)
  {
    $user = Auth::user();
    $userTeamId = $user->team_id ?? null;

    // Check if user has permission to edit this outage
    if ($outage->assigned_to !== $user->id && $outage->assigned_team_id !== $userTeamId) {
      abort(403, 'You do not have permission to edit this outage.');
    }

    $request->validate([
      'status' => 'required|string',
      'progress_notes' => 'nullable|string',
      'resolution_notes' => 'nullable|string',
      'final_reason_id' => 'nullable|exists:outage_final_reasons,id',
      'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:10240', // 10MB max
    ]);

    // Store old values for activity tracking
    $oldValues = [
      'status' => $outage->status,
      'resolution_notes' => $outage->resolution_notes,
      'final_reason_id' => $outage->final_reason_id,
    ];

    $newValues = [
      'status' => $request->status,
      'resolution_notes' => $request->resolution_notes,
      'final_reason_id' => $request->final_reason_id,
    ];

    // Track status change if it occurred
    $statusChanged = $outage->status !== $request->status;
    $oldStatus = $outage->status;

    // Track final reason change if it occurred
    $finalReasonChanged = $outage->final_reason_id != $request->final_reason_id;
    $oldFinalReasonId = $outage->final_reason_id;

    // Update outage
    $outage->update([
      'status' => $request->status,
      'resolution_notes' => $request->resolution_notes,
      'final_reason_id' => $request->final_reason_id,
      'updated_by' => $user->id,
    ]);

    // Log status change activity if status was changed
    if ($statusChanged) {
      OutageActivity::logStatusChange($outage, $oldStatus, $request->status, $user, $request->progress_notes);
    }

    // Log final reason change activity if final reason was changed
    if ($finalReasonChanged) {
      $oldReason = $oldFinalReasonId ? \Modules\Outages\Models\OutageFinalReason::find($oldFinalReasonId) : null;
      $newReason = $request->final_reason_id
        ? \Modules\Outages\Models\OutageFinalReason::find($request->final_reason_id)
        : null;

      OutageActivity::logActivity([
        'outage_id' => $outage->id,
        'user_id' => $user->id,
        'activity_type' => 'final_reason_set',
        'activity_title' => 'Final Reason Updated',
        'activity_description' => sprintf(
          'Final reason changed from "%s" to "%s"',
          $oldReason ? $oldReason->final_reason_name : 'None',
          $newReason ? $newReason->final_reason_name : 'None'
        ),
        'old_values' => json_encode(['final_reason_id' => $oldFinalReasonId]),
        'new_values' => json_encode(['final_reason_id' => $request->final_reason_id]),
        'is_major_activity' => true,
      ]);
    }

    // Add progress update if provided
    if ($request->filled('progress_notes')) {
      OutageProgress::create([
        'outage_id' => $outage->id,
        'user_id' => $user->id,
        'notes' => $request->progress_notes,
        'status' => $request->status,
        'created_by' => $user->id,
      ]);

      // Log progress addition activity
      OutageActivity::logProgressAdded($outage, $user, $request->progress_notes);
    }

    // Handle file attachments
    $uploadedFiles = [];
    if ($request->hasFile('attachments')) {
      foreach ($request->file('attachments') as $file) {
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('outage-attachments', $filename, 'public');
        $uploadedFiles[] = $file->getClientOriginalName();

        // You may need to create OutageAttachment model or use existing one
        // OutageAttachment::create([
        //     'outage_id' => $outage->id,
        //     'filename' => $file->getClientOriginalName(),
        //     'file_path' => $path,
        //     'file_size' => $file->getSize(),
        //     'mime_type' => $file->getMimeType(),
        //     'uploaded_by' => $user->id,
        // ]);

        // Log file attachment activity
        OutageActivity::logActivity([
          'outage_id' => $outage->id,
          'user_id' => $user->id,
          'activity_type' => 'attachment_added',
          'activity_title' => 'File Attachment Added',
          'activity_description' => "User {$user->name} uploaded file: {$file->getClientOriginalName()}",
          'new_values' => [
            'filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
          ],
          'is_major_activity' => false,
          'is_system_generated' => false,
        ]);
      }
    }

    // Log resolution notes update if provided
    if ($request->filled('resolution_notes') && $oldValues['resolution_notes'] !== $request->resolution_notes) {
      OutageActivity::logActivity([
        'outage_id' => $outage->id,
        'user_id' => $user->id,
        'activity_type' => 'resolution_updated',
        'activity_title' => 'Resolution Notes Updated',
        'activity_description' => "User {$user->name} updated resolution notes",
        'old_values' => ['resolution_notes' => $oldValues['resolution_notes']],
        'new_values' => ['resolution_notes' => $request->resolution_notes],
        'is_major_activity' => true,
        'is_system_generated' => false,
      ]);
    }

    // Log final reason if provided
    if ($request->filled('final_reason')) {
      OutageActivity::logActivity([
        'outage_id' => $outage->id,
        'user_id' => $user->id,
        'activity_type' => 'final_reason_set',
        'activity_title' => 'Final Reason Set',
        'activity_description' => "User {$user->name} set final reason: {$request->final_reason}",
        'new_values' => ['final_reason' => $request->final_reason],
        'is_major_activity' => true,
        'is_system_generated' => false,
      ]);
    }

    // Log general update activity (comprehensive summary)
    $updateSummary = [];
    if ($statusChanged) {
      $updateSummary[] = 'status';
    }
    if ($request->filled('progress_notes')) {
      $updateSummary[] = 'progress notes';
    }
    if ($request->filled('resolution_notes')) {
      $updateSummary[] = 'resolution notes';
    }
    if ($request->filled('final_reason')) {
      $updateSummary[] = 'final reason';
    }
    if (!empty($uploadedFiles)) {
      $updateSummary[] = count($uploadedFiles) . ' file(s)';
    }

    if (!empty($updateSummary)) {
      OutageActivity::logUpdate($outage, $user, $oldValues, $newValues);
    }

    return redirect()
      ->route('outages.my-outages')
      ->with('success', 'Outage updated successfully!');
  }
}
