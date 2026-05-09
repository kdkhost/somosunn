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

    /**
     * Retorna informações de saúde do sistema para exibição na dashboard.
     */
    public function systemHealth()
    {
        $diskTotal = @disk_total_space('/home/somosunn/') ?: @disk_total_space('/');
        $diskFree = @disk_free_space('/home/somosunn/') ?: @disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

        $dbSize = \DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.tables WHERE table_schema = ?", [\DB::getDatabaseName()]);
        $dbSizeMB = round($dbSize[0]->size ?? 0, 2);

        $memoryLimit = ini_get('memory_limit');
        $memLimitMB = (int) str_replace(['M', 'G'], ['', '000'], $memoryLimit);
        $estimatedConcurrent = max(10, (int) ($memLimitMB / 32));

        $totalUsers = \App\Models\User::count();
        $onlineRecent = \App\Models\User::where('last_activity_at', '>', now()->subMinutes(5))->count();

        $cronHeartbeat = \Illuminate\Support\Facades\Cache::get('cron_heartbeat');
        $cronActive = $cronHeartbeat && (time() - $cronHeartbeat) < 300;

        $pendingOrders = \App\Models\Order::where('status', 'pending')->count();

        return response()->json([
            'disk' => [
                'total_gb' => round($diskTotal / 1024 / 1024 / 1024, 2),
                'used_gb' => round($diskUsed / 1024 / 1024 / 1024, 2),
                'free_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
                'percent' => $diskPercent,
            ],
            'database' => [
                'size_mb' => $dbSizeMB,
            ],
            'capacity' => [
                'memory_limit' => $memoryLimit,
                'estimated_concurrent' => $estimatedConcurrent,
                'recommended_max' => min(100, $estimatedConcurrent * 2),
                'hosting_type' => 'Compartilhada',
            ],
            'users' => [
                'total' => $totalUsers,
                'online_now' => $onlineRecent,
            ],
            'cron' => [
                'active' => $cronActive,
                'last_run' => $cronHeartbeat ? date('H:i:s', $cronHeartbeat) : null,
            ],
            'orders_pending' => $pendingOrders,
            'php_version' => phpversion(),
        ]);
    }
}
