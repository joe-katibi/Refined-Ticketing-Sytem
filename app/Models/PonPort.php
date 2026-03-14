<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PonPort extends Model
{
    protected $fillable = [
        'olt_slot_id',
        'pon_port_number',
        'pon_port_type',
        'status',
        'created_by',
        'edited_by',
    ];
    protected $casts = [
      'created_at' => 'datetime:d-M-Y',
      'edited_at' => 'datetime:d-M-Y',
    ];
    protected $table = 'pon_ports';
    public function oltSlot()
    {
        return $this->belongsTo(OltSlot::class , 'olt_slot_id' , 'id');
    }
}
