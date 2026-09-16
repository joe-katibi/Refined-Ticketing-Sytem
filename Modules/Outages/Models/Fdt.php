<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Fdt extends Model
{
    use HasFactory;

    protected $fillable = [
        'pon_port_id',
        'fdt_number',
        'fdt_type',
        'location',
        'capacity',
        'status',
        'created_by',
        'edited_by'
    ];

    protected $casts = [
        'fdt_number' => 'integer',
        'capacity' => 'integer',
    ];

    /**
     * Get the PON port that owns the FDT.
     */
    public function ponPort(): BelongsTo
    {
        return $this->belongsTo(PonPort::class);
    }

    /**
     * Get the OLT slot through the PON port. There is no olt_slot_id column
     * on fdts (only pon_port_id, per $fillable) — this used to be a
     * belongsTo(OltSlot::class, 'olt_slot_id') that always returned null,
     * since that column doesn't exist. Walk the real chain instead.
     */
    public function getOltSlotAttribute(): ?OltSlot
    {
        return $this->ponPort?->oltSlot;
    }

    /**
     * Get the OLT through the PON port and slot. Same fix as oltSlot above —
     * there is no olt_id column on fdts.
     */
    public function getOltAttribute(): ?Olt
    {
        return $this->ponPort?->oltSlot?->olt;
    }

    /**
     * Get the FATs for the FDT.
     */
    public function fats(): HasMany
    {
        return $this->hasMany(Fat::class);
    }

    /**
     * Get the user who created this FDT.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this FDT.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Scope to get active FDTs.
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
            'maintenance' => 'bg-warning',
            default => 'bg-primary'
        };
    }

    /**
     * Get the FDT type badge class.
     */
    public function getFdtTypeBadgeClassAttribute(): string
    {
        return match(strtolower($this->fdt_type ?? '')) {
            '8-port' => 'bg-primary',
            '16-port' => 'bg-success',
            '24-port' => 'bg-info',
            '32-port' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    /**
     * Get active FATs count.
     */
    public function getActiveFatsAttribute(): int
    {
        return $this->fats()->where('status', 'active')->count();
    }

    /**
     * Get total FATs count.
     */
    public function getTotalFatsAttribute(): int
    {
        return $this->fats()->count();
    }

    /**
     * Get FDT identifier (OLT/Slot/Port/FDT format).
     */
    public function getIdentifierAttribute(): string
    {
        return "{$this->ponPort->identifier}/FDT-{$this->fdt_number}";
    }

    /**
     * Get short identifier (Port/FDT format).
     */
    public function getShortIdentifierAttribute(): string
    {
        return "Port-{$this->ponPort->pon_port_number}/FDT-{$this->fdt_number}";
    }
}
