<?php

namespace App\Models\Fifo;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = ['queue_entry_id', 'user_id', 'result', 'reason', 'actor_id', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function queueEntry(): BelongsTo
    {
        return $this->belongsTo(QueueEntry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
