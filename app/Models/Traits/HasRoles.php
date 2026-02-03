<?php

namespace App\Models\Traits;

use App\Models\Role;
use App\Models\Permission;

trait HasRoles
{
    public function roles(){ return $this->belongsToMany(Role::class); }

    public function assignRole($role)
    {
        $role = $role instanceof Role ? $role : Role::where('name',$role)->first();
        if($role) $this->roles()->syncWithoutDetaching([$role->id]);
    }

    public function hasRole($roles)
    {
        $roles = is_array($roles)?$roles:[$roles];
        return $this->roles()->whereIn('name',$roles)->exists();
    }

    public function hasPermission($perm)
    {
        $perm = $perm instanceof Permission ? $perm->name : $perm;
        return $this->roles()->whereHas('permissions', function($q) use ($perm){ $q->where('name',$perm); })->exists();
    }
}
