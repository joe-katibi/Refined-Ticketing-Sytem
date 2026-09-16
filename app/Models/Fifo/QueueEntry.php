<?php

namespace App\Models\Fifo;

use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueEntry extends Model
{
    protected $fillable = [
        'work_queue_id', 'work_type', 'work_id', 'priority', 'region_id',
        'status', 'queue_entered_at', 'assigned_at', 'assigned_to',
    ];

    protected $casts = [
        'queue_entered_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(WorkQueue::class, 'work_queue_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssignmentAttempt::class);
    }

    /**
     * The underlying ticket (Escalation/Appointment/Outage) this entry
     * represents, resolved by work_type.
     */
    public function work()
    {
        return match ($this->work_type) {
            'escalation' => \Modules\Escalations\Entities\Escalation::find($this->work_id),
            'appointment' => \Modules\Appointment\Models\Appointment::find($this->work_id),
            'outage' => \Modules\Outages\Models\Outage::find($this->work_id),
            default => null,
        };
    }
}
