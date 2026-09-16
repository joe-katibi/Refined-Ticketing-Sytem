<?php

namespace App\Models\Fifo;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentWorkload extends Model
{
    protected $fillable = [
        'user_id', 'module', 'active_count', 'capacity', 'is_available', 'last_assigned_at',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'last_assigned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
