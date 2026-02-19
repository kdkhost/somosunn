<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')->latest()->take(1000)->get();
        return view('admin.activity_logs.index', compact('logs'));
    }

    public function clear()
    {
        ActivityLog::truncate();
        return redirect()->back()->with('success', 'Histórico de logs limpo com sucesso.');
    }
}
