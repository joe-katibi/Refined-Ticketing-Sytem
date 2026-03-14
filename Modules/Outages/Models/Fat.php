<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Fat extends Model
{
    use HasFactory;

    protected $fillable = [
        'fdt_id',
        'fat_number',
        'fat_type',
        'location',
        'capacity',
        'status',
        'created_by',
        'edited_by'
    ];

    protected $casts = [
        'fat_number' => 'integer',
        'capacity' => 'integer',
    ];

    /**
     * Get the FDT that owns the FAT.
     */
    public function fdt(): BelongsTo
    {
        return $this->belongsTo(Fdt::class);
    }

    /**
     * Get the PON port through the FDT.
     */
    public function ponPort(): BelongsTo
    {
        return $this->belongsTo(PonPort::class, 'pon_port_id');
    }

    /**
     * Get the OLT slot through the FDT and PON port.
     */
    public function oltSlot(): BelongsTo
    {
        return $this->belongsTo(OltSlot::class, 'olt_slot_id');
    }

    /**
     * Get the OLT through the FDT, PON port, and slot.
     */
    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }

    /**
     * Get the user who created this FAT.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this FAT.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Scope to get active FATs.
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
     * Get the FAT type badge class.
     */
    public function getFatTypeBadgeClassAttribute(): string
    {
        return match(strtolower($this->fat_type ?? '')) {
            '4-port' => 'bg-primary',
            '8-port' => 'bg-success',
            '12-port' => 'bg-info',
            '16-port' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    /**
     * Get FAT identifier (OLT/Slot/Port/FDT/FAT format).
     */
    public function getIdentifierAttribute(): string
    {
        return "{$this->fdt->identifier}/FAT-{$this->fat_number}";
    }

    /**
     * Get short identifier (FDT/FAT format).
     */
    public function getShortIdentifierAttribute(): string
    {
        return "FDT-{$this->fdt->fdt_number}/FAT-{$this->fat_number}";
    }
}
