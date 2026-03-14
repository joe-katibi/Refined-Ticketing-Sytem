<?php

namespace Modules\Escalations\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscalationNotification extends Model
{
    protected $fillable = [
        'user_id',
        'escalation_id',
        'type',
        'message',
        'read',
        'read_at'
    ];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $table = 'escalation_notifications';

    /**
     * Get the user that the notification belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get the escalation that the notification is about.
     */
    public function escalation(): BelongsTo
    {
        return $this->belongsTo(Escalation::class, 'escalation_id');
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        $this->read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }
}
