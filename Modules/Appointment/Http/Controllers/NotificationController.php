<?php

namespace Modules\Appointment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Appointment\Models\AppointmentNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $notifications = AppointmentNotification::with('appointment')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('appointment::notifications.index', compact('notifications'));
    }
    
    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = AppointmentNotification::findOrFail($id);
        
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
        AppointmentNotification::where('user_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);
            
        return redirect()->back()->with('success', 'All notifications marked as read');
    }
}
