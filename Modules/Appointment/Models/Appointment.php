<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TeamType;
use App\Models\SubTeamType;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Outages\Models\Olt;
use Modules\Outages\Models\OltSlot;
use App\Traits\OptimizedQueries;
use Modules\Appointment\Models\AppointmentStatus;

class Appointment extends Model
{
  use SoftDeletes, OptimizedQueries;

  protected $fillable = [
    'account_number',
    'appointment_ticket_id',
    'escalation_ticket_id',
    'outage_ticket_id',
    'appointment_id',
    'appointment_type_id',
    'sub_department_id',
    'category_id',
    'sub_category_id',
    'olt_id',
    'slot_id',
    'description_notes',
    'priority',
    'status',
    'scheduled_date',
    'scheduled_time',
    'completed_date',
    'completed_time',
    'assigned_team_id',
    'assigned_to',
    'region_id',
    'escalated_team_id',
    'team_type_id',
    'sub_team_type_id',
    'closed_by',
    'notes_created',
    'notes_closed',
    'closing_reason',
    'escalation_reason',
    'escalation_notes',
    'appointment_type',
    'appointment_status',
    'appointment_location',
    'appointment_venue',
    'created_by',
    'edited_by',
    'closed_at',
    'escalated_at',
    'comment',
    'rescheduled_date',
    'rescheduled_time',
    'reschedule_reason',
    'cancelled_reason',
    'final_reason_id',
  ];

  protected $dates = [
    'scheduled_date',
    'completed_date',
    'rescheduled_date',
    'closed_at',
    'escalated_at',
    'created_at',
    'updated_at',
    'deleted_at',
  ];

  protected $casts = [
    'scheduled_date' => 'date:Y-m-d',
    'completed_date' => 'date:Y-m-d',
    'rescheduled_date' => 'date:Y-m-d',
    'scheduled_time' => 'datetime:H:i',
    'completed_time' => 'datetime:H:i',
    'rescheduled_time' => 'datetime:H:i',
  ];

  // Relationships
  public function creator(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class, 'created_by');
  }

  public function editor(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class, 'edited_by');
  }

  public function type(): BelongsTo
  {
    return $this->belongsTo(AppointmentType::class, 'appointment_id');
  }

  public function subType(): BelongsTo
  {
    return $this->belongsTo(SubAppointmentType::class, 'appointment_type_id');
  }

  public function assignedTeam(): BelongsTo
  {
    return $this->belongsTo(\App\Models\Team::class, 'assigned_team_id');
  }

  /**
   * The individual technician assigned to this appointment. Referenced by
   * the edit form and by MobileAppointmentController's technician views,
   * which filter strictly on assigned_to — but no relation existed here
   * before, so any code eager-loading or accessing ->assignee would have
   * thrown a RelationNotFoundException the first time assigned_to was
   * actually set on a real appointment.
   */
  public function assignee(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class, 'assigned_to');
  }

  public function escalatedTeam(): BelongsTo
  {
    return $this->belongsTo(\App\Models\Team::class, 'escalated_team_id');
  }

  public function closer(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class, 'closed_by');
  }

  public function teamType(): BelongsTo
  {
    return $this->belongsTo(TeamType::class, 'team_type_id');
  }

  public function subTeamType(): BelongsTo
  {
    return $this->belongsTo(SubTeamType::class, 'sub_team_type_id');
  }

  public function olt(): BelongsTo
  {
    return $this->belongsTo(Olt::class, 'olt_id');
  }

  public function slot(): BelongsTo
  {
    return $this->belongsTo(OltSlot::class, 'slot_id');
  }
  
  public function appointmentStatus(): BelongsTo
  {
    return $this->belongsTo(AppointmentStatus::class, 'status', 'name');
  }

  // Scopes
  public function scopeActive($query)
  {
    return $query->where('status', '!=', 'Closed');
  }

  public function scopeCompleted($query)
  {
    return $query->where('status', 'Scheduled-Closed');
  }

  // Helpers
  public function getStatusBadgeAttribute(): string
  {
    // Get the status from the database
    $statusModel = AppointmentStatus::where('name', $this->status)->first();
    
    if ($statusModel) {
      $badgeClass = $statusModel->badge_class ?: 'bg-info';
      $color = $statusModel->color ?: '#3498db';
      $displayName = $statusModel->display_name ?: $this->status;
      
      return '<span class="badge ' . $badgeClass . '" style="background-color: ' . $color . ' !important;">' . $displayName . '</span>';
    }
    
    // Fallback to the old behavior if status not found in the database
    return match ($this->status) {
      'Escalated-Open' => '<span class="badge bg-warning">Escalated</span>',
      'Escalated-Closed' => '<span class="badge bg-success">Closed</span>',
      default => '<span class="badge bg-info">' . $this->status . '</span>',
    };
  }

  public function getPriorityBadgeAttribute(): string
  {
    return match ($this->priority) {
      'High' => '<span class="badge bg-danger">High</span>',
      'Medium' => '<span class="badge bg-warning">Medium</span>',
      'Low' => '<span class="badge bg-info">Low</span>',
      default => '<span class="badge bg-secondary">' . $this->priority . '</span>',
    };
  }

  /**
   * Get all photos for this appointment
   */
  public function photos()
  {
    return $this->hasMany(\App\Models\TicketPhoto::class, 'ticket_id')
                ->where('ticket_type', 'appointment');
  }
  
  /**
   * Get all status history entries for this appointment
   */
  public function statusHistory()
  {
    return $this->hasMany(AppointmentStatusHistory::class)->orderBy('created_at', 'desc');
  }
}
