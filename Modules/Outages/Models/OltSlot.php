<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class OltSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'olt_id',
        'slot_number',
        'slot_type',
        'status',
        'created_by',
        'edited_by'
    ];

    protected $casts = [
        'slot_number' => 'integer',
    ];

    /**
     * Get the OLT that owns the slot.
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    /**
     * Get the PON ports for the slot.
     */
    public function ponPorts(): HasMany
    {
        return $this->hasMany(PonPort::class);
    }

    /**
     * Get the user who created this slot.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this slot.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Scope to get active slots.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'faulty' => 'bg-danger',
            'spare' => 'bg-warning',
            default => 'bg-primary'
        };
    }

    /**
     * Get the slot type badge class.
     */
    public function getSlotTypeBadgeClassAttribute(): string
    {
        return match(strtolower($this->slot_type ?? '')) {
            'gpon' => 'bg-primary',
            'xgspon' => 'bg-success',
            'epon' => 'bg-info',
            '10g-epon' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    /**
     * Get active PON ports count.
     */
    public function getActivePonPortsAttribute(): int
    {
        return $this->ponPorts()->where('status', 'active')->count();
    }

    /**
     * Get total PON ports count.
     */
    public function getTotalPonPortsAttribute(): int
    {
        return $this->ponPorts()->count();
    }

    /**
     * Get slot identifier (OLT/Slot format).
     */
    public function getIdentifierAttribute(): string
    {
        return "{$this->olt->name}/Slot-{$this->slot_number}";
    }
}
