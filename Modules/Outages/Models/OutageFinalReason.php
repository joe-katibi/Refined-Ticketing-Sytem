<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutageFinalReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'final_reason_name',
        'final_reason_description',
        'final_reason_status',
        'created_by',
        'edited_by'
    ];

    protected $casts = [
        'final_reason_status' => 'string',
    ];

    /**
     * Get the user who created this reason
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last edited this reason
     */
    public function editor()
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }
}
