<?php

namespace Modules\Escalations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Entities\EscalationList;
use Modules\Escalations\App\Models\EscalationHistory;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentType;
use Modules\Appointment\Models\AppointmentHistory;
use Modules\Escalations\Entities\Category;
use Modules\Escalations\Entities\SubCategory;
use Modules\Escalations\Services\NotificationService;

class EscalationsController extends Controller
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
    $this->notificationService = $notificationService;
  }

  /**
   * Display a escalationing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    // Fetch unique SubDepartments from EscalationList
    $subDepartments = \App\Models\SubDepartment::whereIn(
      'id',
      \Modules\Escalations\Entities\EscalationList::pluck('sub_department_id')->unique()
    )->get();
    // Group EscalationLists by sub_department_id
    $listsBySubDepartment = [];
    foreach ($subDepartments as $subDepartment) {
      $listsBySubDepartment[$subDepartment->id] = \Modules\Escalations\Entities\EscalationList::with([
        'category',
        'subcategory',
        'sub_department',
      ])
        ->where('sub_department_id', $subDepartment->id)
        ->orderBy('ticket_id', 'desc')
        ->get();
    }
    return view('escalations::escalation.index', compact('subDepartments', 'listsBySubDepartment'));
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
      ? \Modules\Escalations\Entities\SubCategory::where('category_id', $escalation->category_id)->get()
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

    return view('escalations::escalation.edit', [
      'escalation' => $escalation,
      'categories' => $categories,
      'subcategories' => $subcategories,
      'subDepartments' => $subDepartments,
      'appointmentTypes' => $appointmentTypes,
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
        'escalation_id' => $escalationList->id,
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
        'appointment_id' => $request->appointment_id,
        'escalation_id' => $escalation->id,
    ]);

    // Generate a unique appointment ticket ID with retry mechanism
    $maxAttempts = 10;
    $attempts = 0;
    $appointmentTicketId = null;

    try {
        $typeName = AppointmentType::findOrFail($request->appointment_id)->type_name;
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $typeName), 0, 3)) ?: 'TKT';

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

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to generate ticket ID', [
            'error' => $e->getMessage(),
            'attempt' => $attempts + 1,
            'prefix' => $prefix ?? 'N/A'
        ]);

        // If we've exhausted all attempts, try a fallback method
        if ($attempts >= $maxAttempts) {
            // Fallback: Use timestamp to ensure uniqueness
            $appointmentTicketId = $prefix . '-' . time() . '-' . rand(100, 999);
            \Log::warning('Using fallback ticket ID generation', ['ticket_id' => $appointmentTicketId]);
        } else {
            // Retry with exponential backoff
            $attempts++;
            usleep(100000 * $attempts); // Increase delay with each retry
        }
    }

      $escalatedAppointment =  Appointment::create([
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
        'scheduled_date' => $request->support_date,
        'scheduled_date' => $request->installation_date,
        'scheduled_date' => $request->shifting_date,
        'scheduled_date' => $request->installation_date,
        'scheduled_date' => $request->wifi_extender_date,
        'scheduled_time' => $request->support_time,
        'scheduled_time' => $request->shifting_time,
        'scheduled_time' => $request->installation_time,
        'scheduled_time' => $request->wifi_extender_time,
        'appointment_location' => $request->support_address,
        'appointment_location' => $request->shifting_address,
        'appointment_location' => $request->installation_address,
        'appointment_location' => $request->wifi_extender_address,
        'escalation_notes' => $request->support_notes,
        'escalation_notes' => $request->shifting_notes,
        'escalation_notes' => $request->installation_notes,
        'escalation_notes' => $request->wifi_extender_notes,
        'created_by' => auth()->id(),
    ]);

    AppointmentHistory::create([
      'appointment_id' => $escalatedAppointment->id,
      'account_number' => $request->account_number,
      'ticket_id' => $appointmentTicketId,
      'escalation_ticket_id' => $request->ticket_id,
      'appointment_id' => $request->appointment_id,
      'appointment_type_id' => $request->appointment_type_id,
      'description_notes' => $request->description,
      'priority' => $request->priority,
      'category_id' => $request->category_id,
      'sub_category_id' => $request->sub_category_id,
      'sub_department_id' => $request->sub_department_id,
      'status' => $request->status,
      'action_by' => auth()->id(),
      'edited_by' => auth()->id(),
      'created_by' => auth()->id(),
      'created_at' => now(),
      'changed_at' => now(),
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
    $subcategories = \Modules\Escalations\Entities\SubCategory::where('category_id', $categoryId)
      ->orderBy('sub_category_name')
      ->get(['id', 'sub_category_name']);

    return response()->json($subcategories);
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
