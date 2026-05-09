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
     * Retorna o saldo do MercadoPago via AJAX.
     */
    public function getMpBalance(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('admin.dashboard');
        }

        try {
            $mpService = new \App\Services\Payment\MercadoPagoService();
            $balance = $mpService->getBalance(null);

            return response()->json([
                'success' => true,
                'balance' => $balance,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível obter o saldo.',
            ], 500);
        }
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

        // Tamanho do banco de dados (query otimizada com cache de 5 min)
        $dbSizeMB = \Illuminate\Support\Facades\Cache::remember('system_health_db_size', 300, function () {
            $result = \DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.tables WHERE table_schema = ?", [\DB::getDatabaseName()]);
            return round($result[0]->size ?? 0, 2);
        });

        $memoryLimit = ini_get('memory_limit');
        $memLimitMB = (int) str_replace(['M', 'G'], ['', '000'], $memoryLimit);
        $estimatedConcurrent = max(10, (int) ($memLimitMB / 32));

        // Contagens otimizadas (cache curto de 60s)
        $totalUsers = \Illuminate\Support\Facades\Cache::remember('system_health_total_users', 60, function () {
            return \App\Models\User::count();
        });

        $onlineRecent = 0;
        try {
            if (\Schema::hasColumn('users', 'last_activity_at')) {
                $onlineRecent = \App\Models\User::where('last_activity_at', '>', now()->subMinutes(5))->count();
            }
        } catch (\Throwable $e) {
        }

        $cronHeartbeat = \Illuminate\Support\Facades\Cache::get('cron_heartbeat');
        $cronActive = false;
        $cronLastRun = null;

        if ($cronHeartbeat) {
            if ($cronHeartbeat instanceof \Carbon\Carbon || $cronHeartbeat instanceof \DateTimeInterface) {
                $cronActive = $cronHeartbeat->diffInSeconds(now()) < 300;
                $cronLastRun = $cronHeartbeat->format('H:i:s');
            } else {
                $ts = is_numeric($cronHeartbeat) ? (int) $cronHeartbeat : strtotime((string) $cronHeartbeat);
                $cronActive = $ts && (time() - $ts) < 300;
                $cronLastRun = $ts ? date('H:i:s', $ts) : null;
            }
        }

        $pendingOrders = \App\Models\Order::where('status', 'pending')->count();

        // Informações de fila
        $queuePending = 0;
        try {
            if (\Schema::hasTable('jobs')) {
                $queuePending = \DB::table('jobs')->count();
            }
        } catch (\Throwable $e) {
        }

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
                'note' => $estimatedConcurrent < 30
                    ? 'Capacidade limitada. Considere upgrade de plano.'
                    : 'Capacidade adequada para uso atual.',
            ],
            'users' => [
                'total' => $totalUsers,
                'online_now' => $onlineRecent,
            ],
            'cron' => [
                'active' => $cronActive,
                'last_run' => $cronLastRun,
            ],
            'queue' => [
                'pending_jobs' => $queuePending,
            ],
            'orders_pending' => $pendingOrders,
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
        ]);
    }
}
