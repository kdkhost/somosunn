<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plans') || !Schema::hasColumn('plans', 'permissions')) {
            return;
        }

        // Slug é usado para updateOrCreate seguro. Se não existir (instalação muito antiga), aborta.
        if (!Schema::hasColumn('plans', 'slug')) {
            return;
        }

        $now = now();

        // Tipos solicitados:
        // 1) Cliente comum (comprador via marketplace, sem associação)
        // 2) Membro associado (pontos/ranking + compra com desconto futuramente)
        // 3) Membro associado criador (instrutor/mentor/palestrante) + vendas no marketplace
        // 4) Membro associado com acesso liberado (acesso amplo às áreas)
        $definitions = [
            'cliente' => [
                'name' => 'Cliente (Comprador)',
                'description' => 'Conta de comprador do marketplace (sem associação).',
                'price' => 0,
                'period' => 'vitalício',
                'billing_cycle' => 'monthly',
                'prorata' => false,
                'is_active' => false, // não aparece no /premium por padrão
                'permissions' => [
                    'marketplace.buy',
                ],
            ],
            'associado' => [
                'name' => 'Associado',
                'description' => 'Membro associado com acesso à comunidade e compras no marketplace.',
                'price' => 49.90,
                'period' => 'mensal',
                'billing_cycle' => 'monthly',
                'prorata' => false,
                'is_active' => true,
                'permissions' => [
                    'community',
                    'chat',
                    'connections',
                    'rankings',
                    'courses',
                    'events',
                    'mentorships',
                    'marketplace.buy',
                ],
            ],
            'associado-criador' => [
                'name' => 'Associado Criador (Instrutor/Mentor)',
                'description' => 'Membro associado com permissão para criar e vender produtos digitais no marketplace.',
                'price' => 99.90,
                'period' => 'mensal',
                'billing_cycle' => 'monthly',
                'prorata' => false,
                'is_active' => true,
                'permissions' => [
                    'community',
                    'chat',
                    'connections',
                    'rankings',
                    'marketplace.buy',
                    'marketplace.sell',
                    'marketplace.sales',
                    'courses',
                    'courses.create',
                    'courses.edit',
                    'courses.delete',
                    'courses.certificates',
                    'courses.downloads',
                    'events',
                    'events.create',
                    'events.edit',
                    'events.delete',
                    'mentorships',
                    'mentorships.create',
                    'mentorships.edit',
                    'mentorships.delete',
                ],
            ],
            'associado-acesso-total' => [
                'name' => 'Associado Acesso Total',
                'description' => 'Membro associado com acesso liberado a cursos, mentorias e eventos.',
                'price' => 149.90,
                'period' => 'mensal',
                'billing_cycle' => 'monthly',
                'prorata' => false,
                'is_active' => true,
                'permissions' => [
                    'community',
                    'chat',
                    'connections.unlimited',
                    'rankings',
                    'support.priority',
                    'early.access',
                    'marketplace.buy',
                    'courses',
                    'courses.certificates',
                    'courses.downloads',
                    'events',
                    'events.recordings',
                    'events.vip',
                    'mentorships',
                    'mentorships.group',
                    'mentorships.individual',
                ],
            ],
        ];

        foreach ($definitions as $slug => $plan) {
            $existing = DB::table('plans')->where('slug', $slug)->first();

            if (!$existing) {
                $insert = [
                    'name' => $plan['name'],
                    'slug' => $slug,
                    'price' => $plan['price'],
                    'period' => $plan['period'],
                    'permissions' => json_encode(array_values(array_unique($plan['permissions']))),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (Schema::hasColumn('plans', 'description')) {
                    $insert['description'] = $plan['description'];
                }
                if (Schema::hasColumn('plans', 'billing_cycle')) {
                    $insert['billing_cycle'] = $plan['billing_cycle'];
                }
                if (Schema::hasColumn('plans', 'prorata')) {
                    $insert['prorata'] = (bool) $plan['prorata'];
                }
                if (Schema::hasColumn('plans', 'is_active')) {
                    $insert['is_active'] = (bool) $plan['is_active'];
                }
                if (Schema::hasColumn('plans', 'is_featured')) {
                    $insert['is_featured'] = false;
                }
                if (Schema::hasColumn('plans', 'highlight')) {
                    $insert['highlight'] = false;
                }

                DB::table('plans')->insert($insert);
                continue;
            }

            $mergedPermissions = $this->mergePermissions($existing->permissions, $plan['permissions']);

            $update = [
                'permissions' => json_encode($mergedPermissions),
                'updated_at' => $now,
            ];

            // Não sobrescreve preço/período por segurança; admin pode ajustar no painel.
            if (Schema::hasColumn('plans', 'description')) {
                $desc = trim((string) ($existing->description ?? ''));
                if ($desc === '') {
                    $update['description'] = $plan['description'];
                }
            }

            DB::table('plans')->where('id', (int) $existing->id)->update($update);
        }

        // Correção dos planos existentes (Starter/Pro/Elite) para incluir marketplace + ranking.
        foreach (['starter', 'pro', 'elite'] as $slug) {
            $existing = DB::table('plans')->where('slug', $slug)->first();
            if (!$existing) {
                continue;
            }

            $mergedPermissions = $this->mergePermissions($existing->permissions, [
                'marketplace.buy',
                'rankings',
            ]);

            DB::table('plans')->where('id', (int) $existing->id)->update([
                'permissions' => json_encode($mergedPermissions),
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // no-op (safety)
    }

    private function mergePermissions($raw, array $add): array
    {
        $current = $this->normalizePermissions($raw);
        $add = array_values(array_filter($add, static fn ($v) => is_string($v) && trim($v) !== ''));
        return array_values(array_unique(array_merge($current, $add)));
    }

    private function normalizePermissions($raw): array
    {
        $values = [];

        if (is_array($raw)) {
            $values = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $values = $decoded;
            }
        }

        $out = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ($value === '') {
                continue;
            }

            $out[] = $value;
        }

        return array_values(array_unique($out));
    }
};

