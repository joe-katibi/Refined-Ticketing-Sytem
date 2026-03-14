<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\User;

class PonPort extends Model
{
    use HasFactory;

    protected $fillable = [
        'olt_slot_id',
        'pon_port_number',
        'pon_port_type',
        'status',
        'created_by',
        'edited_by'
    ];

    protected $casts = [
        'pon_port_number' => 'integer',
    ];

    /**
     * Get the OLT slot that owns the PON port.
     */
    public function oltSlot(): BelongsTo
    {
        return $this->belongsTo(OltSlot::class);
    }

    /**
     * Get the OLT through the slot.
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }

    /**
     * Get the user who created this PON port.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this PON port.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Get the FDTs for the PON port.
     */
    public function fdts(): HasMany
    {
        return $this->hasMany(Fdt::class);
    }

    /**
     * Get all FATs through FDTs.
     */
    public function fats(): HasManyThrough
    {
        return $this->hasManyThrough(Fat::class, Fdt::class);
    }

    /**
     * Scope to get active PON ports.
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
     * Get the PON port type badge class.
     */
    public function getPonPortTypeBadgeClassAttribute(): string
    {
        return match(strtolower($this->pon_port_type ?? '')) {
            'gpon' => 'bg-primary',
            'xgspon' => 'bg-success',
            'epon' => 'bg-info',
            '10g-epon' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    /**
     * Get port identifier (OLT/Slot/Port format).
     */
    public function getIdentifierAttribute(): string
    {
        return "{$this->oltSlot->olt->name}/Slot-{$this->oltSlot->slot_number}/Port-{$this->pon_port_number}";
    }

    /**
     * Get short identifier (Slot/Port format).
     */
    public function getShortIdentifierAttribute(): string
    {
        return "Slot-{$this->oltSlot->slot_number}/Port-{$this->pon_port_number}";
    }
}
