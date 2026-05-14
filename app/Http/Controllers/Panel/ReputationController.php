<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MemberReputationHistory;
use App\Services\ReputationService;
use Illuminate\Support\Facades\Auth;

/**
 * Pagina de detalhes da reputacao do membro logado.
 *
 * Mostra:
 * - Badge de reputacao (size lg)
 * - Breakdown das 4 dimensoes (Entrega, Relacionamento, Interacao, Engajamento)
 * - Dicas de melhoria por dimensao com score < 50
 * - Historico do score nos ultimos 6 meses
 */
class ReputationController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $service = app(ReputationService::class);
        $reputationData = $service->getScore($user->id);

        // Historico dos ultimos 6 meses
        $history = MemberReputationHistory::where('user_id', $user->id)
            ->where('recorded_at', '>=', now()->subMonths(6))
            ->orderBy('recorded_at', 'asc')
            ->get(['recorded_at', 'overall_score']);

        return view('panel.reputation.show', compact('user', 'reputationData', 'history'));
    }
}
