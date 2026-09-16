<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Outages\Database\Factories\OutageFactory;
use Illuminate\Support\Facades\DB;
use App\Traits\OptimizedQueries;

class Outage extends Model
{
  use HasFactory, SoftDeletes, OptimizedQueries;

  protected $table = 'outages';

  protected $fillable = [
    'ticket_number',
    'ticket_type',
    'title',
    'description',
    'status',
    'priority',
    'impact',
    'urgency',
    'total_customers_affected',
    'olt_id',
    'slot_id',
    'port_id',
    'region_id',
    'start_time',
    'end_time',
    'root_cause',
    'resolution',
    'resolution_notes',
    'impacted_areas',
    'impacted_services',
    'assigned_team_id',
    'sub_team_type_id',
    'assigned_to',
    'reported_by',
    'resolved_by',
    'final_reason_id',
    'sla_breached',
    'sla_breach_time',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'start_time' => 'datetime',
    'end_time' => 'datetime',
    'sla_breach_time' => 'datetime',
    'impacted_areas' => 'array',
    'impacted_services' => 'array',
    'sla_breached' => 'boolean',
  ];

  protected static function newFactory()
  {
    return OutageFactory::new();
  }

  /**
   * Get the ticket type display name.
   */
  public function getTicketTypeDisplayAttribute()
  {
    return match ($this->ticket_type) {
      'regular' => 'Regular Outage',
      'emergency' => 'Emergency Outage',
      'planned_maintenance' => 'Planned Maintenance',
      default => 'Regular Outage',
    };
  }

  /**
   * Get the reasons associated with the outage.
   */
  public function reasons()
  {
    return $this->hasMany(OutageReason::class);
  }

  /**
   * Get the attachments associated with the outage.
   */
  public function attachments()
  {
    return $this->hasMany(OutageAttachment::class);
  }

  /**
   * Get the progress updates for the outage.
   */
  public function progress()
  {
    return $this->hasMany(OutageProgress::class)->latest();
  }

  /**
   * Get the activities for the outage.
   */
  public function activities()
  {
    return $this->hasMany(OutageActivity::class)->latest();
  }

  /**
   * Get the team type assigned to the outage.
   */
  public function assignedTeam()
  {
    return $this->belongsTo(\App\Models\TeamType::class, 'assigned_team_id');
  }

  /**
   * Get the sub team type assigned to the outage.
   */
  public function assignedSubTeamType()
  {
    return $this->belongsTo(\App\Models\SubTeamType::class, 'sub_team_type_id');
  }

  /**
   * Get the user assigned to the outage.
   */
  public function assignee()
  {
    return $this->belongsTo(\App\Models\User::class, 'assigned_to');
  }

  /**
   * Get the user who reported the outage.
   */
  public function reporter()
  {
    return $this->belongsTo(\App\Models\User::class, 'reported_by');
  }

  /**
   * Get the user who resolved the outage.
   */
  public function resolver()
  {
    return $this->belongsTo(\App\Models\User::class, 'resolved_by');
  }

  /**
   * Get the affected areas for the outage.
   */
  public function affectedAreas()
  {
    return $this->belongsToMany(AffectedArea::class, 'outage_affected_areas', 'outage_id', 'affected_area_id');
  }

  /**
   * Get the affected services for the outage.
   */
  public function affectedServices()
  {
    return $this->belongsToMany(AffectedService::class, 'outage_affected_services', 'outage_id', 'affected_service_id');
  }

  /**
   * Get the OLT associated with the outage.
   */
  public function olt()
  {
    return $this->belongsTo(Olt::class, 'olt_id');
  }

  /**
   * Get the OLT slot associated with the outage.
   */
  public function oltSlot()
  {
    return $this->belongsTo(OltSlot::class, 'slot_id');
  }

  /**
   * Get the PON port associated with the outage.
   */
  public function ponPort()
  {
    return $this->belongsTo(PonPort::class, 'port_id');
  }

  /**
   * Get all available status options.
   */
  public static function getStatusOptions()
  {
    return [
      'support-unconfirmed-outage' => 'Support Unconfirmed Outage',
      'noc-confirmed-outage' => 'NOC Confirmed Outage',
      'noc-rejected' => 'NOC Rejected',
      'infra-dispatched' => 'Infrastructure Dispatched',
      'infra-confirmed-outage' => 'Infrastructure Confirmed Outage',
      'infra-resolved' => 'Infrastructure Resolved',
      'noc-restore-confirmed' => 'NOC Restore Confirmed',
      'noc-incident-outage' => 'NOC Incident Outage',
      'support-follow-up' => 'Support Follow-up',
      'support-closed' => 'Support Closed',
      'auto-monitor-detected' => 'Auto Monitor Detected',
      'awaiting-customer-confirmation' => 'Awaiting Customer Confirmation',
      'partial-restore' => 'Partial Restore',
      'awaiting-field-access' => 'Awaiting Field Access',
      'scheduled-maintenance' => 'Scheduled Maintenance',
      'vendor-escalated' => 'Vendor Escalated',
    ];
  }

  /**
   * Get the status display name.
   */
  public function getStatusDisplayAttribute()
  {
    $statusOptions = self::getStatusOptions();
    return $statusOptions[$this->status] ?? $this->status;
  }

  /**
   * Scope a query to only include active outages.
   */
  public function scopeActive($query)
  {
    return $query->whereNotIn('status', ['infra-resolved', 'support-closed']);
  }

  /**
   * Scope a query to only include resolved outages.
   */
  public function scopeResolved($query)
  {
    return $query->whereIn('status', ['infra-resolved', 'support-closed']);
  }

  /**
   * Scope a query to only include SLA breached outages.
   */
  public function scopeBreached($query)
  {
    return $query->where('sla_breached', true);
  }

  /**
   * Get the active tickets count for the outage.
   */
  public function getActiveTicketsCountAttribute()
  {
    return $this->tickets()
      ->where('status', '!=', 'Resolved')
      ->count();
  }

  /**
   * Check if the outage is active.
   */
  public function isActive()
  {
    return !in_array($this->status, ['infra-resolved', 'support-closed']);
  }

  /**
   * Check if the outage is resolved.
   */
  public function isResolved()
  {
    return in_array($this->status, ['infra-resolved', 'support-closed']);
  }

  /**
   * Generate a new ticket number based on ticket type.
   */
  public static function generateTicketNumber($ticketType = 'regular')
  {
    // Define prefixes for different ticket types
    $prefixes = [
      'regular' => 'OUT',
      'emergency' => 'EMO',
      'planned_maintenance' => 'PLM',
    ];

    $prefix = $prefixes[$ticketType] ?? 'OUT';

    // Previously read the last ticket via ORDER BY id DESC with no lock/transaction,
    // the same race condition the spec explicitly warns against for appointment
    // numbering (two concurrent requests can read the same "last ticket" and both
    // compute the same next number). Uses the same locked-counter approach as
    // Appointment ticket numbering.
    $number = \App\Services\SequenceNumberService::next('outage:' . $prefix);

    return $prefix . '-' . $number;
  }

  /**
   * Get the calculated priority based on impact and urgency.
   */
  public function calculatePriority()
  {
    $impact = strtolower($this->impact);
    $urgency = strtolower($this->urgency);

    if ($impact === 'critical' && $urgency === 'critical') {
      return 'Critical';
    } elseif (($impact === 'critical' && $urgency === 'high') || ($impact === 'high' && $urgency === 'critical')) {
      return 'Critical';
    } elseif (
      ($impact === 'high' && $urgency === 'high') ||
      ($impact === 'critical' && $urgency === 'medium') ||
      ($impact === 'medium' && $urgency === 'critical')
    ) {
      return 'High';
    } elseif (
      ($impact === 'high' && $urgency === 'medium') ||
      ($impact === 'medium' && $urgency === 'high') ||
      ($impact === 'critical' && $urgency === 'low') ||
      ($impact === 'low' && $urgency === 'critical')
    ) {
      return 'Medium';
    } else {
      return 'Low';
    }
  }

  /**
   * Get the final reason for the outage
   */
  public function finalReason()
  {
    return $this->belongsTo(OutageFinalReason::class, 'final_reason_id');
  }

  /**
   * Get all photos for this outage
   */
  public function photos()
  {
    return $this->hasMany(\App\Models\TicketPhoto::class, 'outage_id');
  }

  /**
   * Automatically set priority based on impact and urgency when saving.
   */
  protected static function boot()
  {
    parent::boot();

    static::saving(function ($outage) {
      // Auto-calculate priority if impact and urgency are set
      if ($outage->impact && $outage->urgency) {
        $outage->priority = $outage->calculatePriority();
      }

      // Auto-set status for planned maintenance
      if ($outage->ticket_type === 'planned_maintenance' && !$outage->exists) {
        $outage->status = 'scheduled-maintenance';
      }
    });

    static::created(function ($outage) {
      // Log outage creation
      if (\Auth::check()) {
        OutageActivity::logCreation($outage, \Auth::user());
      }
    });

    static::updated(function ($outage) {
      // Log outage updates
      if (\Auth::check() && $outage->wasChanged()) {
        $oldValues = $outage->getOriginal();
        $newValues = $outage->getAttributes();

        // Log status changes separately
        if ($outage->wasChanged('status')) {
          OutageActivity::logStatusChange($outage, $oldValues['status'], $newValues['status'], \Auth::user());
        }

        // Log other changes
        $changedFields = array_keys($outage->getChanges());
        if (!empty($changedFields) && !in_array('status', $changedFields)) {
          OutageActivity::logUpdate($outage, \Auth::user(), $oldValues, $newValues);
        }
      }
    });
  }
}
