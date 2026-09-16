<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Modules\Outages\Models\Outage;
use Modules\Escalations\App\Models\Escalation;
use Modules\Appointment\Models\Appointment;
use App\Models\Fifo\QueueEntry;
use App\Models\User;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Main dashboard. Previously ~400 lines of this method were dead code
     * after an early `return view('home', ...)` (a second, never-reached
     * `return view('dashboard')->with($data)` sat at the very end) — all of
     * it removed. The live stats it did compute also matched the wrong
     * status vocabulary (`Escalation::where('status','Open')` when real rows
     * use 'Escalated-Open', `Appointment::where('status','Scheduled')` when
     * real rows use 'scheduled-open', etc.), so those counts silently read
     * zero regardless of actual data — the same class of bug fixed
     * separately in UserController's Active/Inactive counts. Fixed here, and
     * the view now actually renders everything computed instead of the
     * static all-zero cards it shipped with.
     */
    public function index()
    {
        $user = Auth::user();

        $escalationsTotal = Escalation::count();
        $escalationsOpen = Escalation::where('status', 'Escalated-Open')->count();
        $escalationsClosed = Escalation::where('status', 'Escalated-Closed')->count();
        $escalationsHigh = Escalation::where('priority', 'High')->count();
        $escalationsToday = Escalation::whereDate('created_at', today())->count();
        $escalationsThisWeek = Escalation::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $appointmentsTotal = Appointment::count();
        $appointmentsOpen = Appointment::whereIn('status', ['scheduled-open', 'scheduled-assigned-team', 'in-progress'])->count();
        $appointmentsCompleted = Appointment::where('status', 'completed')->count();
        $appointmentsCancelled = Appointment::where('status', 'cancelled')->count();
        $appointmentsToday = Appointment::whereDate('scheduled_date', today())->count();
        $appointmentsThisWeek = Appointment::whereBetween('scheduled_date', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $outagesTotal = Outage::count();
        $outagesActive = Outage::whereNotIn('status', ['infra-resolved', 'noc-restore-confirmed', 'support-closed'])->count();
        $outagesResolved = Outage::whereIn('status', ['infra-resolved', 'noc-restore-confirmed', 'support-closed'])->count();
        $outagesCritical = Outage::where('priority', 'Critical')->count();
        $outagesHigh = Outage::where('priority', 'High')->count();
        $outagesEmergency = Outage::where('ticket_type', 'emergency')->count();

        // FIFO queue depth per module — the FIFO Queue pages have the full
        // detail; this is just "how much unassigned work is piling up".
        $fifoWaiting = [
            'escalation' => QueueEntry::whereHas('queue', fn ($q) => $q->where('module', 'escalation'))->where('status', 'waiting')->count(),
            'appointment' => QueueEntry::whereHas('queue', fn ($q) => $q->where('module', 'appointment'))->where('status', 'waiting')->count(),
            'outage' => QueueEntry::whereHas('queue', fn ($q) => $q->where('module', 'outage'))->where('status', 'waiting')->count(),
        ];
        $fifoOldestWaitMinutes = QueueEntry::where('status', 'waiting')->min('queue_entered_at');
        $fifoOldestWaitMinutes = $fifoOldestWaitMinutes ? now()->diffInMinutes($fifoOldestWaitMinutes) : null;

        // Users: `user_status` is stored as 1/0 (see UserController@activate/
        // deactivate), not the strings 'Active'/'Inactive' — this dashboard
        // previously didn't show user stats at all.
        $usersTotal = User::count();
        $usersActive = User::where('user_status', 1)->count();
        $usersInactive = User::where('user_status', 0)->count();

        $recentEscalations = Escalation::where('created_at', '>=', now()->subDays(7))->orderBy('created_at', 'desc')->limit(5)->get();
        $recentAppointments = Appointment::where('created_at', '>=', now()->subDays(7))->orderBy('created_at', 'desc')->limit(5)->get();
        $recentOutages = Outage::where('created_at', '>=', now()->subDays(7))->orderBy('created_at', 'desc')->limit(5)->get();

        $monthlyData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyData->push([
                'month' => $date->format('M Y'),
                'escalations' => Escalation::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'appointments' => Appointment::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'outages' => Outage::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ]);
        }

        return view('home', compact(
            'user',
            'escalationsTotal', 'escalationsOpen', 'escalationsClosed', 'escalationsHigh',
            'escalationsToday', 'escalationsThisWeek',
            'appointmentsTotal', 'appointmentsOpen', 'appointmentsCompleted', 'appointmentsCancelled',
            'appointmentsToday', 'appointmentsThisWeek',
            'outagesTotal', 'outagesActive', 'outagesResolved', 'outagesCritical', 'outagesHigh', 'outagesEmergency',
            'fifoWaiting', 'fifoOldestWaitMinutes',
            'usersTotal', 'usersActive', 'usersInactive',
            'recentEscalations', 'recentAppointments', 'recentOutages',
            'monthlyData'
        ));
    }
}
