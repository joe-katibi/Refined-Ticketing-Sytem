<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Outages\Models\Outage;
use Modules\Outages\Models\OutageHistory;
use App\Models\TicketPhoto;
use Illuminate\Support\Facades\Log;

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

            $outages = Outage::with(['teamType', 'subTeamType'])
                ->where('assigned_team_id', $user->team_type_id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            Log::info('MobileOutageController::index - Query executed', [
                'outages_count' => $outages->count(),
                'total_outages' => $outages->total(),
                'user_team_type_id' => $user->team_type_id
            ]);

            // Transform outages to ensure null values are handled for Flutter
            $transformedOutages = collect($outages->items())->map(function ($outage) {
                return [
                    'id' => $outage->id,
                    'outage_ticket_id' => $outage->outage_ticket_id ?? '',
                    'customer_name' => $outage->customer_name ?? '',
                    'customer_phone' => $outage->customer_phone ?? '',
                    'location' => $outage->location ?? '',
                    'description' => $outage->description ?? '',
                    'priority' => $outage->priority ?? '',
                    'status' => $outage->status ?? '',
                    'assigned_team_id' => $outage->assigned_team_id ?? '',
                    'estimated_resolution' => $outage->estimated_resolution ?? '',
                    'progress_notes' => $outage->progress_notes ?? '',
                    'resolution_notes' => $outage->resolution_notes ?? '',
                    'created_at' => $outage->created_at ? $outage->created_at->toISOString() : '',
                    'updated_at' => $outage->updated_at ? $outage->updated_at->toISOString() : '',
                    'team_type' => [
                        'id' => $outage->teamType->id ?? '',
                        'type_name' => $outage->teamType->type_name ?? '',
                        'description' => $outage->teamType->description ?? '',
                        'status' => $outage->teamType->status ?? '',
                    ],
                    'sub_team_type' => $outage->subTeamType ? [
                        'id' => $outage->subTeamType->id ?? '',
                        'sub_type_name' => $outage->subTeamType->sub_type_name ?? '',
                        'sub_type_description' => $outage->subTeamType->sub_type_description ?? '',
                        'sub_type_status' => $outage->subTeamType->sub_type_status ?? '',
                    ] : null,
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
        
        $outage = Outage::with(['teamType', 'subTeamType', 'photos'])
            ->where('assigned_team_id', $user->team_type_id)
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
        
        $outage = Outage::where('assigned_team_id', $user->team_type_id)
            ->findOrFail($id);

        $request->validate([
            'status' => 'nullable|string',
            'progress_notes' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
            'estimated_resolution' => 'nullable|date',
        ]);

        $oldData = $outage->toArray();

        // Update outage
        $outage->update($request->only([
            'status', 'progress_notes', 'resolution_notes', 'estimated_resolution'
        ]));

        // Create history record
        OutageHistory::create([
            'outage_id' => $outage->id,
            'user_id' => $user->id,
            'action' => 'updated',
            'old_values' => json_encode($oldData),
            'new_values' => json_encode($outage->fresh()->toArray()),
            'notes' => $request->progress_notes,
        ]);

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
        
        $outage = Outage::where('assigned_team_id', $user->team_type_id)
            ->findOrFail($id);

        $history = OutageHistory::with('user')
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
        
        $outage = Outage::where('assigned_team_id', $user->team_type_id)
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
        
        $outage = Outage::where('assigned_team_id', $user->team_type_id)
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
        
        $totalOutages = Outage::where('assigned_team_id', $user->team_type_id)->count();
        $resolvedOutages = Outage::where('assigned_team_id', $user->team_type_id)
            ->where('status', 'noc-restore-confirmed')->count();
        $activeOutages = Outage::where('assigned_team_id', $user->team_type_id)
            ->whereNotIn('status', ['noc-restore-confirmed', 'support-closed'])->count();
        
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
