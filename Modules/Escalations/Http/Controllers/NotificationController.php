<?php

namespace Modules\Escalations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Escalations\Entities\EscalationNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index()
    {
        $notifications = EscalationNotification::where('user_id', Auth::id())
            ->with('escalation')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('escalations::notifications.index', compact('notifications'));
    }
    
    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = EscalationNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
            
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Notification marked as read.');
    }
    
    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        EscalationNotification::where('user_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true, 'read_at' => now()]);
            
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
    
    /**
     * Get unread notifications count for the current user.
     */
    public function getUnreadCount()
    {
        $count = EscalationNotification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();
            
        return response()->json(['count' => $count]);
    }
    
    /**
     * Get recent unread notifications for the current user.
     */
    public function getRecentUnread()
    {
        $notifications = EscalationNotification::where('user_id', Auth::id())
            ->where('read', false)
            ->with('escalation')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count()
        ]);
    }
}
