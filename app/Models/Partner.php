<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Partner extends Model
{
    protected $fillable = [
        'partner_name',
        'description',
        'status',
        'created_by',
        'edited_by',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class, 'partner_id', 'id');
    }

    /**
     * Get the user who created this partner
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this partner
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
