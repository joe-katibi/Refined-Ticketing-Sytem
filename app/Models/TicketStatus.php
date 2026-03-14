<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;

    protected $fillable=[
        'status_id',
        'status_name',
        'created_by',


    ];
    protected $casts = [
        'created_at' => 'datetime:d-M-Y'
    ];

}
