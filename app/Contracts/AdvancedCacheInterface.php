<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 */

namespace App\Contracts;

/**
 * Contrato do AdvancedCacheManager.
 *
 * Define o cache de alta granularidade usado pela plataforma com TTLs
 * configuráveis via tabela `settings` e padrão de chaves `unn:*`.
 *
 * Cada método get*() recebe um $loader (callable) que produz o valor em
 * caso de cache-miss. Subsequentes chamadas dentro do TTL retornam o valor
 * cacheado sem invocar o loader (padrão Cache::remember).
 */
interface AdvancedCacheInterface
{
    /**
     * Retorna o menu de navegação cacheado para um papel (role).
     *
     * Cache key: unn:nav:{role}
     *
     * @param string   $role   Papel do usuário (ex.: superadmin, admin, user).
     * @param callable $loader Função que retorna a navegação se cache miss.
     * @return array
     */
    public function getNavigation(string $role, callable $loader): array;

    /**
     * Retorna as configurações da aplicação cacheadas.
     *
     * Cache key: unn:settings
     *
     * @param callable $loader Função que retorna o array de settings.
     * @return array
     */
    public function getSettings(callable $loader): array;

    /**
     * Retorna as permissões cacheadas de um usuário.
     *
     * Cache key: unn:perms:{userId}
     *
     * @param int      $userId
     * @param callable $loader Função que retorna o array de permissões.
     * @return array
     */
    public function getUserPermissions(int $userId, callable $loader): array;

    /**
     * Retorna métricas de dashboard cacheadas.
     *
     * Cache key: unn:dash:{metric}
     *
     * @param string   $metric Identificador da métrica.
     * @param callable $loader Função que retorna o valor da métrica.
     * @return mixed
     */
    public function getDashboardStats(string $metric, callable $loader): mixed;

    /**
     * Retorna o resultado cacheado de uma query pesada.
     *
     * Cache key: unn:query:{md5(key)}
     *
     * @param string   $key      Identificador da query (será hasheado em md5).
     * @param callable $callback Função que executa a query se cache miss.
     * @param int|null $ttl      TTL em minutos (padrão configurado em settings).
     * @return mixed
     */
    public function getHeavyQuery(string $key, callable $callback, ?int $ttl = null): mixed;

    /**
     * Invalida cache por tipo e identificador.
     *
     * Tipos suportados: navigation, settings, permissions, dashboard,
     * heavy_query, all.
     *
     * @param string      $type
     * @param string|null $identifier Quando nulo, invalida todas as entradas do tipo.
     * @return void
     */
    public function invalidate(string $type, ?string $identifier = null): void;

    /**
     * Pré-popula as entradas de cache mais comuns.
     *
     * @return void
     */
    public function warmUp(): void;
}
