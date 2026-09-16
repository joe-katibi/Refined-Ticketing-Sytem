<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'name',
        'mobile_number',
        'alternative_number',
        'address',
        'onu_type',
        'onu_physical_address',
        'bandwidth_profile',
        'fat_id',
        'status',
        'created_by',
        'edited_by',
    ];

    /**
     * Get the FAT this customer's ONU connects at.
     */
    public function fat(): BelongsTo
    {
        return $this->belongsTo(Fat::class);
    }

    public function getFdtAttribute(): ?Fdt
    {
        return $this->fat?->fdt;
    }

    public function getPonPortAttribute(): ?PonPort
    {
        return $this->fat?->fdt?->ponPort;
    }

    public function getOltSlotAttribute(): ?OltSlot
    {
        return $this->fat?->fdt?->ponPort?->oltSlot;
    }

    public function getOltAttribute(): ?Olt
    {
        return $this->fat?->fdt?->ponPort?->oltSlot?->olt;
    }

    /**
     * Get the user who created this customer record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this customer record.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Active' => 'bg-success',
            'Inactive' => 'bg-secondary',
            'Suspended' => 'bg-warning',
            default => 'bg-primary',
        };
    }
}
