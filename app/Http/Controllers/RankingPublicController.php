<?php

namespace App\Http\Controllers;

use App\Models\Ranking;
use App\Models\User;
use Illuminate\Http\Request;

class RankingPublicController extends Controller
{
    /**
     * Display a listing of members ranked by points for the public site.
     */
    public function index()
    {
        $demoMode = (bool) config('app.demo_mode');

        // Check if members feature is enabled globally
        $isEnabled = \App\Models\Setting::get('feature_members', '1') === '1';

        if (!$isEnabled) {
            abort(404, 'Página de membros/ranking temporariamente indisponível');
        }

        // Tenta obter o ranking da tabela 'rankings' que compila interações e avaliações (como a Home)
        $topRankings = collect();

        if (view()->shared('unnDbAvailable')) {
            try {
                // 1. Pega TODOS os membros do Ranking (que não sejam admins)
                $leaderboard = Ranking::with([
                    'user' => function ($q) {
                        $q->whereNotIn('role', ['admin', 'superadmin']);
                    }
                ])
                    ->orderByDesc('score')
                    ->get()
                    ->filter(function ($rank) {
                        return $rank->user && !in_array($rank->user->role, ['admin', 'superadmin']);
                    })
                    ->values();

                // 2. Fallback: Se não tem tabela de Rankings (ou vazia), usa os pontos brutos do Usuário
                if ($leaderboard->isEmpty()) {
                    $leaderboard = User::whereNotIn('role', ['admin', 'superadmin'])
                        ->where('points', '>', 0)
                        ->orderByDesc('points')
                        ->get()
                        ->map(fn($u) => (object) [
                            'user' => $u,
                            'score' => $u->points ?? 0,
                            'level' => $u->level ?? 'iniciante',
                            'interactions_count' => 0,
                            'average_rating' => null,
                            'is_points_fallback' => true,
                        ])
                        ->values();
                }

                $topRankings = $leaderboard;

            } catch (\Throwable $e) {
                \Log::warning('Falha ao gerar o ranking público: ' . $e->getMessage());
            }
        }

        // 3. Fallback final para Modo Demonstração se o banco estiver limpo
        if ($demoMode && $topRankings->isEmpty()) {
            $topRankings = collect([
                (object) [
                    'user' => (object) [
                        'name' => 'Carlos Eduardo',
                        'profile_photo_url' => null,
                        'username' => 'carlos-demo'
                    ],
                    'score' => 15420,
                    'level' => 'Empresário de Sucesso',
                    'interactions_count' => 342,
                    'average_rating' => 4.9,
                ],
                (object) [
                    'user' => (object) [
                        'name' => 'Ana Paula',
                        'profile_photo_url' => null,
                        'username' => 'ana-demo'
                    ],
                    'score' => 12850,
                    'level' => 'Iniciante',
                    'interactions_count' => 215,
                    'average_rating' => 4.8,
                ],
                (object) [
                    'user' => (object) [
                        'name' => 'Roberto Mendes',
                        'profile_photo_url' => null,
                        'username' => 'roberto-demo'
                    ],
                    'score' => 9740,
                    'level' => 'Sucesso',
                    'interactions_count' => 180,
                    'average_rating' => 5.0,
                ],
                (object) [
                    'user' => (object) [
                        'name' => 'Juliana Costa',
                        'profile_photo_url' => null,
                        'username' => 'juliana-demo'
                    ],
                    'score' => 5120,
                    'level' => 'Empreendedora',
                    'interactions_count' => 89,
                    'average_rating' => 4.5,
                ],
                (object) [
                    'user' => (object) [
                        'name' => 'Fernando Silva',
                        'profile_photo_url' => null,
                        'username' => 'fernando-demo'
                    ],
                    'score' => 3100,
                    'level' => 'Iniciante',
                    'interactions_count' => 42,
                    'average_rating' => 4.2,
                ],
            ]);
        }

        // Separação do Pódio (Top 3) e o Restante (Lista normal)
        $podium = $topRankings->take(3);
        $remaining = $topRankings->slice(3);

        return view('site.ranking', [
            'podium' => $podium,
            'remaining' => $remaining,
            'isDemo' => $demoMode
        ]);
    }
}
