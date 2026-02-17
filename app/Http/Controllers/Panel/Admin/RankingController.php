<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    public function index()
    {
        $this->ensurePermission('ranking.view');

        $top = User::query()
            ->orderByDesc('points')
            ->limit(100)
            ->get();

        return view('panel.admin.ranking.index', compact('top'));
    }

    private function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
