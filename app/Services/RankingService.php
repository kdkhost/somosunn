<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\Interaction;
use App\Models\Ranking;
use App\Models\Satisfaction;
use App\Models\User;
use App\Support\UploadStorage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class RankingService
{
    /**
     * Retorna a lista oficial unificada do Ranking Público (membros qualificados, sem contas de sistema).
     */
    public static function getPublicLeaderboard(int $limit = 50): Collection
    {
        return Cache::remember('unn:public_leaderboard:v4:' . $limit, 60, function () use ($limit) {
            $topRankings = collect();

            try {
                // Contagem de conexões aceitas reais
                $acceptedConnections = Connection::where('status', 'accepted')
                    ->get()
                    ->flatMap(fn($c) => [$c->requester_id, $c->requested_id])
                    ->countBy();

                // 1. Tenta carregar da tabela 'rankings' compilada excluindo apenas os administradores do sistema
                if (Schema::hasTable('rankings')) {
                    $rawRankings = Ranking::with(['user.plan'])
                        ->whereHas('user', function ($q) {
                            $q->whereNotIn('role', ['admin', 'superadmin'])
                              ->where('name', 'not like', '%Admin Somos UNN%')
                              ->where('name', 'not like', '%Administrador%')
                              ->where('email', 'not like', 'admin@somosunn.com.br');
                        })
                        ->orderByDesc('score')
                        ->orderByDesc('id')
                        ->take($limit)
                        ->get();

                    if ($rawRankings->isNotEmpty()) {
                        $topRankings = $rawRankings->map(function ($rank) use ($acceptedConnections) {
                            $user = $rank->user;
                            $realConns = (int) $acceptedConnections->get($user->id, 0);

                            $photoField = $user->photo ?: $user->avatar;
                            $avatarUrl = null;
                            if (!empty($photoField)) {
                                if (str_starts_with($photoField, 'http://') || str_starts_with($photoField, 'https://')) {
                                    $avatarUrl = $photoField;
                                } else {
                                    $avatarUrl = UploadStorage::url($photoField);
                                }
                            }
                            $coverUrl = !empty($user->cover_photo) ? UploadStorage::url($user->cover_photo) : null;

                            $score = (int) $rank->score;
                            $friendlyLevel = $rank->level ?? $user->level;
                            if (empty($friendlyLevel) || mb_strtolower($friendlyLevel) === 'iniciante') {
                                if ($score >= 4000) $friendlyLevel = 'Elite UNN';
                                elseif ($score >= 1000) $friendlyLevel = 'Líder de Negócios';
                                elseif ($score >= 200) $friendlyLevel = 'Empreendedor Pro';
                                else $friendlyLevel = 'Membro UNN';
                            }

                            return (object) [
                                'user_id' => $user->id,
                                'name' => $user->name,
                                'avatar' => $avatarUrl,
                                'cover' => $coverUrl,
                                'initials' => collect(explode(' ', $user->name))->take(2)->map(fn($n) => strtoupper(mb_substr($n, 0, 1)))->join(''),
                                'occupation' => $user->occupation,
                                'company' => $user->company,
                                'city' => trim(($user->city ?? '') . ($user->state ? ', ' . $user->state : '')),
                                'plan_badge' => $user->plan?->name ? ($user->plan->is_free ? 'Starter' : $user->plan->name) : null,
                                'score' => $score,
                                'level' => $friendlyLevel,
                                'connections_count' => $realConns,
                                'interactions_count' => $realConns,
                                'is_demo' => false,
                            ];
                        });
                    }
                }

                // 2. Se a tabela 'rankings' estiver vazia ou com poucos registros, compila ranking real dos usuários ativos
                if ($topRankings->count() < 3) {
                    $users = User::whereNotIn('role', ['admin', 'superadmin'])
                        ->where('name', 'not like', '%Admin Somos UNN%')
                        ->where('name', 'not like', '%Administrador%')
                        ->where('email', 'not like', 'admin@somosunn.com.br')
                        ->with('plan')
                        ->orderByDesc('points')
                        ->get();

                    if ($users->isNotEmpty()) {
                        $topRankings = $users->map(function ($u) use ($acceptedConnections) {
                            $conns = (int) $acceptedConnections->get($u->id, 0);
                            $basePoints = (int) ($u->points ?? 0);
                            $calculatedScore = $basePoints;
                            if ($calculatedScore <= 0) {
                                $calculatedScore = ($conns * 50) + ($u->photo ? 100 : 20) + ($u->occupation ? 50 : 0) + ($u->company ? 30 : 0);
                            }

                            $level = $u->level;
                            if (empty($level) || mb_strtolower($level) === 'iniciante') {
                                if ($calculatedScore >= 4000) $level = 'Elite UNN';
                                elseif ($calculatedScore >= 1000) $level = 'Líder de Negócios';
                                elseif ($calculatedScore >= 200) $level = 'Empreendedor Pro';
                                else $level = 'Membro UNN';
                            }

                            $photoField = $u->photo ?: $u->avatar;
                            $avatarUrl = null;
                            if (!empty($photoField)) {
                                if (str_starts_with($photoField, 'http://') || str_starts_with($photoField, 'https://')) {
                                    $avatarUrl = $photoField;
                                } else {
                                    $avatarUrl = UploadStorage::url($photoField);
                                }
                            }
                            $coverUrl = !empty($u->cover_photo) ? UploadStorage::url($u->cover_photo) : null;

                            return (object) [
                                'user_id' => $u->id,
                                'name' => $u->name,
                                'avatar' => $avatarUrl,
                                'cover' => $coverUrl,
                                'initials' => collect(explode(' ', $u->name))->take(2)->map(fn($n) => strtoupper(mb_substr($n, 0, 1)))->join(''),
                                'occupation' => $u->occupation,
                                'company' => $u->company,
                                'city' => trim(($u->city ?? '') . ($u->state ? ', ' . $u->state : '')),
                                'plan_badge' => $u->plan?->name ? ($u->plan->is_free ? 'Starter' : $u->plan->name) : null,
                                'score' => $calculatedScore,
                                'level' => $level,
                                'connections_count' => $conns,
                                'interactions_count' => $conns,
                                'is_demo' => false,
                            ];
                        })
                        ->sortByDesc('score')
                        ->take($limit)
                        ->values();
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Falha ao processar ranking público: ' . $e->getMessage());
            }

            return $topRankings;
        });
    }

    public function refreshFor(User $user): Ranking
    {
        $stats = Satisfaction::query()
            ->whereHas('interaction', fn ($query) => $query->where('user_to_id', $user->id))
            ->selectRaw('count(*) as total, avg(rating) as average')
            ->first();

        $total = (int) ($stats->total ?? 0);
        $average = (float) ($stats->average ?? 0);
        $score = $this->calculateScore($total, $average);

        return Ranking::updateOrCreate(
            ['user_id' => $user->id],
            [
                'level' => $user->level,
                'interactions_count' => $total,
                'average_rating' => number_format($average, 2, '.', ''),
                'score' => number_format($score, 2, '.', ''),
            ]
        );
    }

    public function refreshFromInteraction(Interaction $interaction): array
    {
        $updated = [];
        foreach ([$interaction->user_from, $interaction->user_to] as $user) {
            if ($user) {
                $updated[] = $this->refreshFor($user);
            }
        }
        return $updated;
    }

    private function calculateScore(int $total, float $average): float
    {
        if ($total === 0 || $average === 0) {
            return 0.0;
        }
        return ($average * 20) + (log($total + 1) * 5);
    }
}
