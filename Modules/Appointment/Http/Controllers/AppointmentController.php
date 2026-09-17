<?php

namespace Modules\Appointment\Http\Controllers;

use App\Http\Controllers\OptimizedController;
use App\Models\SubDepartment;
use App\Models\TeamType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentFinalReason;
use Modules\Appointment\Models\AppointmentHistory;
use Modules\Appointment\Models\AppointmentStatus;
use Modules\Appointment\Models\AppointmentStatusHistory;
use Modules\Appointment\Models\AppointmentType;
use Modules\Appointment\Models\SubAppointmentType;
use Modules\Appointment\Services\NotificationService;
use Modules\Outages\Models\Olt;
use Modules\Outages\Models\OltSlot;

class AppointmentController extends OptimizedController
{
    /**
     * The notification service instance.
     *
     * @var \Modules\Appointment\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
        // $this->middleware('permission:view-list-appointment')->only(['index']);
        // $this->middleware('permission:view-create-appointment')->only(['create', 'store']);
        // $this->middleware('permission:view-view-appointment')->only(['show']);
        // $this->middleware('permission:view-edit-appointment')->only(['edit', 'update']);
        // $this->middleware('permission:view-delete-appointment')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Appointment::withOptimizedRelations(['type', 'subType', 'creator', 'assignedTeam', 'olt']);

        // Apply status filter if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $appointments = $query->latest()->paginate(25);

        // Get all active statuses for the filter dropdown
        $statuses = AppointmentStatus::active()
            ->orderBy('sort_order')
            ->get();

        return view('appointment::appointment.index', compact('appointments', 'statuses'));
    }

    /**
     * Display a list of appointments grouped by type
     */
    /**
     * Display assigned appointments grouped by team type (In-House/Outsource Partner)
     */
    public function assigned()
    {
        // Get in-house team type ID (assuming it exists)
        $inhouseTeamType = TeamType::where('type_name', 'Inhouse')->first();
        $outsourceTeamType = TeamType::where('type_name', 'Outsource Partner')->first();

        // Get appointments for in-house team (specific statuses only)
        $inhouseAppointments = [];
        if ($inhouseTeamType) {
            $inhouseAppointments = Appointment::with([
                'type',
                'subType',
                'creator',
                'assignedTeam',
                'teamType',
                'subTeamType',
            ])
                ->where('team_type_id', $inhouseTeamType->id)
                ->whereIn('status', ['scheduled-assigned-team', 'rescheduled', 'support-post-install'])
                ->latest()
                ->get();
        }

        // Get appointments for outsource partner team (specific statuses only)
        $outsourceAppointments = [];
        if ($outsourceTeamType) {
            $outsourceAppointments = Appointment::with([
                'type',
                'subType',
                'creator',
                'assignedTeam',
                'teamType',
                'subTeamType',
            ])
                ->where('team_type_id', $outsourceTeamType->id)
                ->whereIn('status', ['scheduled-assigned-team', 'rescheduled', 'support-post-install'])
                ->latest()
                ->get();
        }

        // For the bulk-assign toolbar's Team Type dropdown (same source as
        // the single-appointment edit() form uses).
        $teamTypes = TeamType::active()->get();

        return view(
            'appointment::appointment.assigned',
            compact('inhouseAppointments', 'outsourceAppointments', 'teamTypes')
        );
    }

    /**
     * Bulk-assign Team Type, Sub Team Type, Technician, and (for Outsource
     * Partner) Assign Team to multiple appointments at once — the same
     * fields update() sets for a single appointment, applied to every
     * selected row. Only ever reached from the Assigned Appointments page,
     * where every listed appointment is already in a team-assigned-family
     * status, so this deliberately does NOT touch `status` — that field is
     * about ticket lifecycle, not who's working it, and bulk-changing it
     * for a mixed batch of tickets would be guessing at intent no form
     * field here actually asked for.
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'appointment_ids' => 'required|array|min:1',
            'appointment_ids.*' => 'exists:appointments,id',
            'team_type_id' => 'required|exists:team_types,id',
            'sub_team_type_id' => 'required|exists:sub_team_types,id',
            'assigned_to' => 'nullable|exists:users,id',
            'assigned_team_id' => 'nullable|exists:teams,id',
        ]);

        $updated = 0;

        DB::transaction(function () use ($validated, &$updated) {
            $appointments = Appointment::whereIn('id', $validated['appointment_ids'])->get();

            foreach ($appointments as $appointment) {
                $appointment->team_type_id = $validated['team_type_id'];
                $appointment->sub_team_type_id = $validated['sub_team_type_id'];
                $appointment->assigned_to = $validated['assigned_to'] ?? null;
                $appointment->assigned_team_id = $validated['assigned_team_id'] ?? null;
                $appointment->edited_by = auth()->id();
                $appointment->save();

                if (class_exists('\Modules\Appointment\Models\AppointmentHistory')) {
                    \Modules\Appointment\Models\AppointmentHistory::create([
                        'appointment_id' => $appointment->id,
                        'action' => 'bulk_assigned',
                        'ticket_id' => $appointment->appointment_ticket_id,
                        'action_by' => auth()->id(),
                        'status' => $appointment->status,
                        'account_number' => $appointment->account_number,
                        'priority' => $appointment->priority,
                        'appointment_type_id' => $appointment->appointment_type_id,
                        'team_type_id' => $appointment->team_type_id,
                        'sub_team_type_id' => $appointment->sub_team_type_id,
                        'assigned_team_id' => $appointment->assigned_team_id,
                        'assigned_to' => $appointment->assigned_to,
                        'edited_by' => $appointment->edited_by,
                    ]);
                }

                $this->notificationService->notifyAppointmentUpdated($appointment, auth()->user());

                $updated++;
            }
        });

        return redirect()
            ->route('appointment.appointments.assigned')
            ->with('success', "Bulk-assigned {$updated} appointment(s).");
    }

    /**
     * Display a list of appointments grouped by type
     */
    public function list()
    {
        // Get all appointment types for the navigation pills
        $appointmentTypes = AppointmentType::with('subTypes')->get();

        // Initialize an array to hold appointments grouped by type
        $appointmentsByType = [];

        // Get appointments for each type
        foreach ($appointmentTypes as $type) {
            // Get all sub-type IDs for this type
            $subTypeIds = $type->subTypes->pluck('id');

            // Get appointments for this type (scheduled-open and support-post-install statuses)
            $appointmentsByType[$type->id] = Appointment::with(['type', 'subType', 'creator', 'assignedTeam', 'olt'])
                ->whereIn('appointment_type_id', $subTypeIds)
                ->whereIn('status', ['scheduled-open', 'support-post-install'])
                ->latest()
                ->get();
        }

        return view('appointment::appointment.list', [
            'appointmentTypes' => $appointmentTypes,
            'appointmentsByType' => $appointmentsByType,
        ]);
    }

    /**
     * Display appointments assigned to the current user
     */
    public function myAppointments()
    {
        $currentUser = auth()->user();

        // Eager load the teams relationship to avoid N+1 queries
        if (method_exists($currentUser, 'teams')) {
            $currentUser->load('teams');
        }

        // Get appointments where the user is assigned through any of the following:
        // 1. User's sub_team_type_id matches appointment's sub_team_type_id
        // 2. User's team_id matches appointment's assigned_team_id
        // 3. User belongs to a team that is assigned to the appointment
        $myAppointments = Appointment::with([
            'type',
            'subType',
            'creator',
            'assignedTeam',
            'teamType',
            'subTeamType',
            'appointmentStatus',
        ])
            ->where(function ($query) use ($currentUser) {
                // Check if user's sub_team_type_id matches appointment's sub_team_type_id
                if ($currentUser->sub_team_type_id) {
                    $query->where('sub_team_type_id', $currentUser->sub_team_type_id);
                }

                // Check if user belongs to a team that is assigned to appointments
                if (method_exists($currentUser, 'teams') && $currentUser->teams && $currentUser->teams->count() > 0) {
                    $teamIds = $currentUser->teams->pluck('id')->toArray();
                    if (! empty($teamIds)) {
                        $query->orWhereIn('assigned_team_id', $teamIds);
                    }
                }

                // Check if user's team_id matches appointment's assigned_team_id
                if (! empty($currentUser->team_id)) {
                    $query->orWhere('assigned_team_id', $currentUser->team_id);
                }

                // Check if user's team_type_id matches appointment's team_type_id
                if (! empty($currentUser->team_type_id)) {
                    $query->orWhere(function ($q) use ($currentUser) {
                        $q->where('team_type_id', $currentUser->team_type_id)->whereNotNull('assigned_team_id');
                    });
                }
            })
            ->whereIn('status', ['scheduled-assigned-team', 'rescheduled', 'support-post-install'])
            ->latest()
            ->get();

        \Log::info('My Appointments query for user: '.$currentUser->id, [
            'user_team_id' => $currentUser->team_id ?? 'null',
            'user_team_type_id' => $currentUser->team_type_id ?? 'null',
            'user_sub_team_type_id' => $currentUser->sub_team_type_id ?? 'null',
            'appointment_count' => $myAppointments->count(),
        ]);

        return view('appointment::appointment.my_appointments', compact('myAppointments'));
    }

    /**
     * Whether $user is a legitimate assignee of $appointment, using the same
     * assignment rules as myAppointments() above (direct assignee, matching
     * sub-team-type, matching team, or membership of the assigned team) plus
     * the direct assigned_to column. Used to gate the "assigned/edit" routes
     * so a user holding only 'view-my-appointment-edit' can't reach an
     * appointment that was never actually assigned to them.
     */
    private function userOwnsAppointment(Appointment $appointment, $user): bool
    {
        if ((int) $appointment->assigned_to === (int) $user->id) {
            return true;
        }

        if ($user->sub_team_type_id && (int) $appointment->sub_team_type_id === (int) $user->sub_team_type_id) {
            return true;
        }

        if (! empty($user->team_id) && (int) $appointment->assigned_team_id === (int) $user->team_id) {
            return true;
        }

        if (! empty($user->team_type_id) && (int) $appointment->team_type_id === (int) $user->team_type_id && $appointment->assigned_team_id !== null) {
            return true;
        }

        if (method_exists($user, 'teams')) {
            $teamIds = $user->teams()->pluck('teams.id')->toArray();
            if (! empty($teamIds) && in_array($appointment->assigned_team_id, $teamIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = AppointmentType::active()->get();
        $teamTypes = TeamType::active()->get();
        $subTypes = SubAppointmentType::active()->get();
        $olts = Olt::active()
            ->orderBy('name')
            ->get();
        $statuses = AppointmentStatus::active()
            ->orderBy('sort_order')
            ->get();

        // Generate a unique submission token for this form
        $submissionToken = md5(uniqid(mt_rand(), true));

        return view('appointment::appointment.create', compact('types', 'subTypes', 'olts', 'statuses', 'submissionToken'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log the beginning of the store method
        \Illuminate\Support\Facades\Log::info('AppointmentController@store: Starting validation');

        // Check if this is a duplicate submission using session token
        $submissionToken = $request->input('_submission_token');
        if ($submissionToken) {
            if (session()->has('used_submission_tokens') && in_array($submissionToken, session('used_submission_tokens'))) {
                \Illuminate\Support\Facades\Log::warning('AppointmentController@store: Duplicate submission detected', [
                    'token' => $submissionToken,
                ]);

                return redirect()
                    ->route('appointment.appointments.index')
                    ->with('warning', 'This form has already been submitted. Please refresh and try again if needed.');
            }

            // Store the token as used
            $usedTokens = session('used_submission_tokens', []);
            $usedTokens[] = $submissionToken;
            session(['used_submission_tokens' => $usedTokens]);
        }

        $validated = $request->validate([
            'account_number' => 'required|string|max:255',
            'appointment_id' => 'required|exists:appointment_types,id',
            'appointment_type_id' => 'required|exists:sub_appointment_types,id',
            'priority' => 'required|in:High,Medium,Low',
            'status' => 'required|exists:appointment_statuses,name',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'appointment_location' => 'required|string|max:255',
            'appointment_venue' => 'required|string|max:255',
            'description_notes' => 'required|string',
            'olt_id' => 'nullable|exists:olts,id',
            'slot_id' => 'nullable|exists:olt_slots,id',
        ]);

        // Log after validation
        \Illuminate\Support\Facades\Log::info('AppointmentController@store: Validation completed', [
            'validated_data' => array_keys($validated),
        ]);

        // Get the appointment type
        $appointmentType = AppointmentType::findOrFail($validated['appointment_id']);

        // Ticket prefix comes from the type's configured code_prefix (set via
        // Appointment Types settings). Falls back to a derived prefix only for
        // legacy types saved before that field existed.
        $prefix = $appointmentType->code_prefix
          ?: strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $appointmentType->type_name), 0, 3));
        if (empty($prefix)) {
            $prefix = 'TKT';
        }

        $validated['created_by'] = auth()->id();
        $validated['edited_by'] = auth()->id();

        // Get default status (Scheduled-Open) or use the one from the form
        $defaultStatus = AppointmentStatus::where('name', 'scheduled-open')->first();
        $validated['status'] = $defaultStatus ? $defaultStatus->name : $validated['status'] ?? 'scheduled-open';

        // SequenceNumberService::next() is called BEFORE the transaction below,
        // not inside it: next() commits its own increment immediately (it takes
        // a row lock per prefix so two concurrent requests can't receive the same
        // number), but if it were called inside this transaction, a later failure
        // here (e.g. a bad FK on AppointmentHistory) would roll back that
        // increment along with everything else — silently "returning" the ticket
        // number for the next appointment created to hand out again, producing
        // two different appointments sharing one ticket ID with no visible error.
        $ticketNumber = \App\Services\SequenceNumberService::next('appointment:'.$prefix);
        $validated['appointment_ticket_id'] = $prefix.'-'.$ticketNumber;

        // The appointment + status history + history log writes are still
        // wrapped together: a failure partway through rolls back the whole
        // thing instead of leaving an orphaned appointment with no status
        // history (as happened before this fix, e.g. ticket SUP-1) — it just no
        // longer also rolls back the ticket number.
        $appointment = DB::transaction(function () use ($validated) {
            $appointment = Appointment::create($validated);

            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->id,
                'previous_status' => null,
                'new_status' => $appointment->status,
                'notes' => 'Initial status set during appointment creation',
                'changed_by' => auth()->id(),
            ]);

            if (class_exists('\Modules\Appointment\Models\AppointmentHistory')) {
                \Modules\Appointment\Models\AppointmentHistory::create([
                    'appointment_id' => $appointment->id,
                    'action' => 'created',
                    'ticket_id' => $appointment->appointment_ticket_id,
                    'action_by' => auth()->id(),
                    'status' => $appointment->status,
                    'account_number' => $appointment->account_number,
                    'priority' => $appointment->priority,
                    'appointment_type_id' => $appointment->appointment_type_id,
                    'olt_id' => $appointment->olt_id,
                    'slot_id' => $appointment->slot_id,
                ]);
            }

            // FIFO: enter the unassigned queue. Dispatched via FifoQueueController
            // (Assign Next / Bulk Assign) or the scheduled fifo:dispatch command.
            (new \App\Services\Fifo\FifoQueueService)->enqueue(
                'appointment',
                'appointment',
                $appointment->id,
                $appointment->priority,
                $appointment->region_id
            );

            return $appointment;
        });

        \Illuminate\Support\Facades\Log::info('AppointmentController@store: Appointment created successfully', [
            'appointment_id' => $appointment->id,
            'appointment_ticket_id' => $appointment->appointment_ticket_id,
        ]);

        // Create notifications
        $this->notificationService->notifyAppointmentCreated($appointment);

        return redirect()
            ->route('appointment.appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $appointment = Appointment::with([
            'type',
            'subType',
            'creator',
            'editor',
            'assignedTeam',
            'escalatedTeam',
            'closer',
            'teamType',
            'subTeamType',
            'statusHistory',
            'statusHistory.user',
        ])->findOrFail($id);

        // Get status history with related status records for colors
        $statusHistory = AppointmentStatusHistory::where('appointment_id', $id)
            ->with(['user', 'previousStatusRecord', 'newStatusRecord'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('appointment::appointment.show', compact('appointment', 'statusHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $appointment = Appointment::findOrFail($id);
        $types = AppointmentType::active()->get();
        $teamTypes = TeamType::active()->get();
        $subTypes = SubAppointmentType::where('appointment_type_id', $appointment->type_id)
            ->active()
            ->pluck('sub_type_name', 'id');
        $statuses = AppointmentStatus::active()
            ->orderBy('sort_order')
            ->get();

        return view('appointment::appointment.edit', compact('appointment', 'types', 'teamTypes', 'subTypes', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Enhanced debugging - log request method, URL and data
        \Log::info('Starting appointment update process', [
            'method' => $request->method(),
            'url' => $request->url(),
            'appointment_id' => $id,
            'user_id' => auth()->id(),
            'request_data' => $request->all(),
        ]);

        try {
            $appointment = Appointment::findOrFail($id);
            \Log::debug('Appointment found', ['appointment' => $appointment->toArray()]);

            // Validate the request - removed type_id requirement since we'll use the existing appointment data
            $validatedData = $request->validate([
                'account_number' => 'required|string|max:50',
                'appointment_type_id' => 'required|exists:sub_appointment_types,id',
                'priority' => 'required|in:High,Medium,Low',
                'scheduled_date' => 'required|date',
                'scheduled_time' => 'required',
                'appointment_location' => 'required|string|max:255',
                'appointment_venue' => 'required|string|max:255',
                'description_notes' => 'required|string',
                'status' => 'required|exists:appointment_statuses,name',
                'team_type_id' => 'required|exists:team_types,id',
                'sub_team_type_id' => 'required|exists:sub_team_types,id',
                'assigned_team_id' => 'nullable|exists:teams,id',
                // The mobile app's technician views (MobileAppointmentController)
                // filter strictly by assigned_to = the logged-in user's id, but
                // until now there was no field anywhere on this form to set it —
                // dispatchers could only assign a Team Type + Sub Team Type
                // (a category, not a person), so an appointment assigned this way
                // could never appear for any technician on mobile.
                'assigned_to' => 'nullable|exists:users,id',
                'olt_id' => 'nullable|string|max:50',
                'slot_id' => 'nullable|string|max:50',
            ]);

            // Keep the existing escalated_team_id if it's not in the request
            if (! $request->has('escalated_team_id')) {
                $validatedData['escalated_team_id'] = $appointment->escalated_team_id;
            }

            // Use the existing appointment_id from the appointment model
            // This preserves the relationship with appointment_types
            $validatedData['appointment_id'] = $appointment->appointment_id;

            \Log::debug('Validation passed', ['validated_data' => $validatedData]);

            // Add editor information
            $validatedData['edited_by'] = auth()->id();
            \Log::debug('Added editor info', ['edited_by' => auth()->id()]);

            // Handle appointment completion
            if ($request->status === 'Scheduled-Closed' && $appointment->status !== 'Scheduled-Closed') {
                $validatedData['completed_date'] = now()->toDateString();
                $validatedData['completed_time'] = now()->toTimeString();
                $validatedData['closed_by'] = auth()->id();
                $validatedData['closed_at'] = now();
                \Log::info('Marking appointment as completed', [
                    'completed_date' => $validatedData['completed_date'],
                    'completed_time' => $validatedData['completed_time'],
                ]);
            }

            // Log original data
            $originalData = $appointment->getOriginal();
            \Log::debug('Original appointment data', $originalData);
            \Log::debug('New appointment data', $validatedData);

            // Find and log changed fields
            $changes = [];
            foreach ($validatedData as $key => $value) {
                if (! array_key_exists($key, $originalData) || $originalData[$key] != $value) {
                    $changes[$key] = [
                        'from' => $originalData[$key] ?? null,
                        'to' => $value,
                    ];
                }
            }

            \Log::info('Detected changes', ['changes' => $changes]);

            if (empty($changes)) {
                \Log::warning('No changes detected in the update request');

                return redirect()
                    ->back()
                    ->with('info', 'No changes were made.');
            }

            // Store the previous status before updating
            $previousStatus = $appointment->status;

            // Update the appointment with validated data
            // Fix for SQL error: ensure status is properly quoted by using the model's update method
            // instead of directly passing the array to update
            foreach ($validatedData as $key => $value) {
                $appointment->$key = $value;
            }

            // Track status changes if the status has changed
            if (isset($validatedData['status']) && $previousStatus !== $validatedData['status']) {
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment->id,
                    'previous_status' => $previousStatus,
                    'new_status' => $validatedData['status'],
                    'notes' => $request->input('status_change_notes') ?? 'Status updated during appointment edit',
                    'changed_by' => auth()->id(),
                ]);

                \Log::info('Appointment status changed', [
                    'appointment_id' => $appointment->id,
                    'from' => $previousStatus,
                    'to' => $validatedData['status'],
                    'changed_by' => auth()->id(),
                ]);
            }
            $appointment->save();
            \Log::info('Appointment updated successfully', ['appointment_id' => $appointment->id]);

            // Record history
            if (class_exists('\Modules\Appointment\Models\AppointmentHistory')) {
                \Modules\Appointment\Models\AppointmentHistory::create([
                    'appointment_id' => $appointment->id,
                    'action' => 'updated',
                    'ticket_id' => $appointment->appointment_ticket_id,
                    'action_by' => auth()->id(),
                    'status' => $appointment->status,
                    'account_number' => $appointment->account_number,
                    'priority' => $appointment->priority,
                    'appointment_type_id' => $appointment->appointment_type_id,
                    'team_type_id' => $appointment->team_type_id,
                    'sub_team_type_id' => $appointment->sub_team_type_id,
                    'assigned_team_id' => $appointment->assigned_team_id,
                    'escalated_team_id' => $appointment->escalated_team_id,
                    'completed_date' => $appointment->completed_date,
                    'completed_time' => $appointment->completed_time,
                    'closed_by' => $appointment->closed_by,
                    'closed_at' => $appointment->closed_at,
                    'edited_by' => $appointment->edited_by,
                    'edited_at' => $appointment->edited_at,
                    'description' => $appointment->description_notes,
                    'appointment_location' => $appointment->appointment_location,
                    'appointment_venue' => $appointment->appointment_venue,
                    'olt_id' => $appointment->olt_id,
                    'slot_id' => $appointment->slot_id,
                ]);
            }

            // Create notifications using the notification service
            $this->notificationService->notifyAppointmentUpdated($appointment, auth()->user());

            return redirect()
                ->route('appointment.appointments.show', $appointment->id)
                ->with('success', 'Appointment updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error updating appointment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'appointment_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the appointment. Please try again.')
                ->withErrors(['exception' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);

        // Prevent deletion of completed appointments
        if ($appointment->status === 'Completed') {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete a completed appointment.');
        }

        $appointment->delete();

        return redirect()
            ->route('appointment.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }

    /**
     * Show the form for editing an assigned appointment.
     */
    public function editAssigned($id)
    {
        $appointment = Appointment::findOrFail($id);

        // Route middleware accepts EITHER the full 'view-appointment-edit'
        // permission OR the narrower 'view-my-appointment-edit' one, so a
        // technician holding only the latter must additionally be the
        // appointment's actual assignee — otherwise they could edit/close any
        // appointment in the system just by incrementing this URL's ID.
        if (! auth()->user()->can('view-appointment-edit') && ! $this->userOwnsAppointment($appointment, auth()->user())) {
            abort(403, 'You do not have permission to edit this appointment.');
        }

        $types = AppointmentType::active()->get();
        $teamTypes = TeamType::active()->get();
        $subTypes = SubAppointmentType::where('appointment_type_id', $appointment->type_id)
            ->active()
            ->pluck('sub_type_name', 'id');
        $finalReasons = AppointmentFinalReason::all();
        // Get active sub departments - check common status values
        $subDepartments = SubDepartment::whereIn('sub_department_status', ['Active', 'active', '1', 1])->get();

        // If no active ones found, get all (fallback)
        if ($subDepartments->isEmpty()) {
            $subDepartments = SubDepartment::all();
        }

        return view(
            'appointment::appointment.edit_assigned',
            compact('appointment', 'types', 'teamTypes', 'subTypes', 'finalReasons', 'subDepartments')
        );
    }

    /**
     * Update the specified assigned appointment in storage.
     */
    public function updateAssigned(Request $request, $id)
    {
        \Log::info('Starting assigned appointment update process', ['appointment_id' => $id]);

        try {
            $appointment = Appointment::findOrFail($id);

            if (! auth()->user()->can('view-appointment-edit') && ! $this->userOwnsAppointment($appointment, auth()->user())) {
                abort(403, 'You do not have permission to edit this appointment.');
            }

            \Log::debug('Appointment found', ['appointment' => $appointment->toArray()]);

            // Log all incoming request data (password/token fields excluded —
            // this form never carries credentials, but keeping the exclusion
            // list here matches the standard used elsewhere in this codebase).
            \Log::debug('Request data received:', $request->except(['password', 'password_confirmation', '_token']));

            // Validate the request
            $validatedData = $request->validate([
                'status' => 'required',
                'optical_level' => 'nullable|numeric',
                'final_reason' => 'nullable|exists:appointment_final_reasons,id',
                'comment' => 'nullable|string',
                'sub_department_id' => 'nullable|exists:sub_departments,id',
                'rescheduled_date' => 'nullable|date',
                'rescheduled_time' => 'nullable',
                'reschedule_reason' => 'nullable|string',
                'closing_reason' => 'nullable|string',
                'cancelled_reason' => 'nullable|string',
                'notes' => 'nullable|string',
                'olt_id' => 'nullable|string|max:50',
                'slot_id' => 'nullable|string|max:50',
            ]);

            \Log::debug('Validation passed', ['validated_data' => $validatedData]);

            // Add editor information
            $validatedData['edited_by'] = auth()->id();
            \Log::debug('Added editor info', ['edited_by' => auth()->id()]);

            // Handle appointment completion
            if ($request->status === 'Scheduled-Closed' && $appointment->status !== 'Scheduled-Closed') {
                $validatedData['completed_date'] = now()->toDateString();
                $validatedData['completed_time'] = now()->toTimeString();
                $validatedData['closed_by'] = auth()->id();
                $validatedData['closed_at'] = now();
                \Log::info('Marking appointment as completed', [
                    'completed_date' => $validatedData['completed_date'],
                    'completed_time' => $validatedData['completed_time'],
                ]);
            }

            // Map final_reason to final_reason_id. Must be array_key_exists, not
            // isset: leaving the "Final Reason" dropdown on its blank placeholder
            // validates to null, and isset(null) is false — so this rename was
            // skipped and the literal 'final_reason' key (not a real column; only
            // final_reason_id exists) reached $appointment->save() below,
            // throwing "Unknown column 'final_reason'" on every status update
            // that didn't also pick a final reason, i.e. almost every one.
            if (array_key_exists('final_reason', $validatedData)) {
                $validatedData['final_reason_id'] = $validatedData['final_reason'];
                unset($validatedData['final_reason']);
            }

            // reschedule_reason is now a real column (see the migration that added
            // it). cancelled_reason was ALSO a real column all along (present
            // since the original create_appointments_table migration) with its
            // own form field on this page — it never needed stripping; both used
            // to be discarded here, silently dropping whatever the user typed.

            // Log original data
            $originalData = $appointment->getOriginal();
            \Log::debug('Original appointment data', $originalData);
            \Log::debug('New appointment data', $validatedData);

            // Find and log changed fields
            $changes = [];
            foreach ($validatedData as $key => $value) {
                if (! array_key_exists($key, $originalData) || $originalData[$key] != $value) {
                    $changes[$key] = [
                        'from' => $originalData[$key] ?? null,
                        'to' => $value,
                    ];
                }
            }

            \Log::info('Detected changes', ['changes' => $changes]);

            if (empty($changes)) {
                \Log::warning('No changes detected in the update request');

                return redirect()
                    ->back()
                    ->with('info', 'No changes were made.');
            }

            // Update the appointment
            // Fix for SQL error: ensure status is properly quoted by using the model's update method
            // instead of directly passing the array to update
            foreach ($validatedData as $key => $value) {
                $appointment->$key = $value;
            }
            $appointment->save();
            \Log::info('Appointment updated successfully', ['appointment_id' => $appointment->id]);

            // Record history if there are changes
            if (! empty($changes) && class_exists('\Modules\Appointment\Models\AppointmentHistory')) {
                try {
                    $history = \Modules\Appointment\Models\AppointmentHistory::create([
                        'appointment_id' => $appointment->id,
                        'ticket_id' => $appointment->appointment_ticket_id,
                        'escalation_ticket_id' => $appointment->escalation_ticket_id,
                        'status' => $appointment->status,
                        'action_by' => auth()->id(),
                        'sub_department_id' => $appointment->sub_department_id,
                        'team_type_id' => $appointment->team_type_id,
                        'sub_team_type_id' => $appointment->sub_team_type_id,
                        'comment' => $appointment->comment,
                        'rescheduled_date' => $appointment->rescheduled_date,
                        'rescheduled_time' => $appointment->rescheduled_time,
                        'final_reason_id' => $appointment->final_reason_id,
                        'olt_id' => $appointment->olt_id,
                        'slot_id' => $appointment->slot_id,
                        'action_description' => 'Appointment updated via assigned edit form',
                        'changes' => json_encode($changes),
                    ]);
                    \Log::debug('History record created', ['history_id' => $history->id]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create history record', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Create notifications using the notification service
            $this->notificationService->notifyAppointmentUpdated($appointment, auth()->user());

            // This save always succeeds via the "assigned" edit form, which
            // Field-Technician users reach from My Appointments and only hold
            // 'view-my-appointment-edit' for — not 'view-appointment-view'. A
            // redirect to the full show() page 403'd every technician immediately
            // after their update actually saved, masking success as a hard error.
            // Send them back to the same assigned-edit page instead, which they
            // are guaranteed to be allowed to see.
            return redirect()
                ->route('appointment.appointments.edit_assigned', $appointment->id)
                ->with('success', 'Appointment updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error updating appointment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'appointment_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the appointment. Please try again.')
                ->withErrors(['exception' => $e->getMessage()]);
        }
    }

    /**
     * Get sub types for the given appointment type
     *
     * @param  int  $type_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubTypes($type_id)
    {
        try {
            \Log::info('Fetching sub-types for type_id: '.$type_id);

            $subTypes = SubAppointmentType::where('appointment_type_id', $type_id)
                ->active()
                ->select('id', 'sub_type_name')
                ->get();

            \Log::info('Found sub-types:', $subTypes->toArray());

            return response()->json($subTypes);
        } catch (\Exception $e) {
            \Log::error('Error fetching sub-types: '.$e->getMessage());

            return response()->json(
                [
                    'error' => 'Failed to load sub-types. Please try again.',
                    'details' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Get active users belonging to a sub team type, for the "Assigned To"
     * cascade on the appointment edit form — mirrors
     * Modules\Outages\Http\Controllers\OutageController::getUsersBySubTeamType().
     *
     * @param  int  $subTeamTypeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersBySubTeamType($subTeamTypeId)
    {
        try {
            $users = User::where('sub_team_type_id', $subTeamTypeId)
                ->where('user_status', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            return response()->json([
                'success' => true,
                'users' => $users,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching users for sub team type: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to load users.'], 500);
        }
    }

    /**
     * Get slots for the given OLT
     *
     * @param  int  $olt_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSlots($olt_id)
    {
        try {
            \Log::info('Fetching slots for olt_id: '.$olt_id);

            $slots = OltSlot::where('olt_id', $olt_id)
                ->active()
                ->select('id', 'slot_number', 'slot_type')
                ->orderBy('slot_number')
                ->get();

            // Format the slots for display
            $formattedSlots = $slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'name' => "Slot {$slot->slot_number} ({$slot->slot_type})",
                    'slot_number' => $slot->slot_number,
                    'slot_type' => $slot->slot_type,
                ];
            });

            \Log::info('Found slots:', $formattedSlots->toArray());

            return response()->json($formattedSlots);
        } catch (\Exception $e) {
            \Log::error('Error fetching slots: '.$e->getMessage());

            return response()->json(
                [
                    'error' => 'Failed to load slots. Please try again.',
                    'details' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Infrastructure New - List all assigned appointments with status 'Escalated-Infrastructure'
     */
    public function infrastructureNew(Request $request)
    {
        try {
            // Get appointments with Infrastructure status that are assigned, including OLT relationship
            $appointments = Appointment::with(['assignedTeam', 'creator', 'olt'])
                ->where('status', 'escalated-infrastructure')
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('Infrastructure New - Found '.$appointments->count().' appointments');

            return view('appointment::site-visit.infrastructure.new', [
                'appointments' => $appointments,
            ]);
        } catch (\Exception $e) {
            \Log::error('Infrastructure New Error: '.$e->getMessage());

            return back()->with('error', 'Unable to load infrastructure appointments: '.$e->getMessage());
        }
    }

    /**
     * NOC New - List all assigned appointments with status 'Escalated-NOC'
     */
    public function nocNew(Request $request)
    {
        try {
            // Get appointments with NOC status that are assigned, including OLT relationship
            $appointments = Appointment::with(['assignedTeam', 'creator', 'olt'])
                ->where('status', 'escalated-noc')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('appointment::site-visit.noc.new', [
                'appointments' => $appointments,
            ]);
        } catch (\Exception $e) {
            \Log::error('NOC New Error: '.$e->getMessage());

            return back()->with('error', 'Unable to load NOC appointments: '.$e->getMessage());
        }
    }

    /**
     * Design New - List all assigned appointments with status 'Escalated-Design'
     */
    public function designNew(Request $request)
    {
        try {
            // Get appointments with Design status that are assigned, including OLT relationship
            $appointments = Appointment::with(['assignedTeam', 'creator', 'olt'])
                ->where('status', 'escalated-design')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('appointment::site-visit.design.new', [
                'appointments' => $appointments,
            ]);
        } catch (\Exception $e) {
            \Log::error('Design New Error: '.$e->getMessage());

            return back()->with('error', 'Unable to load design appointments: '.$e->getMessage());
        }
    }
}
