<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Outages\Models\Outage;
use Modules\Outages\Models\OutageActivity;
use Modules\Outages\Models\OutageProgress;
use App\Models\TicketPhoto;
use Illuminate\Support\Facades\Log;

/**
 * `use Modules\Outages\Models\OutageHistory` referenced a class that does not
 * exist anywhere in the codebase — no such file, no such table. update() and
 * history() below fatal-errored ("Class not found") the instant either was
 * called; the mobile outage-update flow was completely non-functional, not
 * just subtly wrong. The real audit trail model is OutageActivity
 * (outage_activities table), already used by the web OutageController.
 */
class MobileOutageController extends Controller
{
    /**
     * Get assigned outages for field technician
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            Log::info('MobileOutageController::index - User authenticated', [
                'user_id' => $user->id,
                'user_team_type_id' => $user->team_type_id,
                'user_email' => $user->email
            ]);

            // Was Outage::with(['teamType', 'subTeamType']) — neither relation
            // exists on this model (the real names are assignedTeam() and
            // assignedSubTeamType()), so every call to this endpoint threw
            // "Call to undefined relationship [teamType]" and 500'd before
            // reaching any of the actual response logic below.
            $outages = Outage::where('assigned_to', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            Log::info('MobileOutageController::index - Query executed', [
                'outages_count' => $outages->count(),
                'total_outages' => $outages->total(),
                'user_team_type_id' => $user->team_type_id
            ]);

            // Transform outages to ensure null values are handled for Flutter.
            // Was built entirely from fictional columns (outage_ticket_id,
            // customer_name, customer_phone, location, estimated_resolution,
            // progress_notes) that don't exist on the real Outage model — every
            // one of those resolved to null via Eloquent's magic __get and then
            // to '' via the ?? fallback, regardless of real data. Outages have
            // no per-customer contact fields at all; they're infrastructure
            // incidents (see total_customers_affected).
            $transformedOutages = collect($outages->items())->map(function ($outage) {
                return [
                    'id' => $outage->id,
                    'ticket_number' => $outage->ticket_number ?? '',
                    'title' => $outage->title ?? '',
                    'description' => $outage->description ?? '',
                    'priority' => $outage->priority ?? '',
                    'impact' => $outage->impact ?? '',
                    'urgency' => $outage->urgency ?? '',
                    'status' => $outage->status ?? '',
                    'assigned_team_id' => $outage->assigned_team_id,
                    'total_customers_affected' => $outage->total_customers_affected ?? 0,
                    'resolution_notes' => $outage->resolution_notes ?? '',
                    'root_cause' => $outage->root_cause ?? '',
                    'created_at' => $outage->created_at ? $outage->created_at->toISOString() : '',
                    'updated_at' => $outage->updated_at ? $outage->updated_at->toISOString() : '',
                ];
            });

            return response()->json([
                'outages' => $transformedOutages,
                'pagination' => [
                    'current_page' => $outages->currentPage(),
                    'last_page' => $outages->lastPage(),
                    'per_page' => $outages->perPage(),
                    'total' => $outages->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('MobileOutageController::index - Error occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user() ? $request->user()->id : null
            ]);

            return response()->json([
                'error' => 'Failed to load outages',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific outage details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $outage = Outage::with(['assignedTeam', 'assignedSubTeamType', 'photos'])
            ->where('assigned_to', $user->id)
            ->findOrFail($id);

        return response()->json([
            'outage' => $outage
        ]);
    }

    /**
     * Update outage status and details
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        $outage = Outage::where('assigned_to', $user->id)
            ->findOrFail($id);

        // Was validating/writing 'progress_notes' and 'estimated_resolution' —
        // neither is a real column on outages (only resolution_notes is).
        // 'progress_notes' now creates a proper OutageProgress entry, matching
        // how the web OutageController::storeProgress() records progress.
        $request->validate([
            'status' => 'nullable|string',
            'progress_notes' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
        ]);

        $previousStatus = $outage->status;

        $outage->update($request->only(['status', 'resolution_notes']));
        $outage->refresh();

        if ($request->filled('status') && $previousStatus !== $outage->status) {
            OutageActivity::logStatusChange($outage, $previousStatus, $outage->status, $user, $request->input('progress_notes'));
        }

        if ($request->filled('progress_notes')) {
            OutageProgress::create([
                'outage_id' => $outage->id,
                'user_id' => $user->id,
                'status' => $outage->status,
                'notes' => $request->input('progress_notes'),
                'created_by' => $user->id,
            ]);
            OutageActivity::logProgressAdded($outage, $user, $request->input('progress_notes'));
        }

        return response()->json([
            'message' => 'Outage updated successfully',
            'outage' => $outage->fresh()
        ]);
    }

    /**
     * Get outage history
     */
    public function history(Request $request, $id)
    {
        $user = $request->user();
        
        $outage = Outage::where('assigned_to', $user->id)
            ->findOrFail($id);

        $history = OutageActivity::with('user')
            ->where('outage_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'history' => $history
        ]);
    }

    /**
     * Upload photos for outage
     */
    public function uploadPhoto(Request $request, $id)
    {
        $user = $request->user();
        
        $outage = Outage::where('assigned_to', $user->id)
            ->findOrFail($id);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'photo_type' => 'required|in:fdt_photo,mdu_photo,pre_outage_photo,post_outage_photo',
            'notes' => 'nullable|string|max:500',
        ]);

        // Store the photo
        $photoPath = $request->file('photo')->store('outage_photos', 'public');

        // Create photo record
        $photo = TicketPhoto::create([
            'ticket_id' => $outage->id,
            'ticket_type' => 'outage',
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
     * Get outage photos
     */
    public function photos(Request $request, $id)
    {
        $user = $request->user();
        
        $outage = Outage::where('assigned_to', $user->id)
            ->findOrFail($id);

        $photos = TicketPhoto::with('uploader')
            ->where('ticket_id', $id)
            ->where('ticket_type', 'outage')
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

        // Same bug as MobileAppointmentController@performance: compared
        // outages.assigned_team_id (a team FK) against $user->team_type_id (an
        // unrelated table's FK) — switched to per-user assigned_to.
        $totalOutages = Outage::where('assigned_to', $user->id)->count();
        $resolvedOutages = Outage::where('assigned_to', $user->id)
            ->whereIn('status', ['infra-resolved', 'noc-restore-confirmed', 'support-closed'])->count();
        $activeOutages = Outage::where('assigned_to', $user->id)
            ->whereNotIn('status', ['infra-resolved', 'noc-restore-confirmed', 'support-closed'])->count();
        
        $resolutionRate = $totalOutages > 0 ? 
            round(($resolvedOutages / $totalOutages) * 100, 2) : 0;

        return response()->json([
            'performance' => [
                'total_outages' => $totalOutages,
                'resolved_outages' => $resolvedOutages,
                'active_outages' => $activeOutages,
                'resolution_rate' => $resolutionRate,
            ]
        ]);
    }
}
