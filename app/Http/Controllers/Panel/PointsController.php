<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PointsLog;
use App\Models\PointsRule;
use App\Models\User;
use App\Services\PointsExchangeService;

class PointsController extends Controller
{
    public function __construct(private readonly PointsExchangeService $exchangeService)
    {
    }

    public function index()
    {
        $user = auth()->user();

        // Histórico paginado
        $logs = PointsLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        // Mapa de regras: action_key => PointsRule (para labels e ícones)
        $rules = PointsRule::all()->keyBy('key');

        // Posição no ranking geral (quantos usuários têm mais pontos + 1)
        $rankPosition = User::where('points', '>', $user->points ?? 0)->count() + 1;

        // Total de usuários com pelo menos 1 ponto
        $totalRanked = User::where('points', '>', 0)->count();

        // Pontos ganhos este mês
        $unnbitThisMonth = PointsLog::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('points');

        // Top 10 para mini-ranking motivacional
        $topUsers = User::select('id', 'name', 'photo', 'points')
            ->orderByDesc('points')
            ->limit(10)
            ->get();

        $exchangeSettings = $this->exchangeService->settings();

        return view('panel.points.index', compact(
            'user',
            'logs',
            'rules',
            'rankPosition',
            'totalRanked',
            'unnbitThisMonth',
            'topUsers',
            'exchangeSettings'
        ));
    }
}
