<?php

namespace Modules\Escalations\Entities;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    // 'status', 'created_by', 'edited_by' were missing here even though
    // SourceController::store()/update() both set them via mass assignment
    // (Source::create($validated) / $source->update($validated)) — Eloquent
    // silently drops any key not in $fillable, so every source's creator
    // and editor were lost immediately (the index/show pages always
    // rendered blank for those columns), and the 'status' column only ever
    // got a value from its DB default because active()/inactive() happen to
    // bypass mass assignment (direct property sets), not because store()/
    // update() worked.
    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'edited_by',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }
}
