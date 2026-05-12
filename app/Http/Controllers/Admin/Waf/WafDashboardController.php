<?php

namespace App\Http\Controllers\Admin\Waf;

use App\Http\Controllers\Controller;
use App\Models\Waf\WafEvent;
use App\Services\Waf\WafSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dashboard do WAF no painel do superadmin (AdminLTE).
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 13.3, 13.4, 13.5, 13.6
 */
class WafDashboardController extends Controller
{
    public function index()
    {
        $wafSettings = WafSettings::load();

        return view('admin.waf.dashboard', [
            'wafSettings' => $wafSettings,
            'hasTable'    => Schema::hasTable('waf_events'),
        ]);
    }

    /**
     * Endpoint JSON para polling jQuery (30s).
     */
    public function data(Request $request): JsonResponse
    {
        if (! Schema::hasTable('waf_events')) {
            return response()->json([
                'kpis'       => ['inspected' => 0, 'blocked' => 0, 'monitored' => 0, 'challenged' => 0],
                'timeline'   => [],
                'top_ips'    => [],
                'top_routes' => [],
                'by_pattern' => [],
            ]);
        }

        $since = $request->query('since', now()->subDay()->toDateTimeString());

        // KPIs últimas 24h
        $kpis = WafEvent::query()
            ->where('occurred_at', '>=', now()->subDay())
            ->selectRaw("
                COUNT(*) as inspected,
                SUM(CASE WHEN decision = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN decision = 'monitored' THEN 1 ELSE 0 END) as monitored,
                SUM(CASE WHEN decision = 'challenged' THEN 1 ELSE 0 END) as challenged
            ")
            ->first();

        // Timeline (agrupado por hora nas últimas 24h)
        $timeline = WafEvent::query()
            ->where('occurred_at', '>=', now()->subDay())
            ->selectRaw("
                DATE_FORMAT(occurred_at, '%Y-%m-%d %H:00:00') as ts,
                SUM(CASE WHEN decision = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN decision = 'monitored' THEN 1 ELSE 0 END) as monitored,
                SUM(CASE WHEN decision = 'challenged' THEN 1 ELSE 0 END) as challenged
            ")
            ->groupByRaw("DATE_FORMAT(occurred_at, '%Y-%m-%d %H:00:00')")
            ->orderBy('ts')
            ->get();

        // Top 10 IPs atacantes
        $topIps = WafEvent::query()
            ->where('occurred_at', '>=', now()->subDay())
            ->whereIn('decision', ['blocked', 'challenged', 'monitored'])
            ->select('ip', 'country')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('ip', 'country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Top 10 rotas atacadas
        $topRoutes = WafEvent::query()
            ->where('occurred_at', '>=', now()->subDay())
            ->whereIn('decision', ['blocked', 'challenged', 'monitored'])
            ->whereNotNull('route')
            ->select('route')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('route')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Distribuição por Attack_Pattern (extraído de rules_fired)
        $byPattern = [];
        $events = WafEvent::query()
            ->where('occurred_at', '>=', now()->subDay())
            ->whereNotNull('rules_fired')
            ->pluck('rules_fired');

        foreach ($events as $rulesFired) {
            $rules = is_array($rulesFired) ? $rulesFired : (json_decode($rulesFired, true) ?? []);
            foreach ($rules as $r) {
                $pattern = $r['attack_pattern'] ?? 'Unknown';
                $byPattern[$pattern] = ($byPattern[$pattern] ?? 0) + 1;
            }
        }
        arsort($byPattern);

        return response()->json([
            'kpis'       => [
                'inspected'  => (int) ($kpis->inspected ?? 0),
                'blocked'    => (int) ($kpis->blocked ?? 0),
                'monitored'  => (int) ($kpis->monitored ?? 0),
                'challenged' => (int) ($kpis->challenged ?? 0),
            ],
            'timeline'   => $timeline,
            'top_ips'    => $topIps,
            'top_routes' => $topRoutes,
            'by_pattern' => $byPattern,
        ]);
    }

    /**
     * Alterna modo de operação (detection-only / enforce).
     */
    public function toggleMode(Request $request): JsonResponse
    {
        $newMode = $request->input('mode');

        if (! in_array($newMode, [WafSettings::MODE_DETECTION, WafSettings::MODE_ENFORCE], true)) {
            return response()->json(['error' => 'Modo inválido'], 422);
        }

        if (Schema::hasTable('waf_settings')) {
            DB::table('waf_settings')
                ->where('key', 'waf.mode')
                ->update([
                    'value'      => json_encode($newMode),
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
        }

        try {
            \Illuminate\Support\Facades\Log::channel('security')->info('WAF modo alterado', [
                'new_mode' => $newMode,
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json(['mode' => $newMode, 'success' => true]);
    }
}
