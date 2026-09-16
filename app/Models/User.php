<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Department;
use App\Models\UserCategory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'is_admin',
        'country',
        'services',
        'category',
        'position',
        'password',
        'created_by',
        'phone',
        'user_status',
        'department_id',
        'sub_department_id',
        'team_type_id',
        'sub_team_type_id',
        'region_id',
        'supervisor_id',
        'is_first_login',
        'password_changed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'is_first_login' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public static function getUsers()
    {
        $users = DB::table('users')->orderBy('id', 'asc')->get();
        return $users;
    }

    public function department()
    {
        return $this->belongsTo(Department::class , 'department_id' , 'id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    // In User.php
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withTimestamps();
    }



    public function userCategory()
    {
        return $this->belongsTo(UserCategory::class , 'user_id' , 'id');
    }
    public function categories(): BelongsToMany
   {
    return $this->belongsToMany(
        Category::class,
        'user_categories',
        'user_id',
        'category_id'
    );
   }

    /**
     * Get the team type that the user belongs to.
     */
    public function teamType()
    {
        return $this->belongsTo(\App\Models\TeamType::class, 'team_type_id');
    }

    /**
     * Get the sub team type that the user belongs to.
     */
    public function subTeamType()
    {
        return $this->belongsTo(\App\Models\SubTeamType::class, 'sub_team_type_id');
    }

}
