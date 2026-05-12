<?php

namespace App\Http\Controllers\Admin\Waf;

use App\Http\Controllers\Controller;
use App\Models\Waf\WafAlertConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WafAlertsController extends Controller
{
    public function index()
    {
        if (! Schema::hasTable('waf_alerts_config')) {
            return view('admin.waf.alerts', ['alerts' => collect(), 'hasTable' => false]);
        }

        $alerts = WafAlertConfig::query()->orderByDesc('created_at')->paginate(25);

        return view('admin.waf.alerts', [
            'alerts'   => $alerts,
            'hasTable' => true,
        ]);
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('waf_alerts_config')) {
            return back()->with('error', 'Tabela waf_alerts_config nao disponivel.');
        }

        $request->validate([
            'channel'   => 'required|in:email,webhook',
            'target'    => 'required|string|max:500',
            'trigger'   => 'required|in:block_spike,auto_block,critical_finding,ip_reputation',
            'threshold' => 'nullable|json',
        ]);

        WafAlertConfig::create([
            'channel'    => $request->input('channel'),
            'target'     => $request->input('target'),
            'trigger'    => $request->input('trigger'),
            'threshold'  => $request->filled('threshold') ? json_decode($request->input('threshold'), true) : null,
            'is_active'  => true,
            'created_by' => auth()->id(),
        ]);

        try {
            Log::channel('security')->info('WAF alerta criado', [
                'channel'  => $request->input('channel'),
                'trigger'  => $request->input('trigger'),
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Alerta configurado com sucesso.');
    }

    public function update(Request $request, $id)
    {
        if (! Schema::hasTable('waf_alerts_config')) {
            return back()->with('error', 'Tabela nao disponivel.');
        }

        $alert = WafAlertConfig::findOrFail($id);

        $request->validate([
            'channel'   => 'required|in:email,webhook',
            'target'    => 'required|string|max:500',
            'trigger'   => 'required|in:block_spike,auto_block,critical_finding,ip_reputation',
            'threshold' => 'nullable|json',
            'is_active' => 'required|boolean',
        ]);

        $alert->update([
            'channel'   => $request->input('channel'),
            'target'    => $request->input('target'),
            'trigger'   => $request->input('trigger'),
            'threshold' => $request->filled('threshold') ? json_decode($request->input('threshold'), true) : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        try {
            Log::channel('security')->info('WAF alerta atualizado', [
                'alert_id' => $alert->id,
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Alerta atualizado com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        if (! Schema::hasTable('waf_alerts_config')) {
            return back()->with('error', 'Tabela nao disponivel.');
        }

        $alert = WafAlertConfig::findOrFail($id);
        $alert->delete();

        try {
            Log::channel('security')->info('WAF alerta removido', [
                'alert_id' => $id,
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Alerta removido com sucesso.');
    }
}
