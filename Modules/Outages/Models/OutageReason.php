<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Outages\Database\Factories\OutageReasonFactory;

class OutageReason extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'outage_reasons';

    protected $fillable = [
        'outage_id',
        'ticket_id',
        'reason_type',
        'description',
        'root_cause',
        'resolution',
        'resolved_by',
        'resolved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return OutageReasonFactory::new();
    }

    /**
     * Get the outage that owns the reason.
     */
    public function outage()
    {
        return $this->belongsTo(Outage::class);
    }

    /**
     * Get the ticket that owns the reason.
     */
    public function ticket()
    {
        return $this->belongsTo(OutageTicket::class);
    }

    /**
     * Get the user who resolved the reason.
     */
    public function resolver()
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    /**
     * Get the user who created the reason.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope a query to only include resolved reasons.
     */
    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    /**
     * Scope a query to only include unresolved reasons.
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Check if the reason is resolved.
     */
    public function isResolved()
    {
        return !is_null($this->resolved_at);
    }

    /**
     * Mark the reason as resolved.
     */
    public function markAsResolved($userId, $resolution = null)
    {
        $this->update([
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'resolution' => $resolution ?? $this->resolution,
        ]);

        return $this;
    }

    /**
     * Get the time taken to resolve the reason.
     */
    public function getTimeToResolveAttribute()
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->resolved_at, true);
    }
}
