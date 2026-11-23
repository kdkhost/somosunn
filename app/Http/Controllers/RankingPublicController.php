<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Http\Request;

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

        $topRankings = RankingService::getPublicLeaderboard(50);

        // Fallback para demonstração se a base estiver totalmente limpa
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
                    'connections_count' => 342,
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
                    'connections_count' => 215,
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
                    'connections_count' => 180,
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
                    'connections_count' => 89,
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
                    'level' => 'Membro UNN',
                    'connections_count' => 42,
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
