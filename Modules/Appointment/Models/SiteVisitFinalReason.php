<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class SiteVisitFinalReason extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'site_visit_final_reasons';

    protected $fillable = [
        'final_reason_name',
        'final_reason_description',
        'final_reason_status',
        'created_by',
        'edited_by'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the user who created this final reason
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this final reason
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Get status options for dropdown
     */
    public static function getStatusOptions()
    {
        return [
            'Active' => 'Active',
            'Inactive' => 'Inactive'
        ];
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->final_reason_status === 'Active' ? 'badge-success' : 'badge-secondary';
    }
}
