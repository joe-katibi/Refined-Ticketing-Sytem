<?php

namespace App\Models;

use App\Traits\SerializesDates;
use Spatie\Permission\Models\Permission as Model;


class Permission extends Model
{
    use SerializesDates;

    protected $fillable = [
        'guard_name',
        'module',
        'sub_module',
        'description',
        'created_at',
        'updated_at'
    ];

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function modules()
    {
        $modules = Permission::all()->groupBy('module')->keys();
        return $modules;
    }

    /**
     * @param string $module
     * @return \Illuminate\Support\Collection
     */
    public static function subModules($module = '')
    {
        if (empty($module)) {
            $sub_modules = Permission::all()->groupBy('sub_module')->keys();
        } else {
            $sub_modules = Permission::where('module', '=', $module)->get()
                ->groupBy('sub_module')->keys();
        }
        return $sub_modules;
    }

    /**
     * @param $module
     * @param $sub_module
     * @return \Illuminate\Support\Collection
     */
    public static function filter($module, $sub_module)
    {
        $permissions = Permission::query();

        if (!empty($module)) {
            $permissions = $permissions->where('module', '=', $module);
        }

        if (!empty($sub_module)) {
            $permissions = $permissions->where('sub_module', '=', $sub_module);
        }
        $permissions = $permissions->get();

        return $permissions;
    }

    // public function jsonSerialize()
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'guard_name' => $this->guard_name,
    //         'module' => $this->module,
    //         'sub_module' => $this->sub_module,
    //         'description' => $this->description
    //     ];
    // }
}

