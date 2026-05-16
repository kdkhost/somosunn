<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato Email: contato@kdkhost.com.br
 *
 * ============================================================
 */

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasEagerLoading
 *
 * Helpers para aplicar eager loading consistente em queries
 * com relações conhecidas, evitando o problema N+1.
 *
 * Uso (em models que tenham relações):
 *   class User extends Model {
 *       use HasEagerLoading;
 *   }
 *
 *   User::query()->withCommonRelations()->paginate(20);
 *
 * Spec: advanced-security-performance, Requirements 12.2, 12.3, 12.5
 */
trait HasEagerLoading
{
    /**
     * Aplica eager loading para as relações comuns deste model.
     * Cada model que usa o trait pode definir $commonEagerRelations.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithCommonRelations(Builder $query): Builder
    {
        $relations = property_exists($this, 'commonEagerRelations')
            ? $this->commonEagerRelations
            : [];

        if (!empty($relations) && is_array($relations)) {
            $query->with($relations);
        }

        return $query;
    }

    /**
     * Aplica eager loading com count para relações específicas.
     *
     * @param Builder $query
     * @param array $relations
     * @return Builder
     */
    public function scopeWithCounts(Builder $query, array $relations): Builder
    {
        if (!empty($relations)) {
            $query->withCount($relations);
        }

        return $query;
    }
}
