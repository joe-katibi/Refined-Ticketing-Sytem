<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class Team extends Model
{
    protected $table = 'operational_teams';
    
    protected $fillable = [
        'team_type_id',
        'partner_id',
        'team_name',
        'description',
        'status',
        'created_by',
        'edited_by',
    ];

    /**
     * Get the team type that owns the team.
     */
    public function teamType(): BelongsTo
    {
        return $this->belongsTo(TeamType::class, 'team_type_id');
    }

    /**
     * Get the partner that owns the team.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    /**
     * The users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    /**
     * Get the user who created the team.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited the team.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
