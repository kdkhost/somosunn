<?php

namespace App\Services\Waf;

use App\Models\Waf\WafRule;
use App\Models\Waf\WafRuleVersion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repositorio de WafRules com cache versionado e versionamento append-only.
 *
 * Degradacao graciosa: quando a tabela `waf_rules` nao existe (migration
 * ainda nao aplicada), retorna colecao vazia sem lancar exceoes.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 10.7, 15.2, 15.7, 22.1
 */
final class WafRuleRepository
{
    private const CACHE_VERSION_KEY = 'waf:rules:version';
    private const CACHE_TTL_SECONDS = 3600;

    /**
     * Retorna todas as regras ativas (opcionalmente filtrando por escopo).
     */
    public function allActive(?string $scope = null): Collection
    {
        $version = $this->currentVersion();
        $cacheKey = "waf:rules:active:v{$version}";

        try {
            $rules = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () {
                if (! $this->tableExists()) {
                    return collect();
                }

                return WafRule::query()->active()->get();
            });
        } catch (\Throwable $e) {
            $rules = collect();
        }

        if ($scope === null || $scope === 'default' || $rules->isEmpty()) {
            return $rules;
        }

        return $rules->filter(function (WafRule $rule) use ($scope) {
            $scopeCfg = (array) $rule->scope;
            $scopes   = $scopeCfg['scopes'] ?? null;

            // Se a regra nao define escopo, aplica em todos
            if (! is_array($scopes) || empty($scopes) || in_array('*', $scopes, true)) {
                return true;
            }

            return in_array($scope, $scopes, true);
        })->values();
    }

    /**
     * Encontra uma regra pelo id.
     */
    public function find(int $id): ?WafRule
    {
        try {
            if (! $this->tableExists()) {
                return null;
            }
            return WafRule::query()->find($id);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Persiste uma regra criando registro de versao (append-only).
     */
    public function save(WafRule $rule, ?int $actorId): WafRule
    {
        return DB::transaction(function () use ($rule, $actorId) {
            $isNew     = ! $rule->exists;
            $oldValues = $isNew ? null : $rule->getOriginal();

            $rule->save();

            $version = (int) (WafRuleVersion::query()
                ->where('rule_id', $rule->id)
                ->max('version') ?? 0) + 1;

            WafRuleVersion::query()->create([
                'rule_id'    => $rule->id,
                'version'    => $version,
                'snapshot'   => $oldValues,
                'actor_id'   => $actorId,
                'action'     => $isNew ? WafRuleVersion::ACTION_CREATED : WafRuleVersion::ACTION_UPDATED,
                'created_at' => now(),
            ]);

            $this->invalidateCache();

            return $rule->refresh();
        });
    }

    /**
     * Ativa/desativa rapidamente e registra em versao.
     */
    public function toggle(int $id, ?int $actorId): ?WafRule
    {
        return DB::transaction(function () use ($id, $actorId) {
            $rule = WafRule::query()->lockForUpdate()->find($id);
            if (! $rule) {
                return null;
            }

            $old = $rule->getOriginal();
            $rule->is_active = ! $rule->is_active;
            $rule->updated_by = $actorId;
            $rule->save();

            $version = (int) (WafRuleVersion::query()
                ->where('rule_id', $rule->id)
                ->max('version') ?? 0) + 1;

            WafRuleVersion::query()->create([
                'rule_id'    => $rule->id,
                'version'    => $version,
                'snapshot'   => $old,
                'actor_id'   => $actorId,
                'action'     => WafRuleVersion::ACTION_TOGGLED,
                'created_at' => now(),
            ]);

            $this->invalidateCache();

            return $rule->refresh();
        });
    }

    /**
     * Remove uma regra e preserva o snapshot em waf_rule_versions.
     */
    public function delete(int $id, ?int $actorId): bool
    {
        return DB::transaction(function () use ($id, $actorId) {
            $rule = WafRule::query()->lockForUpdate()->find($id);
            if (! $rule) {
                return false;
            }

            $old = $rule->getOriginal();

            $version = (int) (WafRuleVersion::query()
                ->where('rule_id', $rule->id)
                ->max('version') ?? 0) + 1;

            WafRuleVersion::query()->create([
                'rule_id'    => $rule->id,
                'version'    => $version,
                'snapshot'   => $old,
                'actor_id'   => $actorId,
                'action'     => WafRuleVersion::ACTION_DELETED,
                'created_at' => now(),
            ]);

            $rule->delete();

            $this->invalidateCache();

            return true;
        });
    }

    /**
     * Invalida o cache de regras incrementando a versao.
     */
    public function invalidateCache(): void
    {
        try {
            Cache::increment(self::CACHE_VERSION_KEY);
        } catch (\Throwable $e) {
            // Em ambientes onde o driver nao suporta increment, fallback
            try {
                $v = (int) Cache::get(self::CACHE_VERSION_KEY, 0);
                Cache::put(self::CACHE_VERSION_KEY, $v + 1, self::CACHE_TTL_SECONDS);
            } catch (\Throwable $ee) {
                // Sem cache? segue sem fazer nada
            }
        }
    }

    private function currentVersion(): int
    {
        try {
            return (int) Cache::rememberForever(self::CACHE_VERSION_KEY, fn () => 1);
        } catch (\Throwable $e) {
            return 1;
        }
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('waf_rules');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
