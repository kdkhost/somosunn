<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Policies podem ser registradas aqui se necessário
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            // superadmin tem acesso total
            $isSuper = DB::table('role_user')
                ->join('roles','roles.id','=','role_user.role_id')
                ->where('role_user.user_id',$user->id)
                ->where('roles.name','superadmin')
                ->exists();
            if($isSuper) return true;

            $perms = $this->collectUserPermissions($user);
            return $perms->contains($ability) ? true : null;
        });
    }

    private function collectUserPermissions($user)
    {
        $perms = collect();

        // Permissões de papéis
        $rolePerms = DB::table('role_user')
            ->join('permission_role','permission_role.role_id','=','role_user.role_id')
            ->join('permissions','permissions.id','=','permission_role.permission_id')
            ->where('role_user.user_id',$user->id)
            ->pluck('permissions.name');
        $perms = $perms->merge($rolePerms);

        // Permissões do plano ativo (se houver coluna plan_id)
        if(Schema::hasColumn('users','plan_id') && $user->plan_id){
            $planPerms = DB::table('permission_plan')
                ->join('permissions','permissions.id','=','permission_plan.permission_id')
                ->where('permission_plan.plan_id',$user->plan_id)
                ->pluck('permissions.name');
            $perms = $perms->merge($planPerms);
        }

        return $perms->unique();
    }
}
