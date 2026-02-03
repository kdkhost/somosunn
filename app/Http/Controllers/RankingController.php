<?php

namespace App\Http\Controllers;

use App\Models\Ranking;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public function index()
    {
        $top = Ranking::with('user')->orderByDesc('score')->limit(6)->get();
        $summary = Ranking::select('level', DB::raw('count(*) as total'))
            ->groupBy('level')
            ->pluck('total', 'level')
            ->toArray();

        return response()->json([
            'top' => $top,
            'summary' => $summary,
        ]);
    }
}
