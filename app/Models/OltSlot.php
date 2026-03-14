<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OltSlot extends Model
{
    protected $fillable = [
        'olt_id',
        'slot_number',
        'slot_type',
        'status',
        'created_by',
        'edited_by',
    ];
    protected $casts = [
      'created_at' => 'datetime:d-M-Y',
      'edited_at' => 'datetime:d-M-Y',
    ];
    protected $table = 'olt_slots';
    
    public function olt()
    {
        return $this->belongsTo(Olt::class , 'olt_id' , 'id');
    }
    public function ponPorts()
    {
        return $this->hasMany(PonPort::class);
    }
}
