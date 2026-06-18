<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventCouponPermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $timestamp = now();
        $permissions = [
            'admin.events.coupons.view' => 'Ver cupons de eventos',
            'admin.events.coupons.create' => 'Criar cupons de eventos',
            'admin.events.coupons.edit' => 'Editar cupons de eventos',
            'admin.events.coupons.delete' => 'Excluir cupons de eventos',
            'admin.events.coupons.toggle' => 'Ativar/desativar cupons de eventos',
            'admin.events.group_link.manage' => 'Gerenciar link do grupo do evento',
        ];

        $hasCategory = Schema::hasColumn('permissions', 'category');
        $hasSortOrder = Schema::hasColumn('permissions', 'sort_order');

        foreach ($permissions as $name => $label) {
            $payload = [
                'label' => $label,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if ($hasCategory) {
                $payload['category'] = 'Eventos';
            }

            if ($hasSortOrder) {
                $payload['sort_order'] = 0;
            }

            DB::table('permissions')->updateOrInsert(['name' => $name], $payload);
        }

        if (!Schema::hasTable('roles') || !Schema::hasTable('permission_role')) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['superadmin', 'admin'])
            ->pluck('id');

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_keys($permissions))
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ], [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
}
