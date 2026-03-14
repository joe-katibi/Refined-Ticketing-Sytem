<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Team;

class OutageActivity extends Model
{
    use HasFactory;

    protected $table = 'outage_activities';

    protected $fillable = [
        'outage_id',
        'user_id',
        'activity_type',
        'activity_title',
        'activity_description',
        'old_values',
        'new_values',
        'old_status',
        'new_status',
        'old_assigned_to',
        'new_assigned_to',
        'old_assigned_team_id',
        'new_assigned_team_id',
        'ip_address',
        'user_agent',
        'is_system_generated',
        'is_major_activity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'is_system_generated' => 'boolean',
        'is_major_activity' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the outage that owns the activity.
     */
    public function outage()
    {
        return $this->belongsTo(Outage::class);
    }

    /**
     * Get the user who performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the old assigned user.
     */
    public function oldAssignedUser()
    {
        return $this->belongsTo(User::class, 'old_assigned_to');
    }

    /**
     * Get the new assigned user.
     */
    public function newAssignedUser()
    {
        return $this->belongsTo(User::class, 'new_assigned_to');
    }

    /**
     * Get the old assigned team.
     */
    public function oldAssignedTeam()
    {
        return $this->belongsTo(Team::class, 'old_assigned_team_id');
    }

    /**
     * Get the new assigned team.
     */
    public function newAssignedTeam()
    {
        return $this->belongsTo(Team::class, 'new_assigned_team_id');
    }

    /**
     * Get the activity icon based on activity type.
     */
    public function getActivityIconAttribute()
    {
        return match($this->activity_type) {
            'created' => 'bx-plus-circle',
            'updated' => 'bx-edit',
            'status_changed' => 'bx-refresh',
            'progress_added' => 'bx-time',
            'assigned' => 'bx-user',
            'resolved' => 'bx-check-circle',
            'closed' => 'bx-x-circle',
            'reopened' => 'bx-revision',
            'edited' => 'bx-edit-alt',
            'attachment_added' => 'bx-paperclip',
            'attachment_removed' => 'bx-trash',
            'comment_added' => 'bx-comment',
            default => 'bx-info-circle'
        };
    }

    /**
     * Get the activity color based on activity type.
     */
    public function getActivityColorAttribute()
    {
        return match($this->activity_type) {
            'created' => 'success',
            'updated' => 'info',
            'status_changed' => 'warning',
            'progress_added' => 'primary',
            'assigned' => 'info',
            'resolved' => 'success',
            'closed' => 'secondary',
            'reopened' => 'warning',
            'edited' => 'info',
            'attachment_added' => 'primary',
            'attachment_removed' => 'danger',
            'comment_added' => 'primary',
            default => 'secondary'
        };
    }

    /**
     * Get the activity color (method version).
     */
    public function getActivityColor()
    {
        return $this->getActivityColorAttribute();
    }

    /**
     * Get the activity title.
     */
    public function getActivityTitle()
    {
        return $this->activity_title;
    }

    /**
     * Get the description attribute (accessor).
     */
    public function getDescriptionAttribute()
    {
        return $this->activity_description;
    }

    /**
     * Get the metadata attribute (combines old_values and new_values).
     */
    public function getMetadataAttribute()
    {
        $metadata = [];
        
        if ($this->old_values && $this->new_values) {
            // For field changes, create old_value and new_value entries
            foreach ($this->new_values as $key => $newValue) {
                if (isset($this->old_values[$key])) {
                    $metadata['old_value'] = $this->old_values[$key];
                    $metadata['new_value'] = $newValue;
                    break; // Just show the first change for now
                }
            }
        }
        
        // Add any other metadata fields that might be needed
        if ($this->activity_type === 'progress_added') {
            $metadata['progress_text'] = $this->activity_description;
        }
        
        return $metadata;
    }

    /**
     * Create an activity log entry.
     */
    public static function logActivity(array $data)
    {
        return self::create(array_merge($data, [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));
    }

    /**
     * Log outage creation.
     */
    public static function logCreation(Outage $outage, User $user)
    {
        return self::logActivity([
            'outage_id' => $outage->id,
            'user_id' => $user->id,
            'activity_type' => 'created',
            'activity_title' => 'Outage Created',
            'activity_description' => "Outage {$outage->ticket_number} was created with status: {$outage->status_display}",
            'new_status' => $outage->status,
            'is_major_activity' => true,
        ]);
    }

    /**
     * Log status change.
     */
    public static function logStatusChange(Outage $outage, string $oldStatus, string $newStatus, User $user, string $notes = null)
    {
        $statusOptions = Outage::getStatusOptions();
        $oldStatusDisplay = $statusOptions[$oldStatus] ?? $oldStatus;
        $newStatusDisplay = $statusOptions[$newStatus] ?? $newStatus;

        return self::logActivity([
            'outage_id' => $outage->id,
            'user_id' => $user->id,
            'activity_type' => 'status_changed',
            'activity_title' => 'Status Changed',
            'activity_description' => "Status changed from '{$oldStatusDisplay}' to '{$newStatusDisplay}'" . ($notes ? ". Notes: {$notes}" : ''),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'is_major_activity' => true,
        ]);
    }

    /**
     * Log progress addition.
     */
    public static function logProgressAdded(Outage $outage, User $user, string $notes)
    {
        return self::logActivity([
            'outage_id' => $outage->id,
            'user_id' => $user->id,
            'activity_type' => 'progress_added',
            'activity_title' => 'Progress Update Added',
            'activity_description' => "Progress update added: " . \Str::limit($notes, 100),
            'is_major_activity' => false,
        ]);
    }

    /**
     * Log outage update.
     */
    public static function logUpdate(Outage $outage, User $user, array $oldValues, array $newValues)
    {
        $changes = [];
        foreach ($newValues as $key => $value) {
            if (isset($oldValues[$key]) && $oldValues[$key] != $value) {
                $changes[] = ucfirst(str_replace('_', ' ', $key));
            }
        }

        return self::logActivity([
            'outage_id' => $outage->id,
            'user_id' => $user->id,
            'activity_type' => 'updated',
            'activity_title' => 'Outage Updated',
            'activity_description' => "Updated fields: " . implode(', ', $changes),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'is_major_activity' => true,
        ]);
    }
}
