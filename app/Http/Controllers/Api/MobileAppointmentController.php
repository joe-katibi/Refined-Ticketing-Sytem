<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentHistory;
use App\Models\TicketPhoto;
use Illuminate\Support\Facades\Log;

/**
 * Every method here previously filtered on
 * `Appointment::where('assigned_team_id', $user->team_type_id)` —
 * comparing appointments.assigned_team_id (an FK to `teams`) against
 * $user->team_type_id (an FK to the unrelated `team_types` table). That
 * condition can only be true by numeric coincidence, so a field technician's
 * "My Appointments" list, appointment detail, update, history, and photo
 * endpoints were all effectively broken — real assigned work would not
 * appear, or the wrong appointment could match. Switched to the per-user
 * `assigned_to` column (added for the FIFO engine) throughout.
 */
class MobileAppointmentController extends Controller
{
    /**
     * Get assigned appointments for field technician
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            Log::info('MobileAppointmentController::index - User authenticated', [
                'user_id' => $user->id,
                'user_team_type_id' => $user->team_type_id,
                'user_email' => $user->email
            ]);

            // Simplified query without relationships to avoid timeout
            $appointments = Appointment::where('assigned_to', $user->id)
                ->orderBy('scheduled_date', 'desc')
                ->paginate(20);

            Log::info('MobileAppointmentController::index - Query executed', [
                'appointments_count' => $appointments->count(),
                'total_appointments' => $appointments->total(),
                'user_team_type_id' => $user->team_type_id
            ]);

            // Log each appointment data to check for null values
            foreach ($appointments->items() as $appointment) {
                Log::info('MobileAppointmentController::index - Appointment data', [
                    'appointment_id' => $appointment->id,
                    'account_number' => $appointment->account_number,
                    'customer_name' => $appointment->customer_name ?? '',
                    'scheduled_date' => $appointment->scheduled_date,
                    'status' => $appointment->status,
                    'assigned_team_id' => $appointment->assigned_team_id,
                    'team_type' => $appointment->teamType ? $appointment->teamType->type_name : null,
                    'sub_team_type' => $appointment->subTeamType ? $appointment->subTeamType->name : null
                ]);
            }

            // Transform appointments to ensure null values are handled for Flutter
            $transformedAppointments = collect($appointments->items())->map(function ($appointment) {
                return [
                    'id' => $appointment->id ?? 0,
                    'account_number' => $appointment->account_number ?? '',
                    'appointment_ticket_id' => $appointment->appointment_ticket_id ?? '',
                    'escalation_ticket_id' => $appointment->escalation_ticket_id ?? '',
                    'outage_ticket_id' => $appointment->outage_ticket_id ?? '',
                    'appointment_id' => $appointment->appointment_id ?? '',
                    'appointment_type_id' => $appointment->appointment_type_id ?? '',
                    'sub_department_id' => $appointment->sub_department_id ?? '',
                    'category_id' => $appointment->category_id ?? '',
                    'sub_category_id' => $appointment->sub_category_id ?? '',
                    'description_notes' => $appointment->description_notes ?? '',
                    'priority' => $appointment->priority ?? '',
                    'escalation_type' => $appointment->escalation_type ?? '',
                    'status' => $appointment->status ?? '',
                    'scheduled_date' => $appointment->scheduled_date ?? '',
                    'scheduled_time' => $appointment->scheduled_time ?? '',
                    'completed_date' => $appointment->completed_date ?? '',
                    'completed_time' => $appointment->completed_time ?? '',
                    'assigned_team_id' => $appointment->assigned_team_id ?? 0,
                    'escalated_team_id' => $appointment->escalated_team_id ?? '',
                    'team_type_id' => $appointment->team_type_id ?? 0,
                    'sub_team_type_id' => $appointment->sub_team_type_id ?? 0,
                    'closed_by' => $appointment->closed_by ?? '',
                    'notes_created' => $appointment->notes_created ?? '',
                    'notes_closed' => $appointment->notes_closed ?? '',
                    'closing_reason' => $appointment->closing_reason ?? '',
                    'escalation_reason' => $appointment->escalation_reason ?? '',
                    'escalation_notes' => $appointment->escalation_notes ?? '',
                    'appointment_type' => $appointment->appointment_type ?? '',
                    'appointment_status' => $appointment->appointment_status ?? '',
                    'appointment_location' => $appointment->appointment_location ?? '',
                    'appointment_venue' => $appointment->appointment_venue ?? '',
                    'olt_id' => $appointment->olt_id ?? '',
                    'slot_id' => $appointment->slot_id ?? '',
                    'final_reason_id' => $appointment->final_reason_id ?? '',
                    'optical_level' => $appointment->optical_level ?? '',
                    'custom_confirmation' => $appointment->custom_confirmation ?? 0,
                    'notes' => $appointment->notes ?? '',
                    'comment' => $appointment->comment ?? '',
                    'rescheduled_date' => $appointment->rescheduled_date ?? '',
                    'rescheduled_time' => $appointment->rescheduled_time ?? '',
                    'created_by' => $appointment->created_by ?? 0,
                    'edited_by' => $appointment->edited_by ?? '',
                    'closed_at' => $appointment->closed_at ?? '',
                    'escalated_at' => $appointment->escalated_at ?? '',
                    'created_at' => $appointment->created_at ? $appointment->created_at->toISOString() : '',
                    'updated_at' => $appointment->updated_at ? $appointment->updated_at->toISOString() : '',
                    'deleted_at' => $appointment->deleted_at ?? '',
                    'team_type' => $appointment->teamType ? [
                        'id' => $appointment->teamType->id ?? 0,
                        'type_name' => $appointment->teamType->type_name ?? '',
                        'description' => $appointment->teamType->description ?? '',
                        'status' => $appointment->teamType->status ?? '',
                        'department_id' => $appointment->teamType->department_id ?? 0,
                        'sub_department_id' => $appointment->teamType->sub_department_id ?? 0,
                        'created_by' => $appointment->teamType->created_by ?? 0,
                        'edited_by' => $appointment->teamType->edited_by ?? '',
                        'created_at' => $appointment->teamType->created_at ?? '',
                        'updated_at' => $appointment->teamType->updated_at ?? '',
                        'status_badge' => $appointment->teamType->status_badge ?? '',
                    ] : [
                        'id' => 0,
                        'type_name' => '',
                        'description' => '',
                        'status' => '',
                        'department_id' => 0,
                        'sub_department_id' => 0,
                        'created_by' => 0,
                        'edited_by' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'status_badge' => '',
                    ],
                    'sub_team_type' => $appointment->subTeamType ? [
                        'id' => $appointment->subTeamType->id ?? 0,
                        'sub_type_name' => $appointment->subTeamType->sub_type_name ?? '',
                        'sub_type_description' => $appointment->subTeamType->sub_type_description ?? '',
                        'sub_type_status' => $appointment->subTeamType->sub_type_status ?? '',
                        'team_type_id' => $appointment->subTeamType->team_type_id ?? 0,
                        'department_id' => $appointment->subTeamType->department_id ?? 0,
                        'sub_department_id' => $appointment->subTeamType->sub_department_id ?? 0,
                        'created_by' => $appointment->subTeamType->created_by ?? 0,
                        'edited_by' => $appointment->subTeamType->edited_by ?? '',
                        'created_at' => $appointment->subTeamType->created_at ?? '',
                        'updated_at' => $appointment->subTeamType->updated_at ?? '',
                        'deleted_at' => $appointment->subTeamType->deleted_at ?? '',
                    ] : [
                        'id' => 0,
                        'sub_type_name' => '',
                        'sub_type_description' => '',
                        'sub_type_status' => '',
                        'team_type_id' => 0,
                        'department_id' => 0,
                        'sub_department_id' => 0,
                        'created_by' => 0,
                        'edited_by' => '',
                        'created_at' => '',
                        'updated_at' => '',
                        'deleted_at' => '',
                    ],
                ];
            });

            return response()->json([
                'appointments' => $transformedAppointments,
                'pagination' => [
                    'current_page' => $appointments->currentPage(),
                    'last_page' => $appointments->lastPage(),
                    'per_page' => $appointments->perPage(),
                    'total' => $appointments->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('MobileAppointmentController::index - Error occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user() ? $request->user()->id : null
            ]);

            return response()->json([
                'error' => 'Failed to load appointments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific appointment details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $appointment = Appointment::with(['teamType', 'subTeamType', 'appointmentStatus', 'photos'])
            ->where('assigned_to', $user->id)
            ->findOrFail($id);

        return response()->json([
            'appointment' => $appointment
        ]);
    }

    /**
     * Update appointment status and details
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $appointment = Appointment::where('assigned_to', $user->id)
            ->findOrFail($id);

        // Was validating/writing 'notes', 'completion_notes', 'rescheduled_date',
        // 'rescheduled_time' — none of these are real columns on appointments
        // (fillable has notes_created/notes_closed/scheduled_date/scheduled_time
        // instead), so $appointment->update() silently dropped all of them:
        // a technician's saved notes or reschedule never actually persisted,
        // with no error surfaced anywhere.
        $request->validate([
            'status' => 'nullable|string',
            'notes_created' => 'nullable|string',
            'notes_closed' => 'nullable|string',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable',
        ]);

        $previousStatus = $appointment->status;

        // Update appointment
        $appointment->update($request->only([
            'status', 'notes_created', 'notes_closed',
            'scheduled_date', 'scheduled_time'
        ]));

        // Create history record. Was writing 'user_id', 'action', 'old_values',
        // 'new_values', 'notes' — none of which are fillable on this model (see
        // AppointmentHistory::$fillable), so mass-assignment silently dropped
        // all of them, leaving only appointment_id — and since 'status' (which
        // IS fillable, and NOT NULL with no DB default) was never included
        // either, every update() call failed with a SQL error, on top of never
        // actually recording anything useful.
        AppointmentHistory::create([
            'appointment_id' => $appointment->id,
            'status' => $appointment->status ?? $previousStatus,
            'action_by' => $user->id,
            'action_description' => 'Updated via mobile app (was: ' . $previousStatus . ')',
            'internal_notes' => $request->input('notes_created'),
        ]);

        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment->fresh()
        ]);
    }

    /**
     * Get appointment history
     */
    public function history(Request $request, $id)
    {
        $user = $request->user();

        $appointment = Appointment::where('assigned_to', $user->id)
            ->findOrFail($id);

        $history = AppointmentHistory::with('user')
            ->where('appointment_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'history' => $history
        ]);
    }

    /**
     * Upload photos for appointment
     */
    public function uploadPhoto(Request $request, $id)
    {
        $user = $request->user();

        $appointment = Appointment::where('assigned_to', $user->id)
            ->findOrFail($id);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'photo_type' => 'required|in:onu_photo,atb_photo,speed_test_photo',
            'notes' => 'nullable|string|max:500',
        ]);

        // Store the photo
        $photoPath = $request->file('photo')->store('appointment_photos', 'public');

        // Create photo record
        $photo = TicketPhoto::create([
            'ticket_id' => $appointment->id,
            'ticket_type' => 'appointment',
            'uploaded_by' => $user->id,
            'notes' => $request->notes,
            $request->photo_type => $photoPath,
        ]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo' => $photo
        ]);
    }

    /**
     * Get appointment photos
     */
    public function photos(Request $request, $id)
    {
        $user = $request->user();

        $appointment = Appointment::where('assigned_to', $user->id)
            ->findOrFail($id);

        $photos = TicketPhoto::with('uploader')
            ->where('ticket_id', $id)
            ->where('ticket_type', 'appointment')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'photos' => $photos
        ]);
    }

    /**
     * Get user's performance metrics
     */
    public function performance(Request $request)
    {
        $user = $request->user();

        // Was comparing appointments.assigned_team_id (an FK to teams) against
        // $user->team_type_id (an FK to the unrelated team_types table) — this
        // condition could only match by numeric coincidence, so totals were
        // effectively always 0. Also compared against status 'pending', which
        // doesn't exist in this app's real status vocabulary (scheduled-open,
        // in-progress, completed, cancelled, ...). Switched to the per-user
        // assigned_to column, which is what "my performance" should mean anyway.
        $totalAppointments = Appointment::where('assigned_to', $user->id)->count();
        $completedAppointments = Appointment::where('assigned_to', $user->id)
            ->where('status', 'completed')->count();
        $pendingAppointments = Appointment::where('assigned_to', $user->id)
            ->whereIn('status', ['scheduled-open', 'scheduled-assigned-team', 'in-progress'])->count();

        $completionRate = $totalAppointments > 0 ?
            round(($completedAppointments / $totalAppointments) * 100, 2) : 0;

        return response()->json([
            'performance' => [
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'pending_appointments' => $pendingAppointments,
                'completion_rate' => $completionRate,
            ]
        ]);
    }
}
