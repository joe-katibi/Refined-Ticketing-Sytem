<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Olt extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vendor',
        'model',
        'ip_address',
        'location',
        'total_slots',
        'software_version',
        'created_by',
        'edited_by',
        'status'
    ];

    protected $casts = [
        'total_slots' => 'integer',
    ];

    /**
     * Get the slots for the OLT.
     */
    public function slots(): HasMany
    {
        return $this->hasMany(OltSlot::class);
    }

    /**
     * Get all PON ports through slots.
     */
    public function ponPorts(): HasManyThrough
    {
        return $this->hasManyThrough(PonPort::class, OltSlot::class);
    }

    /**
     * Get the user who created this OLT.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this OLT.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Scope to get active OLTs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'Active' => 'bg-success',
            'Inactive' => 'bg-secondary',
            'Maintenance' => 'bg-warning',
            'Faulty' => 'bg-danger',
            default => 'bg-primary'
        };
    }

    /**
     * Get the vendor badge class.
     */
    public function getVendorBadgeClassAttribute(): string
    {
        return match(strtolower($this->vendor ?? '')) {
            'huawei' => 'bg-danger',
            'zte' => 'bg-primary',
            'fiberhome' => 'bg-success',
            'nokia' => 'bg-info',
            default => 'bg-secondary'
        };
    }

    /**
     * Get available slots count.
     */
    public function getAvailableSlotsAttribute(): int
    {
        // total_slots is nullable (e.g. OLTs created via bulk upload never
        // set it, since the spreadsheet has no such column) — without this
        // guard a null total_slots renders as a confusing negative number.
        if ($this->total_slots === null) {
            return 0;
        }

        return max(0, $this->total_slots - $this->slots()->count());
    }

    /**
     * Get active slots count.
     */
    public function getActiveSlotsAttribute(): int
    {
        return $this->slots()->where('olt_slots.status', 'active')->count();
    }

    /**
     * Get total PON ports count.
     */
    public function getTotalPonPortsAttribute(): int
    {
        return $this->ponPorts()->count();
    }

    /**
     * Get active PON ports count.
     */
    public function getActivePonPortsAttribute(): int
    {
        return $this->ponPorts()->where('pon_ports.status', 'active')->count();
    }
}
