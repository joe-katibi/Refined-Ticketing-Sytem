<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Outages\Database\Factories\OutageProgressFactory;

class OutageProgress extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'outage_progress';

    protected $fillable = [
        'outage_id',
        'ticket_id',
        'user_id',
        'status',
        'notes',
        'action_taken',
        'next_steps',
        'is_major_update',
        'created_by',
    ];

    protected $casts = [
        'is_major_update' => 'boolean',
    ];

    protected $appends = ['formatted_created_at'];

    protected static function newFactory()
    {
        return OutageProgressFactory::new();
    }

    /**
     * Get the outage that owns the progress update.
     */
    public function outage()
    {
        return $this->belongsTo(Outage::class);
    }

    /**
     * Get the ticket that owns the progress update.
     */
    public function ticket()
    {
        return $this->belongsTo(OutageTicket::class);
    }

    /**
     * Get the user who created the progress update.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get the creator of the progress update.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the formatted created_at timestamp.
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Scope a query to only include major updates.
     */
    public function scopeMajorUpdates($query)
    {
        return $query->where('is_major_update', true);
    }

    /**
     * Scope a query to only include progress for a specific outage.
     */
    public function scopeForOutage($query, $outageId)
    {
        return $query->where('outage_id', $outageId);
    }

    /**
     * Scope a query to only include progress for a specific ticket.
     */
    public function scopeForTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    /**
     * Get the previous status for comparison.
     */
    public function getPreviousStatus()
    {
        return static::where('outage_id', $this->outage_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc')
            ->value('status');
    }

    /**
     * Get the time elapsed since the previous update.
     */
    public function getTimeSinceLastUpdate()
    {
        $previousUpdate = static::where('outage_id', $this->outage_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$previousUpdate) {
            return 'First update';
        }

        return $previousUpdate->created_at->diffForHumans($this->created_at, true);
    }

    /**
     * Check if this update includes a status change.
     */
    public function hasStatusChanged()
    {
        $previousStatus = $this->getPreviousStatus();
        return $previousStatus !== null && $previousStatus !== $this->status;
    }
}
