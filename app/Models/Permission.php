<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name', 'module', 'description'];

    /**
     * Override to include module in the unique check
     */
    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        // Check if permission exists with same name, guard, AND module
        $exists = static::query()
            ->where('name', $attributes['name'])
            ->where('guard_name', $attributes['guard_name'])
            ->where('module', $attributes['module'] ?? null)
            ->exists();
        if ($exists) {
            throw \Spatie\Permission\Exceptions\PermissionAlreadyExists::create(
                $attributes['name'], 
                $attributes['guard_name']
            );
        }

        // Use query builder to bypass parent's create logic
        $permission = static::query()->create($attributes);
        
        // Clear cache properly
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        return $permission;
    }

    /**
     * Get permissions grouped by module
     */
    public static function getGroupedByModule($guardName = null)
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        
        return static::where('guard_name', $guardName)
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }

    /**
     * Get all unique modules
     */
    public static function getModules($guardName = null)
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);
        
        return static::where('guard_name', $guardName)
            ->distinct()
            ->pluck('module')
            ->sort()
            ->values();
    }
}