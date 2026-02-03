<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class RankingController extends Controller
{
    public function index()
    {
        $top = User::orderByDesc('points')->limit(50)->get();
        return view('admin.ranking.index', compact('top'));
    }
}