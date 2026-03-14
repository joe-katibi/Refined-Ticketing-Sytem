<?php

namespace Modules\Outages\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Outages\Models\OutageNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OutageNotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $notifications = OutageNotification::with('outage')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('outages::notifications.index', compact('notifications'));
    }
    
    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = OutageNotification::findOrFail($id);
        
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id != Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized action');
        }
        
        $notification->read = true;
        $notification->save();
        
        return redirect()->back()->with('success', 'Notification marked as read');
    }
    
    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        OutageNotification::where('user_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);
            
        return redirect()->back()->with('success', 'All notifications marked as read');
    }
    
    /**
     * Get unread notification count for the authenticated user.
     */
    public function getUnreadCount()
    {
        $count = OutageNotification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();
            
        return response()->json(['count' => $count]);
    }
    
    /**
     * Get recent notifications for dropdown.
     */
    public function getRecent()
    {
        $notifications = OutageNotification::with(['outage', 'creator'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        return response()->json($notifications);
    }
}
