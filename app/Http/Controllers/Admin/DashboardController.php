<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $metrics)
    {
    }

    public function index()
    {
        $payload = $this->metrics->adminPayload(auth()->user());

        if (request()->routeIs('panel.*')) {
            return view('panel.admin.dashboard', $payload);
        }

        return view('admin.dashboard', $payload);
    }

    public function stats(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax() && !$request->wantsJson()) {
            return redirect()->route($request->routeIs('panel.*') ? 'panel.admin.dashboard' : 'admin.dashboard');
        }

        return response()->json([
            'success' => true,
        ] + $this->metrics->adminPayload(auth()->user(), $request->boolean('fresh')));
    }

}
