<?php

namespace Modules\Escalations\Entities;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
}
