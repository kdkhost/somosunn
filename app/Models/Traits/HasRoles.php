<?php

namespace App\Models\Traits;

use App\Models\Role;
use App\Models\Permission;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function assignRole($role)
    {
        $role = $role instanceof Role ? $role : Role::where('name', $role)->first();
        if ($role)
            $this->roles()->syncWithoutDetaching([$role->id]);
    }

    public function hasRole($roles)
    {
        if ($this->isAdmin()) {
            return true;
        }
        $roles = is_array($roles) ? $roles : [$roles];
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission($perm)
    {
        if ($this->isAdmin()) {
            return true;
        }

        $perm = $perm instanceof Permission ? $perm->name : $perm;
        $hasRolePermission = $this->roles()->whereHas('permissions', function ($q) use ($perm) {
            $q->where('name', $perm);
        })->exists();

        if ($hasRolePermission) {
            return true;
        }

        if (!method_exists($this, 'canAccessFeature')) {
            return false;
        }

        $perm = trim((string) $perm);
        if ($perm === '') {
            return false;
        }

        $featureCandidates = [$perm, str_replace('.', '_', $perm)];

        if (preg_match('/^([a-z_]+)\.(view|access|create|edit|delete)$/', $perm, $matches)) {
            $base = $matches[1];
            $action = $matches[2];

            $featureCandidates[] = $base;
            $featureCandidates[] = $base . '_access';

            if (in_array($action, ['view', 'access'], true)) {
                $featureCandidates[] = $base . '.create';
                $featureCandidates[] = $base . '.edit';
                $featureCandidates[] = $base . '.delete';
                $featureCandidates[] = $base . '_create';
                $featureCandidates[] = $base . '_edit';
                $featureCandidates[] = $base . '_delete';
            }

            if (!in_array($action, ['view', 'access'], true)) {
                $featureCandidates[] = $base . '_' . $action;
            }
        }

        if (in_array($perm, ['events.view', 'events.access'], true)) {
            $featureCandidates[] = 'events.exhibitors.manage';
            $featureCandidates[] = 'events_exhibitors_manage';
        }

        if (str_starts_with($perm, 'certificates.')) {
            $featureCandidates[] = 'courses.certificates';
            $featureCandidates[] = 'certificates_access';
        }

        foreach (array_values(array_unique($featureCandidates)) as $feature) {
            if (!is_string($feature) || trim($feature) === '') {
                continue;
            }

            if ($this->canAccessFeature($feature)) {
                return true;
            }
        }

        return false;
    }
}
