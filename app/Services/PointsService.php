<?php

namespace App\Services;

use App\Models\PointsLog;
use App\Models\PointsRule;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Ranking;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PointsService
{
    /**
     * Concede pontos a um usuário com base em uma regra ativa.
     * Respeita regras não-repetíveis e limites diários definidos.
     *
     * @return bool true se os pontos foram concedidos, false caso bloqueado por guard ou regra inexistente.
     */
    public function award(User $user, string $actionKey, $meta = null): bool
    {
        $rule = PointsRule::where('key', $actionKey)->where('active', true)->first();
        if (!$rule) {
            return false;
        }

        if ($rule->repeatable && $this->usesFreePlan($user)) {
            return false;
        }

        // Guard: Ações não repetíveis só podem ser concedidas uma vez na vida do usuário
        if (!$rule->repeatable) {
            if (PointsLog::where('user_id', $user->id)->where('action_key', $actionKey)->exists()) {
                return false;
            }
        }

        // Guard: Limite diário por calendário
        if ($rule->max_daily) {
            $todayCount = PointsLog::where('user_id', $user->id)
                ->where('action_key', $actionKey)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($todayCount >= (int) $rule->max_daily) {
                return false;
            }
        }

        $points = (int) $rule->points;

        PointsLog::create([
            'user_id'    => $user->id,
            'action_key' => $actionKey,
            'points'     => $points,
            'meta'       => is_null($meta) ? null : json_encode($meta),
        ]);

        $user->increment('points', $points);

        // Sincroniza tabela de rankings públicos se existir
        if (class_exists(Ranking::class)) {
            try {
                Ranking::updateOrCreate(
                    ['user_id' => $user->id],
                    ['score' => (int) $user->fresh()->points]
                );
            } catch (\Throwable $e) {}
        }

        return true;
    }

    /**
     * Aplica uma dedução/penalidade de pontos ao usuário por inatividade ou quebra de regra.
     * Garante que os pontos nunca fiquem negativos (mínimo 0) e registra a dedução em PointsLog.
     *
     * @return bool true se pontos foram deduzidos, false caso o usuário já esteja zerado ou parâmetros inválidos.
     */
    public function penalize(User $user, string $actionKey, int $pointsToDeduct, $meta = null): bool
    {
        if ($pointsToDeduct <= 0) {
            return false;
        }

        $currentPoints = (int) $user->points;
        if ($currentPoints <= 0) {
            return false;
        }

        $actualDeduction = min($currentPoints, $pointsToDeduct);

        PointsLog::create([
            'user_id'    => $user->id,
            'action_key' => $actionKey,
            'points'     => -$actualDeduction,
            'meta'       => is_null($meta) ? null : json_encode($meta),
        ]);

        $user->decrement('points', $actualDeduction);

        if (class_exists(Ranking::class)) {
            try {
                Ranking::updateOrCreate(
                    ['user_id' => $user->id],
                    ['score' => max(0, $currentPoints - $actualDeduction)]
                );
            } catch (\Throwable $e) {}
        }

        return true;
    }

    /**
     * Revoga os pontos da entrada mais recente correspondente à chave.
     */
    public function revoke(User $user, string $actionKey, $meta = null): bool
    {
        $log = PointsLog::where('user_id', $user->id)->where('action_key', $actionKey)->latest()->first();
        if (!$log) {
            return false;
        }

        $user->decrement('points', $log->points);
        $log->delete();

        if (class_exists(Ranking::class)) {
            try {
                Ranking::updateOrCreate(
                    ['user_id' => $user->id],
                    ['score' => max(0, (int) $user->fresh()->points)]
                );
            } catch (\Throwable $e) {}
        }

        return true;
    }

    /**
     * Processa o motor recorrente de decaimento por inatividade (pontuação negativa)
     * e bonificações por consistência ativa (pontuação positiva) para todos os membros.
     *
     * @return array Resumo dos processamentos executados.
     */
    public function processRecurrenceDecay(): array
    {
        $stats = [
            'total_users_checked' => 0,
            'penalties_login_7d'  => 0,
            'penalties_login_15d' => 0,
            'penalties_login_30d' => 0,
            'penalties_social'    => 0,
            'bonuses_active'      => 0,
            'rankings_synced'     => 0,
        ];

        try {
            $users = User::whereNotIn('role', ['admin', 'superadmin'])->get();
            $stats['total_users_checked'] = $users->count();

            foreach ($users as $user) {
                // 1. Data do último login conhecido
                $lastLoginDate = PointsLog::where('user_id', $user->id)
                    ->where('action_key', 'daily_login')
                    ->latest('created_at')
                    ->value('created_at');

                if (!$lastLoginDate) {
                    $lastLoginDate = $user->updated_at ?: $user->created_at;
                }

                $daysSinceLogin = $lastLoginDate ? now()->diffInDays($lastLoginDate) : 0;

                // Penalidades por inatividade de login (apenas se tiver pontos)
                if ((int) $user->points > 0) {
                    if ($daysSinceLogin >= 30) {
                        // Dedução severa por 30+ dias sem login (-50 pts por semana)
                        $alreadyPenalized = PointsLog::where('user_id', $user->id)
                            ->where('action_key', 'penalty_inactivity_30d')
                            ->where('created_at', '>=', now()->subDays(7))
                            ->exists();

                        if (!$alreadyPenalized) {
                            if ($this->penalize($user, 'penalty_inactivity_30d', 50, ['days_inactive' => $daysSinceLogin])) {
                                $stats['penalties_login_30d']++;
                            }
                        }
                    } elseif ($daysSinceLogin >= 15) {
                        // Dedução moderada por 15+ dias sem login (-25 pts por semana)
                        $alreadyPenalized = PointsLog::where('user_id', $user->id)
                            ->where('action_key', 'penalty_inactivity_15d')
                            ->where('created_at', '>=', now()->subDays(7))
                            ->exists();

                        if (!$alreadyPenalized) {
                            if ($this->penalize($user, 'penalty_inactivity_15d', 25, ['days_inactive' => $daysSinceLogin])) {
                                $stats['penalties_login_15d']++;
                            }
                        }
                    } elseif ($daysSinceLogin >= 7) {
                        // Dedução leve por 7+ dias sem login (-10 pts por semana)
                        $alreadyPenalized = PointsLog::where('user_id', $user->id)
                            ->where('action_key', 'penalty_inactivity_7d')
                            ->where('created_at', '>=', now()->subDays(7))
                            ->exists();

                        if (!$alreadyPenalized) {
                            if ($this->penalize($user, 'penalty_inactivity_7d', 10, ['days_inactive' => $daysSinceLogin])) {
                                $stats['penalties_login_7d']++;
                            }
                        }
                    }

                    // Penalidade por inatividade social (14+ dias sem postar ou comentar)
                    $accountAgeDays = $user->created_at ? now()->diffInDays($user->created_at) : 0;
                    if ($accountAgeDays >= 14) {
                        $hasRecentPost = class_exists(Post::class) && Post::where('user_id', $user->id)
                            ->where('created_at', '>=', now()->subDays(14))
                            ->exists();

                        $hasRecentComment = class_exists(PostComment::class) && PostComment::where('user_id', $user->id)
                            ->where('created_at', '>=', now()->subDays(14))
                            ->exists();

                        if (!$hasRecentPost && !$hasRecentComment) {
                            $alreadyPenalizedSocial = PointsLog::where('user_id', $user->id)
                                ->where('action_key', 'penalty_no_social_activity')
                                ->where('created_at', '>=', now()->subDays(14))
                                ->exists();

                            if (!$alreadyPenalizedSocial) {
                                if ($this->penalize($user, 'penalty_no_social_activity', 15, ['reason' => '14 dias sem criar posts ou comentários'])) {
                                    $stats['penalties_social']++;
                                }
                            }
                        }
                    }
                }

                // Bonificação recorrente de consistência semanal (membro ativo)
                if ($daysSinceLogin <= 2) {
                    $hasWeeklyActiveBonus = PointsLog::where('user_id', $user->id)
                        ->where('action_key', 'streak_weekly_active')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->exists();

                    if (!$hasWeeklyActiveBonus) {
                        $loginCount7d = PointsLog::where('user_id', $user->id)
                            ->where('action_key', 'daily_login')
                            ->where('created_at', '>=', now()->subDays(7))
                            ->count();

                        if ($loginCount7d >= 3) {
                            // Concede bônus semanal de consistência ativa (+20 pts)
                            PointsLog::create([
                                'user_id'    => $user->id,
                                'action_key' => 'streak_weekly_active',
                                'points'     => 20,
                                'meta'       => json_encode(['logins_this_week' => $loginCount7d]),
                            ]);
                            $user->increment('points', 20);
                            $stats['bonuses_active']++;
                        }
                    }
                }

                // Sincroniza sempre na tabela de ranking para manter ordem atualizada
                if (class_exists(Ranking::class)) {
                    Ranking::updateOrCreate(
                        ['user_id' => $user->id],
                        ['score' => (int) $user->fresh()->points]
                    );
                    $stats['rankings_synced']++;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao processar decaimento/recorrência de pontos: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Calcula o streak de logins consecutivos (dias seguidos).
     */
    public function calculateLoginStreak(User $user): int
    {
        $dates = PointsLog::where('user_id', $user->id)
            ->where('action_key', 'daily_login')
            ->selectRaw('DATE(created_at) as login_date')
            ->groupBy('login_date')
            ->orderByDesc('login_date')
            ->pluck('login_date')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->startOfDay())
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 1;

        for ($i = 1; $i < $dates->count(); $i++) {
            if ((int) $dates[$i - 1]->diffInDays($dates[$i]) === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    private function usesFreePlan(User $user): bool
    {
        if (!method_exists($user, 'activePlan')) {
            return false;
        }

        $plan = $user->activePlan();
        if (!$plan) {
            return false;
        }

        return (bool) $plan->is_free || (float) ($plan->price ?? 0) <= 0;
    }
}
