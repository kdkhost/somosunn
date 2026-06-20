<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Certificate::class => \App\Policies\CertificatePolicy::class,
        \App\Models\Company::class => \App\Policies\CompanyPolicy::class,
        \App\Models\Sponsor::class => \App\Policies\SponsorPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return true;
            }

            // superadmin tem acesso total
            $isSuper = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $user->id)
                ->where('roles.name', 'superadmin')
                ->exists();
            if ($isSuper)
                return true;

            $perms = $this->collectUserPermissions($user);
            return $perms->contains($ability) ? true : null;
        });
    }

    private function collectUserPermissions($user)
    {
        $perms = collect();

        // Permissões de papéis
        $rolePerms = DB::table('role_user')
            ->join('permission_role', 'permission_role.role_id', '=', 'role_user.role_id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('role_user.user_id', $user->id)
            ->pluck('permissions.name');
        $perms = $perms->merge($rolePerms);

        // Permissões do plano ativo (se houver coluna plan_id)
        return $perms->unique();
    }
}
