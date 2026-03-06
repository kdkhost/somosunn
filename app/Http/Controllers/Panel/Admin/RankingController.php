<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    public function index()
    {
        $this->ensurePermission('ranking.view');

        $rankingQuery = User::query()
            ->where('points', '>', 0)
            ->orderByDesc('points')
            ->orderBy('name');

        $podium = (clone $rankingQuery)
            ->limit(3)
            ->get();

        $rankedUsers = $rankingQuery
            ->paginate(20)
            ->withQueryString();

        return view('panel.admin.ranking.index', compact('podium', 'rankedUsers'));
    }

    private function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
