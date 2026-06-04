<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                'message' => 'Nao foi possivel obter o saldo.',
            ], 500);
        }
    }

    private static array $schemaCache = [];

    private function hasColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";
        if (isset(self::$schemaCache[$key])) {
            return self::$schemaCache[$key];
        }

        return self::$schemaCache[$key] = Schema::hasColumn($table, $column);
    }

    public function systemHealth()
    {
        return response()->json(Cache::remember('system_health_payload_v4', 60, function (): array {
            $basePath = base_path();
            $accountPath = dirname($basePath);
            $installSizeBytes = $this->directorySize($basePath);
            $accountSizeBytes = $this->directorySize($accountPath);
            $quotaBytes = $this->detectQuotaBytes();
            $freeBytes = $quotaBytes !== null ? max(0, $quotaBytes - $accountSizeBytes) : null;
            $diskPercent = $quotaBytes && $quotaBytes > 0
                ? round(($accountSizeBytes / $quotaBytes) * 100, 1)
                : null;

            $dbSizeMB = Cache::remember('system_health_db_size', 300, function () {
                $result = DB::select(
                    "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.tables WHERE table_schema = ?",
                    [DB::getDatabaseName()]
                );

                return round($result[0]->size ?? 0, 2);
            });

            $memoryLimit = ini_get('memory_limit');
            $estimatedConcurrent = max(10, (int) floor($this->toMegabytes($memoryLimit) / 48));

            $totalUsers = Cache::remember('system_health_total_users', 60, function () {
                return \App\Models\User::count();
            });

            $onlineRecent = 0;
            try {
                if ($this->hasColumn('users', 'last_activity_at')) {
                    $onlineRecent = Cache::remember('system_health_online_recent', 30, function () {
                        return \App\Models\User::where('last_activity_at', '>', now()->subMinutes(5))->count();
                    });
                }
            } catch (\Throwable $e) {
            }

            $cronHeartbeat = Cache::get('cron_heartbeat');
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

            $pendingOrders = Cache::remember('system_health_pending_orders', 30, function () {
                return \App\Models\Order::where('status', 'pending')->count();
            });

            $queuePending = 0;
            try {
                if (Schema::hasTable('jobs')) {
                    $queuePending = Cache::remember('system_health_queue_pending', 15, function () {
                        return DB::table('jobs')->count();
                    });
                }
            } catch (\Throwable $e) {
            }

            return [
                'disk' => [
                    'scope' => 'instalacao',
                    'scope_label' => 'Hospedagem da instalacao',
                    'install_path' => $basePath,
                    'account_path' => $accountPath,
                    'install_used_gb' => round($installSizeBytes / 1024 / 1024 / 1024, 2),
                    'account_used_gb' => round($accountSizeBytes / 1024 / 1024 / 1024, 2),
                    'total_gb' => $quotaBytes !== null ? round($quotaBytes / 1024 / 1024 / 1024, 2) : null,
                    'free_gb' => $freeBytes !== null ? round($freeBytes / 1024 / 1024 / 1024, 2) : null,
                    'percent' => $diskPercent,
                    'quota_available' => $quotaBytes !== null,
                ],
                'database' => [
                    'size_mb' => $dbSizeMB,
                ],
                'capacity' => [
                    'memory_limit' => $memoryLimit,
                    'estimated_concurrent' => $estimatedConcurrent,
                    'hosting_type' => 'Conta da aplicacao',
                    'note' => 'Medido pela instalacao em public_html e pela conta do sistema.',
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
            ];
        }));
    }

    private function directorySize(string $path): int
    {
        $cacheKey = 'system_health_dir_size:' . md5($path);

        return (int) Cache::remember($cacheKey, 600, function () use ($path): int {
            $escapedPath = escapeshellarg($path);
            $duOutput = @shell_exec("du -sb {$escapedPath} 2>/dev/null");

            if (is_string($duOutput) && preg_match('/^\s*(\d+)/', trim($duOutput), $matches)) {
                return (int) $matches[1];
            }

            if (!is_dir($path)) {
                return 0;
            }

            $size = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += (int) $file->getSize();
                }
            }

            return $size;
        });
    }

    private function detectQuotaBytes(): ?int
    {
        $output = @shell_exec('quota -sb 2>/dev/null || quota -s 2>/dev/null');

        if (!is_string($output) || trim($output) === '') {
            return null;
        }

        foreach (preg_split('/\r?\n/', trim($output)) as $line) {
            if (preg_match_all('/(\d+(?:\.\d+)?)([KMGTP])/', strtoupper($line), $matches, PREG_SET_ORDER) >= 2) {
                return $this->fromHumanSize($matches[1][1] . $matches[1][2]);
            }
        }

        return null;
    }

    private function fromHumanSize(string $value): int
    {
        if (!preg_match('/^\s*(\d+(?:\.\d+)?)([KMGTP])\s*$/i', trim($value), $matches)) {
            return 0;
        }

        $number = (float) $matches[1];
        $power = ['K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5][strtoupper($matches[2])] ?? 0;

        return (int) round($number * (1024 ** $power));
    }

    private function toMegabytes(string $memoryLimit): int
    {
        $value = trim($memoryLimit);

        if ($value === '' || $value === '-1') {
            return 2048;
        }

        if (!preg_match('/^\s*(\d+)([KMG])?\s*$/i', $value, $matches)) {
            return 512;
        }

        return match (strtoupper((string) ($matches[2] ?? 'M'))) {
            'G' => (int) $matches[1] * 1024,
            'K' => (int) ceil(((int) $matches[1]) / 1024),
            default => (int) $matches[1],
        };
    }
}
