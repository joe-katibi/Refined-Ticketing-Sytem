<?php

namespace App\Models\Fifo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkQueue extends Model
{
    protected $fillable = ['module', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function entries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }
}
