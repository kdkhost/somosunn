<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SponsorPermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $timestamp = now();
        $permissions = [
            'sponsor.dashboard' => ['label' => 'Acessar painel do patrocinador', 'category' => 'Patrocinadores'],
            'sponsor.leads' => ['label' => 'Acessar leads do patrocinador', 'category' => 'Patrocinadores'],
            'sponsor.billing' => ['label' => 'Acessar financeiro do patrocinador', 'category' => 'Patrocinadores'],
            'sponsor.reports' => ['label' => 'Acessar relatorios do patrocinador', 'category' => 'Patrocinadores'],
            'sponsor.events' => ['label' => 'Acessar eventos patrocinados', 'category' => 'Patrocinadores'],
            'sponsor.campaigns' => ['label' => 'Acessar campanhas do patrocinador', 'category' => 'Patrocinadores'],
            'admin.sponsors.view' => ['label' => 'Ver patrocinadores', 'category' => 'Patrocinadores'],
            'admin.sponsors.create' => ['label' => 'Criar patrocinadores', 'category' => 'Patrocinadores'],
            'admin.sponsors.edit' => ['label' => 'Editar patrocinadores', 'category' => 'Patrocinadores'],
            'admin.sponsors.delete' => ['label' => 'Excluir patrocinadores', 'category' => 'Patrocinadores'],
            'admin.sponsor_plans.view' => ['label' => 'Ver planos de patrocinio', 'category' => 'Patrocinadores'],
            'admin.sponsor_plans.create' => ['label' => 'Criar planos de patrocinio', 'category' => 'Patrocinadores'],
            'admin.sponsor_plans.edit' => ['label' => 'Editar planos de patrocinio', 'category' => 'Patrocinadores'],
            'admin.sponsor_plans.delete' => ['label' => 'Excluir planos de patrocinio', 'category' => 'Patrocinadores'],
            'admin.sponsor_banners.view' => ['label' => 'Ver banners patrocinados', 'category' => 'Patrocinadores'],
            'admin.sponsor_banners.create' => ['label' => 'Criar banners patrocinados', 'category' => 'Patrocinadores'],
            'admin.sponsor_banners.edit' => ['label' => 'Editar banners patrocinados', 'category' => 'Patrocinadores'],
            'admin.sponsor_banners.delete' => ['label' => 'Excluir banners patrocinados', 'category' => 'Patrocinadores'],
            'admin.companies.view' => ['label' => 'Ver empresas', 'category' => 'Empresas'],
            'admin.companies.create' => ['label' => 'Criar empresas', 'category' => 'Empresas'],
            'admin.companies.edit' => ['label' => 'Editar empresas', 'category' => 'Empresas'],
            'admin.companies.delete' => ['label' => 'Excluir empresas', 'category' => 'Empresas'],
        ];

        $hasCategory = Schema::hasColumn('permissions', 'category');
        $hasSortOrder = Schema::hasColumn('permissions', 'sort_order');

        foreach ($permissions as $name => $definition) {
            $payload = [
                'label' => $definition['label'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if ($hasCategory) {
                $payload['category'] = $definition['category'];
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
                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['role_id' => $roleId, 'permission_id' => $permissionId]
                );
            }
        }
    }
}
