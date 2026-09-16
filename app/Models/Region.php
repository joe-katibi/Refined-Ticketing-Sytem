<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Region extends Model
{
    protected $fillable = ['name', 'status', 'created_by'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }
}
