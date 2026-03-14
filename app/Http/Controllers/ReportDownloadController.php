<?php

namespace App\Http\Controllers;

use App\Models\ReportDownload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ReportDownloadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the report downloads page
     */
    public function index()
    {
        return view('admin.report-downloads.index');
    }

    /**
     * Get report downloads data for DataTables
     */
    public function getData(Request $request)
    {
        $downloads = ReportDownload::with('user')
            ->forUser(Auth::id())
            ->orderBy('created_at', 'desc');

        return DataTables::of($downloads)
            ->addColumn('user_name', function ($download) {
                return $download->user->name ?? 'Unknown';
            })
            ->addColumn('date_range', function ($download) {
                if ($download->start_date && $download->end_date) {
                    return $download->start_date->format('M d, Y') . ' - ' . $download->end_date->format('M d, Y');
                }
                return 'N/A';
            })
            ->addColumn('status_badge', function ($download) {
                $badgeClass = $download->getStatusBadgeClass();
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($download->status) . '</span>';
            })
            ->addColumn('requested_at_formatted', function ($download) {
                return $download->requested_at->format('M d, Y H:i');
            })
            ->addColumn('completed_at_formatted', function ($download) {
                return $download->completed_at ? $download->completed_at->format('M d, Y H:i') : 'N/A';
            })
            ->addColumn('actions', function ($download) {
                $actions = '';
                
                if ($download->isCompleted() && $download->file_path) {
                    $actions .= '<a href="' . route('report-downloads.download', $download->id) . '" 
                                   class="btn btn-sm btn-success me-1" title="Download Report">
                                   <i class="bx bx-download"></i> Download
                                </a>';
                }
                
                if ($download->isFailed()) {
                    $actions .= '<button class="btn btn-sm btn-danger me-1" 
                                        onclick="showError(' . $download->id . ')" title="View Error">
                                   <i class="bx bx-error"></i> Error
                                </button>';
                }
                
                $actions .= '<button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteDownload(' . $download->id . ')" title="Delete">
                               <i class="bx bx-trash"></i>
                            </button>';
                
                return $actions;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Download the generated report file
     */
    public function download($id)
    {
        $download = ReportDownload::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->firstOrFail();

        if (!$download->file_path || !Storage::exists($download->file_path)) {
            return redirect()->back()->with('error', 'Report file not found or has been deleted.');
        }

        return Storage::download($download->file_path, $download->file_name);
    }

    /**
     * Get error details for a failed download
     */
    public function getError($id)
    {
        $download = ReportDownload::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'failed')
            ->firstOrFail();

        return response()->json([
            'error_message' => $download->error_message ?? 'Unknown error occurred'
        ]);
    }

    /**
     * Delete a report download record
     */
    public function destroy($id)
    {
        $download = ReportDownload::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Delete the file if it exists
        if ($download->file_path && Storage::exists($download->file_path)) {
            Storage::delete($download->file_path);
        }

        $download->delete();

        return response()->json(['success' => true, 'message' => 'Download record deleted successfully.']);
    }

    /**
     * Get unread count for notifications
     */
    public function getUnreadCount()
    {
        $count = ReportDownload::forUser(Auth::id())
            ->where('status', 'completed')
            ->where('created_at', '>', now()->subHours(1))
            ->count();

        return response()->json(['count' => $count]);
    }
}
