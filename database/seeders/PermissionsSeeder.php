<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        // Permissões organizadas por categoria com ordem
        $permissionsByCategory = [
            'dashboard' => [
                'dashboard.view' => 'Ver dashboard',
            ],
            'usuarios' => [
                'users.view' => 'Listar usuários',
                'users.create' => 'Criar usuário',
                'users.edit' => 'Editar usuário',
                'users.delete' => 'Excluir usuário',
                'users.impersonate' => 'Assumir sessão de usuário',
                'users.export' => 'Exportar usuários',
            ],
            'cursos' => [
                'courses.view' => 'Listar cursos',
                'courses.create' => 'Criar curso',
                'courses.edit' => 'Editar curso',
                'courses.delete' => 'Excluir curso',
                'courses.publish' => 'Publicar/arquivar curso',
                'courses.enrollments' => 'Gerenciar matrículas',
            ],
            'mentorias' => [
                'mentorships.view' => 'Listar mentorias',
                'mentorships.create' => 'Criar mentoria',
                'mentorships.edit' => 'Editar mentoria',
                'mentorships.delete' => 'Excluir mentoria',
                'mentorships.schedule' => 'Agendar sessão',
                'mentorships.sessions' => 'Gerenciar sessões',
            ],
            'eventos' => [
                'events.view' => 'Listar eventos',
                'events.create' => 'Criar evento',
                'events.edit' => 'Editar evento',
                'events.delete' => 'Excluir evento',
                'events.publish' => 'Publicar/encerrar evento',
                'events.ticket.manage' => 'Gerenciar ingressos',
                'events.exhibitors.manage' => 'Gerenciar áreas de expositores',
                'events.checkin' => 'Fazer check-in de participantes',
            ],
            'planos' => [
                'plans.view' => 'Listar planos',
                'plans.create' => 'Criar plano',
                'plans.edit' => 'Editar plano',
                'plans.delete' => 'Excluir plano',
                'plans.feature.toggle' => 'Destacar/ocultar plano',
                'plans.discount.manage' => 'Gerenciar descontos',
            ],
            'vendas' => [
                'orders.view' => 'Listar vendas/pedidos',
                'orders.edit' => 'Editar pedidos',
                'orders.cancel' => 'Cancelar pedidos',
                'orders.refund' => 'Processar reembolsos',
                'orders.export' => 'Exportar vendas',
            ],
            'faturas' => [
                'invoices.view' => 'Listar faturas',
                'invoices.create' => 'Criar fatura manual',
                'invoices.send' => 'Enviar fatura por e-mail',
                'invoices.delete' => 'Excluir faturas',
            ],
            'cupons' => [
                'coupons.view' => 'Listar cupons',
                'coupons.create' => 'Criar cupom',
                'coupons.edit' => 'Editar cupom',
                'coupons.delete' => 'Excluir cupom',
            ],
            'certificados' => [
                'certificates.view' => 'Listar certificados',
                'certificates.generate' => 'Gerar certificado',
                'certificates.delete' => 'Excluir certificado',
                'certificates.templates' => 'Gerenciar templates',
            ],
            'pontuacao' => [
                'points.rules.manage' => 'Gerenciar regras de pontos',
                'ranking.view' => 'Ver ranking',
                'ranking.edit' => 'Editar ranking',
                'points.adjust' => 'Ajustar pontos de usuários',
            ],
            'comunidade' => [
                'community.view' => 'Ver comunidade/feed',
                'community.moderate' => 'Moderar posts',
                'community.delete' => 'Excluir posts',
                'community.pin' => 'Fixar posts',
            ],
            'emails' => [
                'mailtemplates.view' => 'Listar templates',
                'mailtemplates.create' => 'Criar template',
                'mailtemplates.edit' => 'Editar template',
                'mailtemplates.delete' => 'Excluir template',
                'mail.sendtest' => 'Enviar e-mail de teste',
                'mail.bulk' => 'Enviar e-mail em massa',
            ],
            'depoimentos' => [
                'testimonials.view' => 'Listar depoimentos',
                'testimonials.moderate' => 'Moderar depoimentos',
                'testimonials.delete' => 'Excluir depoimentos',
            ],
            'faq' => [
                'faq.view' => 'Listar perguntas frequentes',
                'faq.create' => 'Criar pergunta',
                'faq.edit' => 'Editar pergunta',
                'faq.delete' => 'Excluir pergunta',
            ],
            'uploads' => [
                'uploads.view' => 'Listar arquivos',
                'uploads.manage' => 'Gerenciar uploads',
                'uploads.delete' => 'Excluir arquivos',
            ],
            'gateways' => [
                'gateways.view' => 'Ver integrações de pagamento',
                'gateways.manage' => 'Gerenciar gateways',
                'gateways.webhooks' => 'Ver logs de webhooks',
            ],
            'relatorios' => [
                'reports.view' => 'Ver relatórios',
                'reports.export' => 'Exportar relatórios',
                'reports.financial' => 'Relatórios financeiros',
                'reports.users' => 'Relatórios de usuários',
            ],
            'configuracoes' => [
                'settings.view' => 'Ver configurações',
                'settings.update' => 'Atualizar configurações',
                'settings.smtp.test' => 'Testar SMTP',
                'settings.pwa.toggle' => 'Ativar/desativar PWA',
                'settings.branding.update' => 'Atualizar branding',
                'settings.adsense' => 'Configurar AdSense',
            ],
            'fontes' => [
                'fonts.view' => 'Listar fontes',
                'fonts.manage' => 'Gerenciar fontes personalizadas',
            ],
            'permissoes' => [
                'permissions.view' => 'Listar permissões',
                'permissions.assign' => 'Atribuir permissões',
                'permissions.sync' => 'Sincronizar permissões',
                'roles.manage' => 'Gerenciar papéis',
            ],
        ];

        // Nomes amigáveis das categorias
        $categoryLabels = [
            'dashboard' => 'Dashboard',
            'usuarios' => 'Usuários',
            'cursos' => 'Cursos',
            'mentorias' => 'Mentorias',
            'eventos' => 'Eventos',
            'planos' => 'Planos',
            'vendas' => 'Vendas',
            'faturas' => 'Faturas',
            'cupons' => 'Cupons',
            'certificados' => 'Certificados',
            'pontuacao' => 'Pontuação',
            'comunidade' => 'Comunidade',
            'emails' => 'E-mails',
            'depoimentos' => 'Depoimentos',
            'faq' => 'FAQ',
            'uploads' => 'Uploads',
            'gateways' => 'Pagamentos',
            'relatorios' => 'Relatórios',
            'configuracoes' => 'Configurações',
            'fontes' => 'Fontes',
            'permissoes' => 'Permissões',
        ];

        $roles = [
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'gestor' => 'Gestor de conteúdo',
            'suporte' => 'Suporte',
            'membro' => 'Membro',
        ];

        // Verifica se as colunas category/sort_order existem
        $hasCategory = \Schema::hasColumn('permissions', 'category');

        // Insere permissões com categoria e ordem
        $sortOrder = 0;
        foreach ($permissionsByCategory as $category => $perms) {
            foreach ($perms as $name => $label) {
                $data = [
                    'label' => $label,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ];
                if ($hasCategory) {
                    $data['category'] = $categoryLabels[$category] ?? $category;
                    $data['sort_order'] = $sortOrder++;
                }
                DB::table('permissions')->updateOrInsert(
                    ['name' => $name],
                    $data
                );
            }
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
        $subset = function (array $names) use ($permIds) {
            return $permIds->only($names)->values()->all();
        };

        $rolePerms = [
            'superadmin' => $giveAll,
            'admin' => $permIds->except([
                // Admin não gerencia permissões nem roles (apenas superadmin)
            ])->values()->all(),
            'gestor' => $subset([
                // Dashboard
                'dashboard.view',
                // Cursos
                'courses.view', 'courses.create', 'courses.edit', 'courses.delete', 'courses.publish', 'courses.enrollments',
                // Mentorias
                'mentorships.view', 'mentorships.create', 'mentorships.edit', 'mentorships.delete', 'mentorships.schedule', 'mentorships.sessions',
                // Eventos
                'events.view', 'events.create', 'events.edit', 'events.delete', 'events.publish', 'events.ticket.manage', 'events.exhibitors.manage', 'events.checkin',
                // Planos (apenas visualizar)
                'plans.view', 'plans.edit', 'plans.feature.toggle', 'plans.discount.manage',
                // Cupons
                'coupons.view', 'coupons.create', 'coupons.edit',
                // Certificados
                'certificates.view', 'certificates.generate', 'certificates.templates',
                // Pontuação
                'ranking.view', 'ranking.edit',
                // Comunidade
                'community.view', 'community.moderate', 'community.pin',
                // E-mails
                'mailtemplates.view', 'mailtemplates.create', 'mailtemplates.edit', 'mail.sendtest',
                // Depoimentos
                'testimonials.view', 'testimonials.moderate',
                // FAQ
                'faq.view', 'faq.create', 'faq.edit',
                // Uploads
                'uploads.view', 'uploads.manage',
            ]),
            'suporte' => $subset([
                // Dashboard
                'dashboard.view',
                // Usuários (visualizar/editar)
                'users.view', 'users.edit',
                // Cursos/Eventos/Mentorias (apenas visualizar)
                'courses.view', 'events.view', 'mentorships.view',
                // Vendas/Faturas (visualizar)
                'orders.view', 'invoices.view',
                // E-mails
                'mailtemplates.view', 'mail.sendtest',
                // Uploads
                'uploads.view', 'uploads.manage',
                // Depoimentos
                'testimonials.view',
                // Comunidade (moderação)
                'community.view', 'community.moderate',
            ]),
            'membro' => $subset([
                // Apenas visualização básica
                'dashboard.view',
                'courses.view',
                'events.view',
                'mentorships.view',
                'ranking.view',
                'community.view',
            ]),
        ];

        foreach ($rolePerms as $roleName => $permIdList) {
            $roleId = $roleIds[$roleName] ?? null;
            if (!$roleId) {
                continue;
            }

            foreach ($permIdList as $permissionId) {
                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['role_id' => $roleId, 'permission_id' => $permissionId]
                );
            }
        }

        // Atribui superadmin ao primeiro usuário apenas se ainda não existir nenhum vínculo desse papel.
        $userId = DB::table('users')->min('id');
        $superadminRoleId = $roleIds['superadmin'] ?? null;
        $hasSuperadminAssignment = $superadminRoleId
            ? DB::table('role_user')->where('role_id', $superadminRoleId)->exists()
            : false;

        if ($userId && $superadminRoleId && !$hasSuperadminAssignment) {
            DB::table('role_user')->updateOrInsert(
                ['role_id' => $superadminRoleId, 'user_id' => $userId],
                ['role_id' => $superadminRoleId, 'user_id' => $userId]
            );
        }
    }
}
