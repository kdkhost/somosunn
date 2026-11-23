<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Ranking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RankingPublicController extends Controller
{
    /**
     * Exibe a página pública de Ranking da Comunidade SOMOS UNN.
     */
    public function index()
    {
        $demoMode = (bool) config('app.demo_mode');

        // Verifica se a funcionalidade de membros/ranking está ativa
        $isEnabled = \App\Models\Setting::get('feature_members', '1') === '1';

        if (!$isEnabled) {
            abort(404, 'Página de ranking temporariamente indisponível');
        }

        $topRankings = collect();

        try {
            // 1. Tenta carregar da tabela 'rankings' compilada
            if (Schema::hasTable('rankings')) {
                $rawRankings = Ranking::with(['user.plan'])
                    ->whereHas('user', function ($q) {
                        $q->whereNotIn('role', ['admin', 'superadmin']);
                    })
                    ->orderByDesc('score')
                    ->take(50)
                    ->get();

                if ($rawRankings->isNotEmpty()) {
                    $topRankings = $rawRankings->map(function ($rank) {
                        $user = $rank->user;
                        return (object) [
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'avatar' => $user->photo ? asset($user->photo) : null,
                            'cover' => $user->cover_photo ? asset($user->cover_photo) : null,
                            'initials' => collect(explode(' ', $user->name))->take(2)->map(fn($n) => strtoupper(mb_substr($n, 0, 1)))->join(''),
                            'occupation' => $user->occupation,
                            'company' => $user->company,
                            'city' => trim(($user->city ?? '') . ($user->state ? ', ' . $user->state : '')),
                            'plan_badge' => $user->plan?->name ? ($user->plan->is_free ? 'Starter' : $user->plan->name) : null,
                            'score' => (int) $rank->score,
                            'level' => $rank->level ?? ($user->level ?: 'Membro Ativo'),
                            'interactions_count' => (int) ($rank->interactions_count ?: 0),
                            'is_demo' => false,
                        ];
                    });
                }
            }

            // 2. Se a tabela 'rankings' estiver vazia ou com poucos registros, compila ranking real dos usuários ativos
            if ($topRankings->count() < 3) {
                $users = User::whereNotIn('role', ['admin', 'superadmin'])
                    ->with('plan')
                    ->get();

                if ($users->isNotEmpty()) {
                    // Contagem de conexões em lote para máxima performance
                    $acceptedConnections = Connection::where('status', 'accepted')
                        ->get()
                        ->flatMap(fn($c) => [$c->requester_id, $c->requested_id])
                        ->countBy();

                    $topRankings = $users->map(function ($u) use ($acceptedConnections) {
                        $conns = $acceptedConnections->get($u->id, 0);
                        $basePoints = (int) ($u->points ?? 0);
                        // Pontuação dinâmica: pontos gravados + conexões (50 pts cada) + foto (100 pts) + dados completos
                        $calculatedScore = $basePoints;
                        if ($calculatedScore <= 0) {
                            $calculatedScore = ($conns * 50) + ($u->photo ? 100 : 20) + ($u->occupation ? 50 : 0) + ($u->company ? 30 : 0);
                        }

                        $level = $u->level;
                        if (empty($level)) {
                            if ($calculatedScore >= 1000) $level = 'Elite UNN';
                            elseif ($calculatedScore >= 500) $level = 'Líder de Negócios';
                            elseif ($calculatedScore >= 200) $level = 'Empreendedor Pro';
                            else $level = 'Membro Ativo';
                        }

                        return (object) [
                            'user_id' => $u->id,
                            'name' => $u->name,
                            'avatar' => $u->photo ? asset($u->photo) : null,
                            'cover' => $u->cover_photo ? asset($u->cover_photo) : null,
                            'initials' => collect(explode(' ', $u->name))->take(2)->map(fn($n) => strtoupper(mb_substr($n, 0, 1)))->join(''),
                            'occupation' => $u->occupation,
                            'company' => $u->company,
                            'city' => trim(($u->city ?? '') . ($u->state ? ', ' . $u->state : '')),
                            'plan_badge' => $u->plan?->name ? ($u->plan->is_free ? 'Starter' : $u->plan->name) : null,
                            'score' => $calculatedScore,
                            'level' => $level,
                            'interactions_count' => $conns,
                            'is_demo' => false,
                        ];
                    })
                    ->sortByDesc('score')
                    ->take(50)
                    ->values();
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Falha ao processar ranking público: ' . $e->getMessage());
        }

        // 3. Fallback para demonstração se a base estiver totalmente limpa
        if ($topRankings->isEmpty()) {
            $topRankings = collect([
                (object) [
                    'user_id' => 1,
                    'name' => 'Carlos Eduardo Silva',
                    'avatar' => null,
                    'cover' => null,
                    'initials' => 'CS',
                    'occupation' => 'CEO & Fundador',
                    'company' => 'Tech Solutions LTDA',
                    'city' => 'São Paulo, SP',
                    'plan_badge' => 'Elite',
                    'score' => 15420,
                    'level' => 'Elite UNN',
                    'interactions_count' => 342,
                    'is_demo' => true,
                ],
                (object) [
                    'user_id' => 2,
                    'name' => 'Ana Paula Costa',
                    'avatar' => null,
                    'cover' => null,
                    'initials' => 'AC',
                    'occupation' => 'CMO & Estrategista',
                    'company' => 'Growth Marketing Co.',
                    'city' => 'Rio de Janeiro, RJ',
                    'plan_badge' => 'Pro',
                    'score' => 12850,
                    'level' => 'Líder de Negócios',
                    'interactions_count' => 215,
                    'is_demo' => true,
                ],
                (object) [
                    'user_id' => 3,
                    'name' => 'Roberto Mendes',
                    'avatar' => null,
                    'cover' => null,
                    'initials' => 'RM',
                    'occupation' => 'Diretor de Operações',
                    'company' => 'Mendes Logística',
                    'city' => 'Belo Horizonte, MG',
                    'plan_badge' => 'Pro',
                    'score' => 9740,
                    'level' => 'Líder de Negócios',
                    'interactions_count' => 180,
                    'is_demo' => true,
                ],
                (object) [
                    'user_id' => 4,
                    'name' => 'Juliana Ferreira',
                    'avatar' => null,
                    'cover' => null,
                    'initials' => 'JF',
                    'occupation' => 'Fundadora',
                    'company' => 'EcoFashion Brasil',
                    'city' => 'Curitiba, PR',
                    'plan_badge' => 'Starter',
                    'score' => 5120,
                    'level' => 'Empreendedor Pro',
                    'interactions_count' => 89,
                    'is_demo' => true,
                ],
                (object) [
                    'user_id' => 5,
                    'name' => 'Fernando Oliveira',
                    'avatar' => null,
                    'cover' => null,
                    'initials' => 'FO',
                    'occupation' => 'Consultor Empresarial',
                    'company' => 'Digital Transform',
                    'city' => 'Porto Alegre, RS',
                    'plan_badge' => 'Starter',
                    'score' => 3100,
                    'level' => 'Membro Ativo',
                    'interactions_count' => 42,
                    'is_demo' => true,
                ],
            ]);
        }

        // Separação: Pódio (Top 3) e Lista Geral (Restante)
        $podium = $topRankings->take(3);
        $remaining = $topRankings->slice(3);

        return view('site.ranking', [
            'podium' => $podium,
            'remaining' => $remaining,
            'totalRanked' => $topRankings->count(),
            'isDemo' => $topRankings->first()->is_demo ?? false,
        ]);
    }
}
