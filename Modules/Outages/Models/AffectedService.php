<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AffectedService extends Model
{
    use HasFactory;

    protected $table = 'affected_services';

    protected $fillable = [
        'service_name',
        'service_description',
        'service_status',
        'created_by',
        'edited_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this affected service.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this affected service.
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
