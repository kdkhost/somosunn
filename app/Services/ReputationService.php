<?php

namespace App\Services;

use App\Models\MemberReputationHistory;
use App\Models\MemberReputationScore;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Servico responsavel pelo calculo e gerenciamento do score de reputacao dos membros.
 *
 * O score final (0-100) e composto por 4 dimensoes ponderadas:
 * - Taxa de Entrega (40%) — desempenho como vendedor no marketplace
 * - Relacionamento (25%) — bloqueios, denuncias e punicoes
 * - Interacao (20%) — posts, comentarios, eventos, reacoes
 * - Engajamento (15%) — frequencia de login e aulas concluidas
 */
class ReputationService
{
    // Pesos das dimensoes
    const WEIGHT_DELIVERY = 0.40;
    const WEIGHT_RELATIONSHIP = 0.25;
    const WEIGHT_INTERACTION = 0.20;
    const WEIGHT_ENGAGEMENT = 0.15;

    // TTL do cache em segundos (24 horas)
    const CACHE_TTL = 86400;

    /**
     * Retorna o score cacheado ou do banco. Nunca dispara recalculo durante o request.
     *
     * @return array{score: int, badge: array}
     */
    public function getScore(int $userId): array
    {
        $cacheKey = "reputation:score:{$userId}";

        try {
            // Tenta cache primeiro
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            // Fallback para banco
            $record = MemberReputationScore::where('user_id', $userId)->first();
            if ($record) {
                $data = [
                    'score' => (int) $record->overall_score,
                    'badge' => self::getBadgeData((int) $record->overall_score),
                    'dimensions' => [
                        'delivery_rate' => (float) $record->delivery_rate,
                        'relationship_score' => (float) $record->relationship_score,
                        'interaction_score' => (float) $record->interaction_score,
                        'engagement_score' => (float) $record->engagement_score,
                    ],
                    'has_seller_store' => (bool) $record->has_seller_store,
                    'calculated_at' => $record->calculated_at?->toIso8601String(),
                ];

                // Repovoar cache
                Cache::put($cacheKey, $data, self::CACHE_TTL);

                return $data;
            }
        } catch (\Throwable $e) {
            Log::warning('ReputationService::getScore falhou para user_id=' . $userId . ': ' . $e->getMessage());
        }

        // Default para membros sem registro
        $defaultScore = 50;
        return [
            'score' => $defaultScore,
            'badge' => self::getBadgeData($defaultScore),
            'dimensions' => [
                'delivery_rate' => 70.00,
                'relationship_score' => 100.00,
                'interaction_score' => 0.00,
                'engagement_score' => 0.00,
            ],
            'has_seller_store' => false,
            'calculated_at' => null,
        ];
    }

    /**
     * Recalcula e persiste o score completo de um membro.
     *
     * @return array{score: int, badge: array, dimensions: array}
     */
    public function recalculateFor(User $user): array
    {
        try {
            $hasSeller = $this->userHasSellerStore($user);

            $deliveryRate = $this->calculateDeliveryRate($user);
            $relationshipScore = $this->calculateRelationshipScore($user);
            $interactionScore = $this->calculateInteractionScore($user);
            $engagementScore = $this->calculateEngagementScore($user);

            $finalScore = $this->computeFinalScore(
                $deliveryRate,
                $relationshipScore,
                $interactionScore,
                $engagementScore,
                $hasSeller
            );

            // Aplicar decay por inatividade
            $finalScore = (int) $this->applyDecay($user, (float) $finalScore);

            // Determinar ultimo login
            $lastLoginAt = $this->getLastLoginDate($user);

            // Persistir no banco
            $record = MemberReputationScore::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'overall_score' => $finalScore,
                    'delivery_rate' => round($deliveryRate, 2),
                    'relationship_score' => round($relationshipScore, 2),
                    'interaction_score' => round($interactionScore, 2),
                    'engagement_score' => round($engagementScore, 2),
                    'has_seller_store' => $hasSeller,
                    'last_login_at' => $lastLoginAt,
                    'calculated_at' => now(),
                ]
            );

            // Registrar historico (1 registro por dia)
            MemberReputationHistory::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'recorded_at' => now()->toDateString(),
                ],
                [
                    'overall_score' => $finalScore,
                ]
            );

            // Atualizar cache
            $data = [
                'score' => $finalScore,
                'badge' => self::getBadgeData($finalScore),
                'dimensions' => [
                    'delivery_rate' => round($deliveryRate, 2),
                    'relationship_score' => round($relationshipScore, 2),
                    'interaction_score' => round($interactionScore, 2),
                    'engagement_score' => round($engagementScore, 2),
                ],
                'has_seller_store' => $hasSeller,
                'calculated_at' => now()->toIso8601String(),
            ];

            $cacheKey = "reputation:score:{$user->id}";
            Cache::put($cacheKey, $data, self::CACHE_TTL);

            return $data;
        } catch (\Throwable $e) {
            Log::error('ReputationService::recalculateFor falhou para user_id=' . $user->id . ': ' . $e->getMessage());

            // Retorna score default em caso de erro critico
            $defaultScore = 50;
            return [
                'score' => $defaultScore,
                'badge' => self::getBadgeData($defaultScore),
                'dimensions' => [
                    'delivery_rate' => 70.00,
                    'relationship_score' => 100.00,
                    'interaction_score' => 0.00,
                    'engagement_score' => 0.00,
                ],
                'has_seller_store' => false,
                'calculated_at' => null,
            ];
        }
    }

    /**
     * Calcula a Taxa de Entrega (0-100).
     *
     * - Pedidos com shipment nos ultimos 12 meses
     * - Percentual de entregas no prazo (delivered_at - shipped_at <= delivery_days)
     * - Default 70 se < 3 pedidos completos
     * - -20 se punishment ativo
     * - 0 se nao tem loja (peso sera redistribuido)
     */
    public function calculateDeliveryRate(User $user): float
    {
        try {
            // Se nao tem loja, retorna 0 (peso sera redistribuido no calculo final)
            if (!$this->userHasSellerStore($user)) {
                return 0.0;
            }

            $twelveMonthsAgo = now()->subMonths(12);

            // Buscar pedidos como vendedor com shipment nos ultimos 12 meses
            $shipments = DB::table('orders')
                ->join('order_shipments', 'orders.id', '=', 'order_shipments.order_id')
                ->where('orders.seller_id', $user->id)
                ->where('orders.status', 'paid')
                ->where('orders.paid_at', '>=', $twelveMonthsAgo)
                ->whereNotNull('order_shipments.shipped_at')
                ->whereNotNull('order_shipments.delivered_at')
                ->select([
                    'order_shipments.shipped_at',
                    'order_shipments.delivered_at',
                    'order_shipments.delivery_days',
                ])
                ->get();

            // Se menos de 3 pedidos completos, usar default
            if ($shipments->count() < 3) {
                $rate = 70.0;
            } else {
                // Calcular percentual de entregas no prazo
                $onTime = 0;
                $total = $shipments->count();

                foreach ($shipments as $shipment) {
                    $shippedAt = \Carbon\Carbon::parse($shipment->shipped_at);
                    $deliveredAt = \Carbon\Carbon::parse($shipment->delivered_at);
                    $deliveryDays = (int) ($shipment->delivery_days ?: 7); // fallback 7 dias

                    $actualDays = $shippedAt->diffInDays($deliveredAt);

                    if ($actualDays <= $deliveryDays) {
                        $onTime++;
                    }
                }

                $rate = ($onTime / $total) * 100;
            }

            // Aplicar penalidade se punishment ativo
            if ($this->hasPunishmentActive($user)) {
                $rate -= 20;
            }

            return max(0.0, min(100.0, $rate));
        } catch (\Throwable $e) {
            Log::warning('ReputationService::calculateDeliveryRate erro: ' . $e->getMessage());
            return 70.0; // default seguro
        }
    }

    /**
     * Calcula o Score de Relacionamento (0-100).
     *
     * - Inicia em 100
     * - -5 por block recebido nos ultimos 6 meses (max 10 blocks contados)
     * - -10 por report confirmado nos ultimos 6 meses
     * - -30 se punishment ativo
     * - Minimo 0
     */
    public function calculateRelationshipScore(User $user): float
    {
        try {
            $score = 100.0;
            $sixMonthsAgo = now()->subMonths(6);

            // Contar blocks recebidos (onde o user foi bloqueado por outros)
            $blocksReceived = DB::table('connections')
                ->where('requested_id', $user->id)
                ->where('status', 'blocked')
                ->where('updated_at', '>=', $sixMonthsAgo)
                ->count();

            // Tambem contar blocks onde o user e o requester e foi bloqueado
            $blocksReceived += DB::table('connections')
                ->where('requester_id', $user->id)
                ->where('status', 'blocked')
                ->where('updated_at', '>=', $sixMonthsAgo)
                // Neste caso, quem bloqueou foi o requested_id, entao o user recebeu o block
                // Na verdade, no modelo Connection, quem bloqueia e quem muda o status
                // Vamos considerar apenas requested_id como quem recebeu o block
                ->count();

            // Limitar a 10 blocks contados (apenas os recebidos como requested)
            $blocksReceived = DB::table('connections')
                ->where('requested_id', $user->id)
                ->where('status', 'blocked')
                ->where('updated_at', '>=', $sixMonthsAgo)
                ->count();

            $blocksCount = min($blocksReceived, 10);
            $score -= ($blocksCount * 5);

            // Contar reports confirmados nos ultimos 6 meses
            if (Schema::hasTable('post_reports')) {
                $confirmedReports = DB::table('post_reports')
                    ->join('posts', 'post_reports.post_id', '=', 'posts.id')
                    ->where('posts.user_id', $user->id)
                    ->where('post_reports.status', 'confirmed')
                    ->where('post_reports.created_at', '>=', $sixMonthsAgo)
                    ->count();

                $score -= ($confirmedReports * 10);
            }

            // Penalidade por punishment ativo
            if ($this->hasPunishmentActive($user)) {
                $score -= 30;
            }

            return max(0.0, $score);
        } catch (\Throwable $e) {
            Log::warning('ReputationService::calculateRelationshipScore erro: ' . $e->getMessage());
            return 100.0; // default seguro
        }
    }

    /**
     * Calcula o Score de Interacao (0-100).
     *
     * Ultimos 90 dias:
     * - Posts: 2 pts cada (max 20)
     * - Comments: 1 pt cada (max 15)
     * - Events checked_in: 5 pts cada (max 25)
     * - Reactions: 0.5 pt cada (max 10)
     * - Normalizar: raw / 70 * 100, max 100
     */
    public function calculateInteractionScore(User $user): float
    {
        try {
            $ninetyDaysAgo = now()->subDays(90);

            // Posts nos ultimos 90 dias
            $postsCount = DB::table('posts')
                ->where('user_id', $user->id)
                ->where('created_at', '>=', $ninetyDaysAgo)
                ->count();

            // Comentarios nos ultimos 90 dias
            $commentsCount = DB::table('post_comments')
                ->where('user_id', $user->id)
                ->where('created_at', '>=', $ninetyDaysAgo)
                ->count();

            // Eventos com check-in nos ultimos 90 dias
            $eventsCheckedIn = DB::table('event_registrations')
                ->where('user_id', $user->id)
                ->whereNotNull('check_in_at')
                ->where('check_in_at', '>=', $ninetyDaysAgo)
                ->count();

            // Reacoes nos ultimos 90 dias
            $reactionsCount = DB::table('post_reactions')
                ->where('user_id', $user->id)
                ->where('created_at', '>=', $ninetyDaysAgo)
                ->count();

            // Calcular pontos com caps
            $postPoints = min($postsCount * 2, 20);
            $commentPoints = min($commentsCount * 1, 15);
            $eventPoints = min($eventsCheckedIn * 5, 25);
            $reactionPoints = min($reactionsCount * 0.5, 10);

            $rawPoints = $postPoints + $commentPoints + $eventPoints + $reactionPoints;

            // Normalizar: raw / 70 * 100, max 100
            $normalized = ($rawPoints / 70) * 100;

            return min(100.0, $normalized);
        } catch (\Throwable $e) {
            Log::warning('ReputationService::calculateInteractionScore erro: ' . $e->getMessage());
            return 0.0; // default seguro
        }
    }

    /**
     * Calcula o Score de Engajamento (0-100).
     *
     * Ultimos 90 dias:
     * - Login: se >= 20 dias distintos -> 50 pts, senao proporcional (days/20*50)
     * - Lessons: 5 pts por licao completa (max 50)
     * - Max 100
     */
    public function calculateEngagementScore(User $user): float
    {
        try {
            $ninetyDaysAgo = now()->subDays(90);

            // Contar dias distintos de login usando points_log (action_key = 'daily_login')
            $loginDays = 0;

            if (Schema::hasTable('points_logs')) {
                $loginDays = DB::table('points_logs')
                    ->where('user_id', $user->id)
                    ->where('action_key', 'daily_login')
                    ->where('created_at', '>=', $ninetyDaysAgo)
                    ->selectRaw('COUNT(DISTINCT DATE(created_at)) as days')
                    ->value('days') ?? 0;
            } elseif (Schema::hasTable('activity_logs')) {
                // Fallback: usar activity_logs com action 'login'
                $loginDays = DB::table('activity_logs')
                    ->where('user_id', $user->id)
                    ->where('action', 'login')
                    ->where('created_at', '>=', $ninetyDaysAgo)
                    ->selectRaw('COUNT(DISTINCT DATE(created_at)) as days')
                    ->value('days') ?? 0;
            }

            // Pontuacao de login
            if ($loginDays >= 20) {
                $loginPoints = 50.0;
            } else {
                $loginPoints = ($loginDays / 20) * 50;
            }

            // Licoes completas nos ultimos 90 dias
            $lessonsCompleted = 0;

            if (Schema::hasTable('lesson_progress')) {
                $lessonsCompleted = DB::table('lesson_progress')
                    ->where('user_id', $user->id)
                    ->whereNotNull('completed_at')
                    ->where('completed_at', '>=', $ninetyDaysAgo)
                    ->count();
            }

            $lessonPoints = min($lessonsCompleted * 5, 50);

            $total = $loginPoints + $lessonPoints;

            return min(100.0, $total);
        } catch (\Throwable $e) {
            Log::warning('ReputationService::calculateEngagementScore erro: ' . $e->getMessage());
            return 0.0; // default seguro
        }
    }

    /**
     * Aplica decay por inatividade ao score.
     *
     * Se > 30 dias sem login: decay = floor((dias - 30) / 7) * 2
     * Score minimo apos decay: 20
     */
    public function applyDecay(User $user, float $score): float
    {
        try {
            $lastLogin = $this->getLastLoginDate($user);

            if (!$lastLogin) {
                // Sem dados de login, nao aplica decay
                return $score;
            }

            $daysInactive = (int) $lastLogin->diffInDays(now());

            if ($daysInactive <= 30) {
                return $score;
            }

            $decay = (int) floor(($daysInactive - 30) / 7) * 2;
            $result = $score - $decay;

            return max(20.0, $result);
        } catch (\Throwable $e) {
            Log::warning('ReputationService::applyDecay erro: ' . $e->getMessage());
            return $score;
        }
    }

    /**
     * Computa o score final ponderado e clamped [0, 100].
     *
     * Soma ponderada das 4 dimensoes com redistribuicao de peso se nao tem loja.
     */
    public function computeFinalScore(
        float $deliveryRate,
        float $relationshipScore,
        float $interactionScore,
        float $engagementScore,
        bool $hasSeller
    ): int {
        $weights = $this->getWeights($hasSeller);

        $weighted = ($deliveryRate * $weights['delivery'])
            + ($relationshipScore * $weights['relationship'])
            + ($interactionScore * $weights['interaction'])
            + ($engagementScore * $weights['engagement']);

        $final = (int) round($weighted);

        return max(0, min(100, $final));
    }

    /**
     * Retorna os pesos das dimensoes.
     * Se nao tem loja, delivery = 0 e redistribui 0.40/3 para as outras.
     */
    public function getWeights(bool $hasSellerStore): array
    {
        if ($hasSellerStore) {
            return [
                'delivery' => self::WEIGHT_DELIVERY,
                'relationship' => self::WEIGHT_RELATIONSHIP,
                'interaction' => self::WEIGHT_INTERACTION,
                'engagement' => self::WEIGHT_ENGAGEMENT,
            ];
        }

        // Redistribuir peso de delivery igualmente entre as outras 3 dimensoes
        $redistribution = self::WEIGHT_DELIVERY / 3;

        return [
            'delivery' => 0.0,
            'relationship' => self::WEIGHT_RELATIONSHIP + $redistribution,
            'interaction' => self::WEIGHT_INTERACTION + $redistribution,
            'engagement' => self::WEIGHT_ENGAGEMENT + $redistribution,
        ];
    }

    /**
     * Retorna dados do badge (icon, color, label) para um score.
     *
     * Faixas:
     * 90-100: Excelente, estrela dourada, #FFD700
     * 70-89: Confiavel, escudo azul, #1F5EDB
     * 50-69: Regular, circulo verde, #22C55E
     * 30-49: Atencao, triangulo laranja, #F59E0B
     * 0-29: Baixa Reputacao, exclamacao vermelha, #EF4444
     */
    public static function getBadgeData(int $score): array
    {
        if ($score >= 90) {
            return [
                'label' => 'Excelente',
                'icon' => 'star',
                'color' => '#FFD700',
            ];
        }

        if ($score >= 70) {
            return [
                'label' => 'Confiavel',
                'icon' => 'shield',
                'color' => '#1F5EDB',
            ];
        }

        if ($score >= 50) {
            return [
                'label' => 'Regular',
                'icon' => 'circle',
                'color' => '#22C55E',
            ];
        }

        if ($score >= 30) {
            return [
                'label' => 'Atencao',
                'icon' => 'triangle',
                'color' => '#F59E0B',
            ];
        }

        return [
            'label' => 'Baixa Reputacao',
            'icon' => 'exclamation',
            'color' => '#EF4444',
        ];
    }

    // =========================================================================
    // Metodos auxiliares privados
    // =========================================================================

    /**
     * Verifica se o usuario tem loja ativa no marketplace.
     */
    private function userHasSellerStore(User $user): bool
    {
        try {
            if (!Schema::hasTable('seller_stores')) {
                return false;
            }

            return DB::table('seller_stores')
                ->where('user_id', $user->id)
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->where('is_blocked', false)->orWhereNull('is_blocked');
                })
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Verifica se o usuario tem punishment ativo (bloqueado ou suspenso).
     */
    private function hasPunishmentActive(User $user): bool
    {
        try {
            if (!Schema::hasColumn('users', 'blocked_until')) {
                return false;
            }

            $blockedUntil = $user->blocked_until ?? null;

            if ($blockedUntil && \Carbon\Carbon::parse($blockedUntil)->isFuture()) {
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Obtem a data do ultimo login do usuario.
     * Usa points_log (daily_login) ou activity_logs como fallback.
     */
    private function getLastLoginDate(User $user): ?\Carbon\Carbon
    {
        try {
            // Tentar via points_logs (daily_login)
            if (Schema::hasTable('points_logs')) {
                $lastLogin = DB::table('points_logs')
                    ->where('user_id', $user->id)
                    ->where('action_key', 'daily_login')
                    ->orderByDesc('created_at')
                    ->value('created_at');

                if ($lastLogin) {
                    return \Carbon\Carbon::parse($lastLogin);
                }
            }

            // Fallback: activity_logs
            if (Schema::hasTable('activity_logs')) {
                $lastLogin = DB::table('activity_logs')
                    ->where('user_id', $user->id)
                    ->where('action', 'login')
                    ->orderByDesc('created_at')
                    ->value('created_at');

                if ($lastLogin) {
                    return \Carbon\Carbon::parse($lastLogin);
                }
            }

            // Fallback: last_activity_at no user
            if (Schema::hasColumn('users', 'last_activity_at') && $user->last_activity_at) {
                return \Carbon\Carbon::parse($user->last_activity_at);
            }

            // Fallback: updated_at do user como aproximacao
            return $user->updated_at ? \Carbon\Carbon::parse($user->updated_at) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
