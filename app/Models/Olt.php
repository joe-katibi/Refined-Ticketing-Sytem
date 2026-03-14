<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
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
        'status',
    ];
    protected $table = 'olts';
    protected $casts = [
      'created_at' => 'datetime:d-M-Y',
      'edited_at' => 'datetime:d-M-Y',
    ];

    public function oltSlots()
    {
        return $this->hasMany(OltSlot::class);
    }
}
