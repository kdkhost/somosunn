<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        $permissions = [
            // Dashboard
            'dashboard.view' => 'Ver dashboard',
            // Usuários
            'users.view' => 'Listar usuários',
            'users.create' => 'Criar usuário',
            'users.edit' => 'Editar usuário',
            'users.delete' => 'Excluir usuário',
            'users.impersonate' => 'Assumir sessão de usuário',
            // Cursos
            'courses.view' => 'Listar cursos',
            'courses.create' => 'Criar curso',
            'courses.edit' => 'Editar curso',
            'courses.delete' => 'Excluir curso',
            'courses.publish' => 'Publicar/arquivar curso',
            // Mentorias
            'mentorships.view' => 'Listar mentorias',
            'mentorships.create' => 'Criar mentoria',
            'mentorships.edit' => 'Editar mentoria',
            'mentorships.delete' => 'Excluir mentoria',
            'mentorships.schedule' => 'Agendar sessão',
            // Eventos
            'events.view' => 'Listar eventos',
            'events.create' => 'Criar evento',
            'events.edit' => 'Editar evento',
            'events.delete' => 'Excluir evento',
            'events.publish' => 'Publicar/encerrar evento',
            'events.ticket.manage' => 'Gerenciar ingressos',
            // Planos/Pacotes
            'plans.view' => 'Listar planos',
            'plans.create' => 'Criar plano',
            'plans.edit' => 'Editar plano',
            'plans.delete' => 'Excluir plano',
            'plans.feature.toggle' => 'Destacar/ocultar plano',
            'plans.discount.manage' => 'Gerenciar descontos',
            // Certificados
            'certificates.generate' => 'Gerar certificado',
            'certificates.view' => 'Listar certificados',
            'certificates.delete' => 'Excluir certificado',
            // Pontuação/Ranking
            'points.rules.manage' => 'Gerenciar regras de pontos',
            'ranking.view' => 'Ver ranking',
            'ranking.edit' => 'Editar ranking',
            // E-mails/Templates
            'mailtemplates.view' => 'Listar templates',
            'mailtemplates.create' => 'Criar template',
            'mailtemplates.edit' => 'Editar template',
            'mailtemplates.delete' => 'Excluir template',
            'mail.sendtest' => 'Enviar e-mail de teste',
            // Uploads
            'uploads.manage' => 'Gerenciar uploads',
            // Configurações
            'settings.view' => 'Ver configurações',
            'settings.update' => 'Atualizar configurações',
            'settings.smtp.test' => 'Testar SMTP',
            'settings.pwa.toggle' => 'Ativar/desativar PWA',
            'settings.branding.update' => 'Atualizar branding (logo/preloader)',
            // Permissões
            'permissions.view' => 'Listar permissões',
            'permissions.assign' => 'Atribuir permissões',
            'permissions.sync' => 'Sincronizar permissões',
            'roles.manage' => 'Gerenciar papéis',
        ];

        $roles = [
            'superadmin' => 'Super Administrador',
            'admin'      => 'Administrador',
            'gestor'     => 'Gestor de conteúdo',
            'suporte'    => 'Suporte',
            'membro'     => 'Membro',
        ];

        // Insere permissões
        foreach ($permissions as $name => $label) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['label' => $label, 'updated_at' => $timestamp, 'created_at' => $timestamp]
            );
        }

        // Insere roles
        foreach ($roles as $name => $label) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name],
                ['label' => $label, 'updated_at' => $timestamp, 'created_at' => $timestamp]
            );
        }

        $permIds = DB::table('permissions')->pluck('id', 'name');
        $roleIds = DB::table('roles')->pluck('id', 'name');

        $giveAll = $permIds->values()->all();
        $subset = function(array $names) use ($permIds) {
            return $permIds->only($names)->values()->all();
        };

        $rolePerms = [
            'superadmin' => $giveAll,
            'admin'      => $giveAll, // se quiser limitar, troque a lista
            'gestor'     => $subset([
                'dashboard.view',
                'courses.view','courses.create','courses.edit','courses.delete','courses.publish',
                'mentorships.view','mentorships.create','mentorships.edit','mentorships.delete','mentorships.schedule',
                'events.view','events.create','events.edit','events.delete','events.publish','events.ticket.manage',
                'plans.view','plans.edit','plans.feature.toggle','plans.discount.manage',
                'certificates.generate','certificates.view',
                'ranking.view','ranking.edit',
                'mailtemplates.view','mailtemplates.create','mailtemplates.edit','mail.sendtest',
                'uploads.manage'
            ]),
            'suporte'    => $subset([
                'dashboard.view',
                'users.view','users.edit',
                'mailtemplates.view','mail.sendtest',
                'uploads.manage',
                'events.view','mentorships.view','courses.view'
            ]),
            'membro'     => $subset([
                'dashboard.view',
                'events.view',
                'courses.view',
                'mentorships.view',
                'ranking.view'
            ]),
        ];

        // limpa vínculos antigos
        DB::table('permission_role')->truncate();
        DB::table('role_user')->truncate();

        foreach ($rolePerms as $roleName => $permIdList) {
            $roleId = $roleIds[$roleName] ?? null;
            if(!$roleId) continue;
            $rows = array_map(fn($pid)=>['role_id'=>$roleId,'permission_id'=>$pid], $permIdList);
            DB::table('permission_role')->insert($rows);
        }

        // atribui superadmin ao primeiro usuário, se existir
        $userId = DB::table('users')->min('id');
        if($userId && isset($roleIds['superadmin'])){
            DB::table('role_user')->updateOrInsert(
                ['role_id'=>$roleIds['superadmin'], 'user_id'=>$userId],
                ['role_id'=>$roleIds['superadmin'], 'user_id'=>$userId]
            );
        }
    }
}
