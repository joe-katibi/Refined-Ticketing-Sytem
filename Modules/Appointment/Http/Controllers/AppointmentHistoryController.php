<?php

namespace Modules\Appointment\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Appointment\Models\AppointmentHistory;
use Modules\Appointment\Models\AppointmentType;
use Modules\Appointment\Models\Appointment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AppointmentHistoryController extends Controller
{
  public function index()
  {
    // Get all appointment types for the navigation pills
    $appointmentTypes = AppointmentType::with('subTypes')->get();

    // Get histories grouped by appointment type
    $historiesByType = [];

    foreach ($appointmentTypes as $type) {
      $historiesByType[$type->id] = [];

      // Get all sub-type IDs for this type
      $subTypeIds = $type->subTypes->pluck('id');

      // Get histories for this type
      $historiesByType[$type->id] = AppointmentHistory::with(['appointment', 'actionBy'])
        ->whereHas('appointment', function ($query) use ($subTypeIds) {
          $query->whereIn('appointment_type_id', $subTypeIds);
        })
        ->latest()
        ->get()
        ->groupBy('appointment_id');
    }

    return view('appointment::appointmentHistory.index', [
      'appointmentTypes' => $appointmentTypes,
      'historiesByType' => $historiesByType,
    ]);
  }

  /**
   * Display the specified appointment's history.
   *
   * @param  int  $appointmentId
   * @return \Illuminate\Http\Response
   */
  public function show($appointmentId)
  {
    $appointment = Appointment::with(['type', 'subType'])->findOrFail($appointmentId);

    $histories = AppointmentHistory::with('actionBy')
      ->where('appointment_id', $appointmentId)
      ->latest()
      ->get();

    return view('appointment::appointmentHistory.show', [
      'appointment' => $appointment,
      'histories' => $histories,
    ]);
  }

  /**
   * Infrastructure History - List all appointment histories with status 'Escalated-Infrastructure'
   */
  public function infrastructureHistory(Request $request)
  {
    try {
      // Get appointment histories with Infrastructure status, including appointment and OLT relationships
      $histories = AppointmentHistory::with(['appointment.olt'])
        ->where('status', 'Escalated-Infrastructure')
        ->orderBy('created_at', 'desc')
        ->get();

      return view('appointment::site-visit.infrastructure.history', [
        'histories' => $histories,
      ]);
    } catch (\Exception $e) {
      \Log::error('Infrastructure History Error: ' . $e->getMessage());
      return back()->with('error', 'Unable to load infrastructure history: ' . $e->getMessage());
    }
  }

  /**
   * NOC History - List all appointment histories with status 'Escalated-NOC'
   */
  public function nocHistory(Request $request)
  {
    try {
      // Get appointment histories with NOC status, including appointment and OLT relationships
      $histories = AppointmentHistory::with(['appointment.olt'])
        ->where('status', 'Escalated-NOC')
        ->orderBy('created_at', 'desc')
        ->get();

      return view('appointment::site-visit.noc.history', [
        'histories' => $histories,
      ]);
    } catch (\Exception $e) {
      \Log::error('NOC History Error: ' . $e->getMessage());
      return back()->with('error', 'Unable to load NOC history: ' . $e->getMessage());
    }
  }

  /**
   * Design History - List all appointment histories with status 'Escalated-Design'
   */
  public function designHistory(Request $request)
  {
    try {
      // Get appointment histories with Design status, including appointment and OLT relationships
      $histories = AppointmentHistory::with(['appointment.olt'])
        ->where('status', 'Escalated-Design')
        ->orderBy('created_at', 'desc')
        ->get();

      return view('appointment::site-visit.design.history', [
        'histories' => $histories,
      ]);
    } catch (\Exception $e) {
      \Log::error('Design History Error: ' . $e->getMessage());
      return back()->with('error', 'Unable to load design history: ' . $e->getMessage());
    }
  }
}
