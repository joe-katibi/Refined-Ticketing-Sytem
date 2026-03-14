<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    /**
     * Display a listing of the partners.
     */
    public function index()
    {
        $partners = Partner::with(['creator', 'editor'])
            ->latest()
            ->paginate(10);
            
        return view('partners.index', compact('partners'));
    }

    /**
     * Store a newly created partner in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'partner_name' => 'required|string|max:255|unique:partners,partner_name',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            $partner = Partner::create([
                'partner_name' => $validated['partner_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'created_by' => Auth::id(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Partner created successfully.',
                    'data' => $partner
                ]);
            }

            return redirect()->route('partners.index')
                ->with('success', 'Partner created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the partner.',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while creating the partner.');
        }
    }

    /**
     * Update the specified partner in storage.
     */
    public function update(Request $request, Partner $partner)
    {
        try {
            $validated = $request->validate([
                'partner_name' => 'required|string|max:255|unique:partners,partner_name,' . $partner->id,
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            $partner->update([
                'partner_name' => $validated['partner_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'edited_by' => Auth::id(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Partner updated successfully.',
                    'data' => $partner
                ]);
            }

            return redirect()->route('partners.index')
                ->with('success', 'Partner updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the partner.',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while updating the partner.');
        }
    }

    /**
     * Remove the specified partner from storage.
     */
    public function destroy(Partner $partner)
    {
        try {
            $partner->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Partner deleted successfully.'
                ]);
            }

            return redirect()->route('partners.index')
                ->with('success', 'Partner deleted successfully');
                
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the partner.',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while deleting the partner.');
        }
    }
}
