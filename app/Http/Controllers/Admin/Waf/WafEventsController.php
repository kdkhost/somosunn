<?php

namespace App\Http\Controllers\Admin\Waf;

use App\Http\Controllers\Controller;
use App\Models\Waf\WafEvent;
use App\Models\Waf\WafFalsePositive;
use App\Services\Waf\IpListService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WafEventsController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('waf_events')) {
            return view('admin.waf.events.index', ['events' => collect(), 'hasTable' => false]);
        }

        $query = WafEvent::query()->orderByDesc('occurred_at');

        // Filtro por periodo
        if ($request->filled('date_from')) {
            $query->where('occurred_at', '>=', $request->input('date_from') . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('occurred_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        // Filtro por IP
        if ($request->filled('ip')) {
            $query->where('ip', 'like', '%' . $request->input('ip') . '%');
        }

        // Filtro por rota
        if ($request->filled('route')) {
            $query->where('route', 'like', '%' . $request->input('route') . '%');
        }

        // Filtro por metodo HTTP
        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        // Filtro por decisao
        if ($request->filled('decision')) {
            $query->where('decision', $request->input('decision'));
        }

        // Filtro por attack_pattern
        if ($request->filled('attack_pattern')) {
            $query->whereJsonContains('rules_fired', [['attack_pattern' => $request->input('attack_pattern')]]);
        }

        // Filtro por risk_score minimo
        if ($request->filled('risk_score_min')) {
            $query->where('risk_score', '>=', (int) $request->input('risk_score_min'));
        }

        // Busca livre (IP, rota, user_agent)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ip', 'like', "%{$search}%")
                  ->orWhere('route', 'like', "%{$search}%")
                  ->orWhere('path', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate(25)->withQueryString();

        return view('admin.waf.events.index', [
            'events'   => $events,
            'hasTable' => true,
        ]);
    }

    public function show($id)
    {
        if (! Schema::hasTable('waf_events')) {
            abort(404);
        }

        $event = WafEvent::findOrFail($id);

        return view('admin.waf.events.show', compact('event'));
    }

    public function markFalsePositive(Request $request, $id)
    {
        if (! Schema::hasTable('waf_events') || ! Schema::hasTable('waf_false_positives')) {
            return back()->with('error', 'Tabelas WAF nao disponiveis.');
        }

        $event = WafEvent::findOrFail($id);

        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $event->update(['is_false_positive' => true]);

        WafFalsePositive::create([
            'event_id'    => $event->id,
            'rule_id'     => null,
            'reviewed_by' => auth()->id(),
            'note'        => $request->input('note', ''),
        ]);

        try {
            Log::channel('security')->info('WAF evento marcado como falso positivo', [
                'event_id' => $event->id,
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Evento marcado como falso positivo.');
    }

    public function export(Request $request): StreamedResponse
    {
        if (! Schema::hasTable('waf_events')) {
            abort(404);
        }

        $format = $request->input('format', 'csv');

        $query = WafEvent::query()->orderByDesc('occurred_at');

        // Aplicar mesmos filtros do index
        if ($request->filled('date_from')) {
            $query->where('occurred_at', '>=', $request->input('date_from') . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('occurred_at', '<=', $request->input('date_to') . ' 23:59:59');
        }
        if ($request->filled('ip')) {
            $query->where('ip', 'like', '%' . $request->input('ip') . '%');
        }
        if ($request->filled('decision')) {
            $query->where('decision', $request->input('decision'));
        }
        if ($request->filled('risk_score_min')) {
            $query->where('risk_score', '>=', (int) $request->input('risk_score_min'));
        }

        if ($format === 'json') {
            return new StreamedResponse(function () use ($query) {
                echo '[';
                $first = true;
                $query->chunk(100, function ($events) use (&$first) {
                    foreach ($events as $event) {
                        if (! $first) {
                            echo ',';
                        }
                        echo json_encode($event->toArray(), JSON_UNESCAPED_UNICODE);
                        $first = false;
                    }
                });
                echo ']';
            }, 200, [
                'Content-Type'        => 'application/json',
                'Content-Disposition' => 'attachment; filename="waf-events-' . date('Y-m-d') . '.json"',
            ]);
        }

        // CSV
        return new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'UID', 'Data', 'IP', 'Pais', 'Metodo', 'Rota', 'Risk Score', 'Decisao', 'User Agent']);

            $query->chunk(100, function ($events) use ($handle) {
                foreach ($events as $event) {
                    fputcsv($handle, [
                        $event->id,
                        $event->uid,
                        $event->occurred_at?->format('Y-m-d H:i:s'),
                        $event->ip,
                        $event->country,
                        $event->method,
                        $event->route,
                        $event->risk_score,
                        $event->decision,
                        $event->user_agent,
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="waf-events-' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function blockIp(Request $request, $id)
    {
        if (! Schema::hasTable('waf_events') || ! Schema::hasTable('waf_ip_blocklist')) {
            return back()->with('error', 'Tabelas WAF nao disponiveis.');
        }

        $event = WafEvent::findOrFail($id);
        $ipService = app(IpListService::class);

        $entry = $ipService->block(
            $event->ip,
            null,
            'Bloqueado via evento WAF #' . $event->id,
            auth()->id(),
            'manual'
        );

        try {
            Log::channel('security')->info('WAF IP bloqueado via evento', [
                'ip'       => $event->ip,
                'event_id' => $event->id,
                'actor_id' => auth()->id(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', "IP {$event->ip} adicionado a blocklist.");
    }

    public function allowIp(Request $request, $id)
    {
        if (! Schema::hasTable('waf_events') || ! Schema::hasTable('waf_ip_allowlist')) {
            return back()->with('error', 'Tabelas WAF nao disponiveis.');
        }

        $event = WafEvent::findOrFail($id);
        $ipService = app(IpListService::class);

        $entry = $ipService->allow(
            $event->ip,
            null,
            'Permitido via evento WAF #' . $event->id,
            auth()->id()
        );

        try {
            Log::channel('security')->info('WAF IP permitido via evento', [
                'ip'       => $event->ip,
                'event_id' => $event->id,
                'actor_id' => auth()->id(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', "IP {$event->ip} adicionado a allowlist.");
    }
}
