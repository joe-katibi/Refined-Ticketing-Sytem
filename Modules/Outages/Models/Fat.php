<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Get the PON port through the FDT. There is no pon_port_id/olt_slot_id/
     * olt_id column on fats (only fdt_id, per $fillable) — these used to be
     * belongsTo() relations against columns that don't exist, always
     * returning null. Walk the real chain through fdt->ponPort instead.
     */
    public function getPonPortAttribute(): ?PonPort
    {
        return $this->fdt?->ponPort;
    }

    public function getOltSlotAttribute(): ?OltSlot
    {
        return $this->fdt?->ponPort?->oltSlot;
    }

    public function getOltAttribute(): ?Olt
    {
        return $this->fdt?->ponPort?->oltSlot?->olt;
    }

    /**
     * Get the customers whose ONU connects at this FAT.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
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
