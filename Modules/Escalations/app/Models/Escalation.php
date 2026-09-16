<?php

namespace Modules\Escalations\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\SubDepartment as Department;
use Modules\Escalations\App\Models\EscalationHistory;
use Modules\Escalations\Entities\EscalationList;
use Modules\Escalations\Entities\Subcategory;

class Escalation extends Model
{
    use HasFactory;

    /**
     * Get the appointment associated with the escalation.
     */
    public function appointment()
    {
        return $this->belongsTo(\Modules\Appointment\Models\Appointment::class, 'appointment_id');
    }

    /**
     * Get the appointment type associated with the escalation.
     */
    public function appointmentType()
    {
        return $this->belongsTo(\Modules\Appointment\Models\AppointmentType::class, 'appointment_type_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'escalation_id',
        'ticket_id',
        'appointment_id',
        'appointment_type_id',
        'account_number',
        'category_id',
        'sub_category_id',
        'department_id',
        'sub_department_id',
        'region_id',
        'description',
        'priority',
        'status',
        'assigned_to',
        'created_by',
        'edited_by',
        // Support fields
        'support_date',
        'support_time',
        'support_address',
        'support_notes',
        // Shifting fields
        'shifting_date',
        'shifting_time',
        'shifting_address',
        'shifting_notes',
        // Installation fields
        'installation_date',
        'installation_time',
        'installation_address',
        'installation_notes',
        // WiFi Extender fields
        'wifi_extender_date',
        'wifi_extender_time',
        'wifi_extender_address',
        'wifi_extender_notes',
    ];

    /**
     * Get all history entries for this escalation.
     */
    public function history(): HasMany
    {
        return $this->hasMany(EscalationHistory::class)->latest();
    }

    /**
     * Get the current status of the escalation.
     */
    public function currentStatus()
    {
        return $this->history()->latest()->first();
    }

    /**
     * Get all assignments for this escalation.
     */
    public function assignments()
    {
        return $this->history()->whereNotNull('assigned_to')->latest();
    }

    /**
     * Get the current assignee.
     */
    /**
     * Get the ticket associated with this escalation.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(EscalationList::class, 'ticket_id', 'id');
    }

    /**
     * Get the current assignee.
     */
    public function currentAssignee()
    {
        $latest = $this->history()->whereNotNull('assigned_to')->latest()->first();
        return $latest ? $latest->assignedTo : null;
    }

    /**
     * Get the current department.
     */
    public function currentDepartment()
    {
        $latest = $this->history()->whereNotNull('sub_department_id')->latest()->first();
        return $latest ? $latest->subDepartment : null;
    }

    /**
     * Get the subcategory associated with the escalation.
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }

    /**
     * Get the department associated with the escalation.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    /**
     * Get the sub-department associated with the escalation.
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SubDepartment::class, 'sub_department_id');
    }

    // protected static function newFactory(): EscalationFactory
    // {
    //     // return EscalationFactory::new();
    // }
}
