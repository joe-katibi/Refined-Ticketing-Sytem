<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Outages\Models\Olt;

class Region extends Model
{
    protected $fillable = ['name', 'status', 'created_by', 'edited_by'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }

    /**
     * OLTs currently assigned to this region. Reuses olts.region_id (a
     * plain belongsTo already used for FIFO region-aware routing) rather
     * than a new pivot table — an OLT sits in exactly one region, so the
     * "multi-select" on the Region form is which OLTs currently point at
     * this region_id, not a genuine many-to-many.
     */
    public function olts(): HasMany
    {
        return $this->hasMany(Olt::class, 'region_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
