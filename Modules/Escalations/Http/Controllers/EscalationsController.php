<?php

namespace Modules\Escalations\Http\Controllers;

use App\Http\Controllers\OptimizedController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Entities\EscalationList;
use Modules\Escalations\App\Models\EscalationHistory;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentType;
use Modules\Appointment\Models\AppointmentHistory;
use Modules\Escalations\Entities\Category;
use Modules\Escalations\Entities\Subcategory;
use Modules\Escalations\Services\NotificationService;

class EscalationsController extends OptimizedController
{
  protected $notificationService;

  /**
   * Create a new controller instance.
   *
   * @param NotificationService $notificationService
   * @return void
   */
  public function __construct(NotificationService $notificationService)
  {
    parent::__construct();
    $this->notificationService = $notificationService;
  }

  /**
   * Display a escalationing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    // Get all escalation lists with their relationships
    $escalationLists = \Modules\Escalations\Entities\EscalationList::with([
        'category',
        'subcategory',
        'sub_department'
    ])
    ->orderBy('id', 'desc')
    ->get();

    // Get all sub departments that have escalations
    $subDepartments = \App\Models\SubDepartment::whereIn('id', $escalationLists->pluck('sub_department_id')->unique())
        ->get();

    // If no sub departments found, get all active sub departments
    if ($subDepartments->isEmpty()) {
        $subDepartments = \App\Models\SubDepartment::where('sub_department_status', 1)->get();
    }

    // Group escalation lists by sub_department_id
    $listsBySubDepartment = [];
    foreach ($subDepartments as $subDepartment) {
      $listsBySubDepartment[$subDepartment->id] = $escalationLists->where('sub_department_id', $subDepartment->id);
    }

    return view('escalations::escalation.index', compact('subDepartments', 'listsBySubDepartment', 'escalationLists'));
  }

  // Deactivate (soft inactivate)
  public function deactivate($id)
  {
    $escalation = Escalation::findOrFail($id);
    $escalation->status = 'deactivated';
    $escalation->save();
    return redirect()
      ->route('escalations.index')
      ->with('success', 'Escalation deactivated successfully.');
  }

  // Inactivate (soft inactive)
  public function inactivate($id)
  {
    $escalation = Escalation::findOrFail($id);
    $escalation->status = 'inactive';
    $escalation->save();
    return redirect()
      ->route('escalations.index')
      ->with('success', 'Escalation inactivated successfully.');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('escalations::escalation.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'account_number' => 'required|string|max:255',
      'description' => 'nullable|string',
      'priority' => 'required|in:low,medium,high',
      'status' => 'required|in:open,open-escalated,closed,closed-escalated,in_progress,resolved',
      'category_id' => 'required|string|max:255',
      'sub_category_id' => 'required|string|max:255',
      'source_id' => 'required|string|max:255',
    ]);

    try {
      $validated['escalation_id'] = $this->generateEscalationId();
      $validated['created_by'] = auth()->id();

      $escalation = Escalation::create($validated);

      // Create notification for the new escalation
      $this->notificationService->notifyCreation($escalation);

      if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
          'success' => true,
          'message' => 'Escalation created successfully.',
          'data' => $escalation,
        ]);
      }

      return redirect()
        ->route('escalations.index')
        ->with('success', 'Escalation created successfully.');
    } catch (\Exception $e) {
      if ($request->ajax() || $request->wantsJson()) {
        return response()->json(
          [
            'success' => false,
            'message' => 'Error creating escalation: ' . $e->getMessage(),
          ],
          500
        );
      }

      return back()
        ->withInput()
        ->withErrors(['error' => 'Error creating escalation. Please try again.']);
    }

  }

  /**
   * Display the specified resource.
   *
   * @param  \Modules\Escalations\Entities\Escalation  $escalation
   * @return \Illuminate\Http\Response
   */
  public function show(Escalation $escalation)
  {
    return view('escalations::escalation.show', compact('escalation'));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \Modules\Escalations\Entities\Escalation  $escalation
   * @return \Illuminate\Http\Response
   */
  public function edit(Escalation $escalation)
  {
    $categories = \Modules\Escalations\Entities\Category::all();
    $subcategories = $escalation->category_id
      ? \Modules\Escalations\Entities\Subcategory::where('category_id', $escalation->category_id)->get()
      : collect();

    $subDepartments = \App\Models\SubDepartment::all();

    // Debug: Log current escalation data
    \Log::info('Editing Escalation:', [
      'id' => $escalation->id,
      'appointment_type_id' => $escalation->appointment_type_id,
      'escalation_type' => $escalation->escalation_type,
    ]);

    // Get all active appointment types with their active sub-types
    $appointmentTypes = \Modules\Appointment\Models\AppointmentType::active()
      ->with([
        'subTypes' => function ($query) {
          $query->where('sub_type_status', 'Active');
        },
      ])
      ->get();

    // Debug: Log all available appointment types
    \Log::info(
      'Available Appointment Types:',
      $appointmentTypes
        ->map(function ($type) {
          return [
            'id' => $type->id,
            'name' => $type->type_name,
            'has_subtypes' => $type->subTypes->isNotEmpty(),
            'subtypes' => $type->subTypes->map(fn($st) => ['id' => $st->id, 'name' => $st->sub_type_name]),
          ];
        })
        ->toArray()
    );

    // If we have an appointment type ID, ensure it's included in the results
    if ($escalation->appointment_type_id) {
      $appointmentType = \Modules\Appointment\Models\AppointmentType::find($escalation->appointment_type_id);
      if ($appointmentType) {
        // If it's a parent type without subtypes, include it directly
        if ($appointmentType->subTypes->isEmpty() && !$appointmentTypes->contains('id', $appointmentType->id)) {
          $appointmentTypes->push($appointmentType);
        }
        // If it's a subtype, ensure its parent is included
        elseif ($appointmentType->parent_id) {
          $parentType = \Modules\Appointment\Models\AppointmentType::find($appointmentType->parent_id);
          if ($parentType && !$appointmentTypes->contains('id', $parentType->id)) {
            $parentType->load([
              'subTypes' => function ($q) use ($appointmentType) {
                $q->where('sub_type_status', 'Active')->orWhere('id', $appointmentType->id);
              },
            ]);
            $appointmentTypes->push($parentType);
          }
        }
      }
    }

    // Get OLTs and Slots from Outages module
    $olts = \Modules\Outages\Models\Olt::active()->orderBy('name')->get();
    $slots = collect(); // Will be populated via AJAX based on selected OLT

    // If escalation has an OLT selected, get its slots
    if ($escalation->olt_id) {
      $selectedOlt = \Modules\Outages\Models\Olt::find($escalation->olt_id);
      if ($selectedOlt) {
        $slots = $selectedOlt->slots()->active()->orderBy('slot_number')->get();
      }
    }

    return view('escalations::escalation.edit', [
      'escalation' => $escalation,
      'categories' => $categories,
      'subcategories' => $subcategories,
      'subDepartments' => $subDepartments,
      'appointmentTypes' => $appointmentTypes,
      'olts' => $olts,
      'slots' => $slots,
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Modules\Escalations\Entities\Escalation  $escalation
   * @return \Illuminate\Http\Response
   */

  public function update(Request $request, Escalation $escalation)
  {

    $request->all();

    If($request->escalation_type == 'no_appointment') {
      $validated = $request->validate([
        'account_number' => 'required|string|max:255',
        'ticket_id' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'category_id' => 'nullable|integer',
        'sub_category_id' => 'nullable|integer',
        'sub_department_id' => 'nullable|integer',
        'status' =>
        'required|in:Scheduled-Open,Scheduled-Closed,Escalated-Open,Escalated-Closed,In-Progress,Cancelled,Rescheduled',
        'escalation_type' => 'required|in:no_appointment,appointment',
      ]);

      // Update the escalation record (using the one from route model binding)
      $escalation->update([
        'status' => $request->status,
        'edited_by' => auth()->id(),
      ]);

      // Update the corresponding escalation list record
      $escalationList = EscalationList::where('ticket_id', $escalation->ticket_id)->first();
      if ($escalationList) {
        $escalationList->update([
          'status' => $request->status,
          'edited_by' => auth()->id(),
        ]);
      }

      // Create database notification for the editor
      $this->notificationService->createNotification(
          auth()->id(),
          $escalationList->id,
          'You just edited ticket ' . $escalationList->ticket_id,
          'info'
      );

      // Create database notification for the creator if different from editor
      if ($escalationList->created_by && $escalationList->created_by != auth()->id()) {
          $this->notificationService->createNotification(
              $escalationList->created_by,
              $escalationList->id,
              auth()->user()->name . ' just edited ticket ' . $escalationList->ticket_id,
              'info'
          );
      }

      // Create database notification for the assigned user if different from editor and creator
      if ($escalationList->assigned_to && $escalationList->assigned_to != auth()->id() && $escalationList->assigned_to != $escalationList->created_by) {
          $this->notificationService->createNotification(
              $escalationList->assigned_to,
              $escalationList->id,
              auth()->user()->name . ' just edited ticket ' . $escalationList->ticket_id,
              'info'
          );
      }

      // Set toast notification
      session()->flash('success', auth()->user()->name . ' just edited ticket ' . $escalationList->ticket_id);

      EscalationHistory::create([
        'account_number' => $request->account_number,
        'ticket_id' => $request->ticket_id,
        'description' => $request->description,
        'priority' => $request->priority,
        'category_id' => $request->category_id,
        'sub_category_id' => $request->sub_category_id,
        'sub_department_id' => $request->sub_department_id,
        'status' => $request->status,
        'escalation_type' => $request->escalation_type,
        'edited_by' => auth()->id(),
        'action_by' => auth()->id(),
        'appointment_type_id' => $request->appointment_type_id,
        'escalation_id' => $escalation->id,
    ]);


    return redirect()->route('escalations.index')->with('success', 'Escalation updated successfully');

    }elseif($request->escalation_type == 'appointment') {

       $validated = $request->validate([
        'account_number' => 'required|string|max:255',
        'ticket_id' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'category_id' => 'nullable|integer',
        'sub_category_id' => 'nullable|integer',
        'status' =>
          'required|in:Scheduled-Open,Scheduled-Closed,Escalated-Open,Escalated-Closed,In-Progress,Cancelled,Resolved',
        'appointment_id' => 'nullable|integer',
        'appointment_type_id' => [
          'nullable',
          function ($attribute, $value, $fail) {
            if ($value && !\Modules\Appointment\Models\SubAppointmentType::where('id', $value)->exists()) {
              $fail('The selected appointment subtype is invalid.');
            }
          },
        ],
        'olt_id' => 'nullable|string|max:50',
        'slot_id' => 'nullable|string|max:50',
      ]);

      $escalation = Escalation::where('ticket_id', $validated['ticket_id'])->first();
      $escalation->update([
        'status' => $request->status,
        'edited_by' => auth()->id(),
      ]);

      $escalationList = EscalationList::where('ticket_id', $validated['ticket_id'])->first();
      $escalationList->update([
        'status' => $request->status,
        'edited_by' => auth()->id(),
      ]);

      // Create database notification for the editor
      $this->notificationService->createNotification(
          auth()->id(),
          $escalationList->id,
          'You just edited ticket ' . $escalationList->ticket_id,
          'info'
      );

      // Create database notification for the creator if different from editor
      if ($escalationList->created_by && $escalationList->created_by != auth()->id()) {
          $this->notificationService->createNotification(
              $escalationList->created_by,
              $escalationList->id,
              auth()->user()->name . ' just edited ticket ' . $escalationList->ticket_id,
              'info'
          );
      }

      // Create database notification for the assigned user if different from editor and creator
      if ($escalationList->assigned_to && $escalationList->assigned_to != auth()->id() && $escalationList->assigned_to != $escalationList->created_by) {
          $this->notificationService->createNotification(
              $escalationList->assigned_to,
              $escalationList->id,
              auth()->user()->name . ' just edited ticket ' . $escalationList->ticket_id,
              'info'
          );
      }

      // Set toast notification
      session()->flash('success', auth()->user()->name . ' just edited ticket ' . $escalationList->ticket_id);

      // Create the history record using the correct escalation_id
      $historyData = [
        'account_number' => $request->account_number,
        'ticket_id' => $request->ticket_id,
        'description' => $request->description,
        'priority' => $request->priority,
        'category_id' => $request->category_id,
        'sub_category_id' => $request->sub_category_id,
        'sub_department_id' => $request->sub_department_id,
        'status' => $request->status,
        'escalation_type' => $request->escalation_type,
        'edited_by' => auth()->id(),
        'action_by' => auth()->id(),
        'escalation_id' => $escalation->id,
      ];

      // Only add fields if they exist in the request
      if ($request->has('appointment_type_id')) {
        $historyData['appointment_type_id'] = $request->appointment_type_id;
      }

      if ($request->has('appointment_id')) {
        $historyData['appointment_id'] = $request->appointment_id;
      }

      if ($request->has('olt_id')) {
        $historyData['olt_id'] = $request->olt_id;
      }

      if ($request->has('slot_id')) {
        $historyData['slot_id'] = $request->slot_id;
      }

      // Support appointment fields
      if ($request->has('support_date')) {
        $historyData['support_date'] = $request->support_date;
      }

      if ($request->has('support_time')) {
        $historyData['support_time'] = $request->support_time;
      }

      if ($request->has('support_address')) {
        $historyData['support_address'] = $request->support_address;
      }

      if ($request->has('support_notes')) {
        $historyData['support_notes'] = $request->support_notes;
      }

      // Shifting appointment fields
      if ($request->has('shifting_date')) {
        $historyData['shifting_date'] = $request->shifting_date;
      }

      if ($request->has('shifting_time')) {
        $historyData['shifting_time'] = $request->shifting_time;
      }

      if ($request->has('shifting_address')) {
        $historyData['shifting_address'] = $request->shifting_address;
      }

      if ($request->has('shifting_notes')) {
        $historyData['shifting_notes'] = $request->shifting_notes;
      }

      // Installation appointment fields
      if ($request->has('installation_date')) {
        $historyData['installation_date'] = $request->installation_date;
      }

      if ($request->has('installation_time')) {
        $historyData['installation_time'] = $request->installation_time;
      }

      if ($request->has('installation_address')) {
        $historyData['installation_address'] = $request->installation_address;
      }

      if ($request->has('installation_notes')) {
        $historyData['installation_notes'] = $request->installation_notes;
      }

      // WiFi Extender appointment fields
      if ($request->has('wifi_extender_date')) {
        $historyData['wifi_extender_date'] = $request->wifi_extender_date;
      }

      if ($request->has('wifi_extender_time')) {
        $historyData['wifi_extender_time'] = $request->wifi_extender_time;
      }

      if ($request->has('wifi_extender_address')) {
        $historyData['wifi_extender_address'] = $request->wifi_extender_address;
      }

      if ($request->has('wifi_extender_notes')) {
        $historyData['wifi_extender_notes'] = $request->wifi_extender_notes;
      }

      EscalationHistory::create($historyData);

    // Generate a unique appointment ticket ID with retry mechanism
    $maxAttempts = 10;
    $attempts = 0;
    $appointmentTicketId = null;
    $prefix = 'TKT'; // Default prefix if we can't determine type name

    while ($appointmentTicketId === null && $attempts < $maxAttempts) {
        try {
            // Get the appointment type name for the prefix
            if ($request->appointment_id) {
                $appointmentType = AppointmentType::find($request->appointment_id);
                if ($appointmentType && $appointmentType->type_name) {
                    $typeName = $appointmentType->type_name;
                    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $typeName), 0, 3)) ?: 'TKT';
                }
            }

            // Start a database transaction
            DB::beginTransaction();

            // Get the highest existing ticket number for this prefix
            $lastTicket = Appointment::where('appointment_ticket_id', 'LIKE', $prefix . '-%')
                ->orderByRaw('CAST(SUBSTRING_INDEX(appointment_ticket_id, "-", -1) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            // Generate the next ticket number
            $ticketNumber = 1;
            if ($lastTicket && $lastTicket->appointment_ticket_id) {
                $lastNumber = (int) substr($lastTicket->appointment_ticket_id, strrpos($lastTicket->appointment_ticket_id, '-') + 1);
                $ticketNumber = $lastNumber + 1;
            }

            // Generate the new ticket ID
            $appointmentTicketId = $prefix . '-' . $ticketNumber;

            // Double-check if the ticket ID already exists (shouldn't happen with proper locking)
            $exists = Appointment::where('appointment_ticket_id', $appointmentTicketId)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw new \Exception('Generated ticket ID already exists: ' . $appointmentTicketId);
            }

            // If we got here, we have a unique ticket ID
            DB::commit();
            break; // Exit the loop with a valid ticket ID

        } catch (\Exception $e) {
            DB::rollBack();
            $appointmentTicketId = null; // Reset to try again

            \Log::error('Failed to generate ticket ID', [
                'error' => $e->getMessage(),
                'attempt' => $attempts + 1,
                'prefix' => $prefix
            ]);

            // Increase attempt counter and add delay
            $attempts++;
            usleep(100000 * $attempts); // Exponential backoff
        }
    }

    // If we've exhausted all attempts, use a fallback method
    if ($appointmentTicketId === null) {
        // Fallback: Use timestamp to ensure uniqueness
        $appointmentTicketId = $prefix . '-' . time() . '-' . rand(100, 999);
        \Log::warning('Using fallback ticket ID generation', ['ticket_id' => $appointmentTicketId]);
    }

      // Determine which appointment type is being used and set the appropriate fields
      $appointmentData = [
          'account_number' => $request->account_number,
          'appointment_ticket_id' => $appointmentTicketId,
          'escalation_ticket_id' => $request->ticket_id,
          'appointment_id' => $request->appointment_id,
          'appointment_type_id' => $request->appointment_type_id,
          'description_notes' => $request->description,
          'priority' => $request->priority,
          'category_id' => $request->category_id,
          'sub_category_id' => $request->sub_category_id,
          'sub_department_id' => $request->sub_department_id,
          'status' => $request->status,
          'olt_id' => $request->olt_id,
          'slot_id' => $request->slot_id,
          'created_by' => auth()->id(),
      ];

      // Determine which appointment type is being used and set the appropriate fields
      if ($request->has('support_date') && $request->support_date) {
          $appointmentData['scheduled_date'] = $request->support_date;
          $appointmentData['scheduled_time'] = $request->support_time;
          $appointmentData['appointment_location'] = $request->support_address;
          $appointmentData['escalation_notes'] = $request->support_notes;
      } elseif ($request->has('shifting_date') && $request->shifting_date) {
          $appointmentData['scheduled_date'] = $request->shifting_date;
          $appointmentData['scheduled_time'] = $request->shifting_time;
          $appointmentData['appointment_location'] = $request->shifting_address;
          $appointmentData['escalation_notes'] = $request->shifting_notes;
      } elseif ($request->has('installation_date') && $request->installation_date) {
          $appointmentData['scheduled_date'] = $request->installation_date;
          $appointmentData['scheduled_time'] = $request->installation_time;
          $appointmentData['appointment_location'] = $request->installation_address;
          $appointmentData['escalation_notes'] = $request->installation_notes;
      } elseif ($request->has('wifi_extender_date') && $request->wifi_extender_date) {
          $appointmentData['scheduled_date'] = $request->wifi_extender_date;
          $appointmentData['scheduled_time'] = $request->wifi_extender_time;
          $appointmentData['appointment_location'] = $request->wifi_extender_address;
          $appointmentData['escalation_notes'] = $request->wifi_extender_notes;
      }

      $escalatedAppointment = Appointment::create($appointmentData);

    AppointmentHistory::create([
      'appointment_id' => $escalatedAppointment->id,
      'account_number' => $request->account_number,
      'ticket_id' => $appointmentTicketId,
      'escalation_ticket_id' => $request->ticket_id,
      'appointment_type_id' => $request->appointment_type_id,
      // Removed 'description_notes' as it doesn't exist in the database table
      'priority' => $request->priority,
      'category_id' => $request->category_id,
      'sub_category_id' => $request->sub_category_id,
      'sub_department_id' => $request->sub_department_id,
      'status' => $request->status,
      'olt_id' => $request->olt_id,
      'slot_id' => $request->slot_id,
      'action_by' => auth()->id(),
      'edited_by' => auth()->id(),
      'created_by' => auth()->id(),
  ]);

   return redirect()->route('escalations.index')->with('success', 'Escalation updated successfully');

    }

  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \Modules\Escalations\Entities\Escalation  $escalation
   * @return \Illuminate\Http\Response
   */
  public function destroy(Escalation $escalation)
  {
    $escalation->delete();

    return redirect()
      ->route('escalations.index')
      ->with('success', 'Escalation deleted successfully');
  }

  /**
   * Get subcategories for a category (AJAX)
   *
   * @param  int  $categoryId
   * @return \Illuminate\Http\Response
   */
  public function getSubcategories($categoryId)
  {
    $subcategories = \Modules\Escalations\Entities\Subcategory::where('category_id', $categoryId)
      ->orderBy('sub_category_name')
      ->get(['id', 'sub_category_name']);

    return response()->json($subcategories);
  }

  /**
   * Get slots for an OLT (AJAX)
   *
   * @param  int  $oltId
   * @return \Illuminate\Http\Response
   */
  public function getOltSlots($oltId)
  {
    try {
      $olt = \Modules\Outages\Models\Olt::find($oltId);

      if (!$olt) {
        return response()->json([
          'success' => false,
          'message' => 'OLT not found'
        ], 404);
      }

      $slots = $olt->slots()
        ->active()
        ->orderBy('slot_number')
        ->get(['id', 'slot_number', 'slot_type', 'status']);

      return response()->json([
        'success' => true,
        'slots' => $slots->map(function($slot) {
          return [
            'id' => $slot->id,
            'slot_number' => $slot->slot_number,
            'display_name' => "Slot {$slot->slot_number} ({$slot->slot_type})",
            'slot_type' => $slot->slot_type,
            'status' => $slot->status
          ];
        })
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error fetching slots: ' . $e->getMessage()
      ], 500);
    }
  }

  // Method to generate unique escalation ID
  private function generateEscalationId()
  {
    // Get the last escalation ID from database
    $lastEscalation = Escalation::orderBy('id', 'desc')->first();

    if (!$lastEscalation) {
      // First escalation
      return 'ESC-1';
    }

    // Extract number from last escalation_id (e.g., "ESC-5" -> 5)
    $lastNumber = (int) str_replace('ESC-', '', $lastEscalation->escalation_id);

    // Increment and return new ID
    return 'ESC-' . ($lastNumber + 1);
  }

  // Alternative method using database transactions for better concurrency handling
  private function generateEscalationIdSafe()
  {
    return DB::transaction(function () {
      // Lock the table to prevent race conditions
      $lastEscalation = Escalation::lockForUpdate()
        ->orderBy('id', 'desc')
        ->first();

      if (!$lastEscalation) {
        return 'ESC-1';
      }

      $lastNumber = (int) str_replace('ESC-', '', $lastEscalation->escalation_id);
      return 'ESC-' . ($lastNumber + 1);
    });
  }
}
