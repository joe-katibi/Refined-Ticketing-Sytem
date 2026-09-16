<?php

namespace Modules\Outages\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Outages\Entities\Olt;
use Modules\Outages\Entities\OltSlot;
use Modules\Outages\Entities\PonPort;
use App\Models\TeamType;
use App\Models\SubTeamType;

class OutageApiController extends Controller
{
  /**
   * Get slots for a specific OLT
   */
  public function getOltSlots($oltId)
  {
    try {
      $slots = OltSlot::where('olt_id', $oltId)
        ->where('status', 'active')
        ->orderBy('slot_number')
        ->get(['id', 'slot_number', 'slot_type']);

      return response()->json([
        'success' => true,
        'slots' => $slots,
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Error fetching slots: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  /**
   * Get ports for a specific slot
   */
  public function getSlotPorts($slotId)
  {
    try {
      $ports = PonPort::where('olt_slot_id', $slotId)
        ->where('status', 'active')
        ->orderBy('pon_port_number')
        ->get(['id', 'pon_port_number', 'pon_port_type']);

      return response()->json([
        'success' => true,
        'ports' => $ports,
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Error fetching ports: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  /**
   * Get sub team types for a specific team type
   */
  public function getSubTeamTypes($teamTypeId)
  {
    try {
      $subTeamTypes = SubTeamType::where('team_type_id', $teamTypeId)
        ->where('status', 'Active')
        ->orderBy('sub_type_name')
        ->get(['id', 'sub_type_name as display_name']);

      return response()->json([
        'success' => true,
        'subTeamTypes' => $subTeamTypes,
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Error fetching sub team types: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  /**
   * Get users for a specific sub team type
   */
  public function getSubTeamUsers($subTeamTypeId)
  {
    try {
      $users = User::where('sub_team_type_id', $subTeamTypeId)
        ->where('user_status', 1)
        ->orderBy('name')
        ->get(['id', 'name', 'email'])
        ->map(function ($user) {
          return [
            'id' => $user->id,
            'display_name' => $user->name . ' (' . $user->email . ')',
          ];
        });

      return response()->json([
        'success' => true,
        'users' => $users,
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Error fetching users: ' . $e->getMessage(),
        ],
        500
      );
    }
  }
}
