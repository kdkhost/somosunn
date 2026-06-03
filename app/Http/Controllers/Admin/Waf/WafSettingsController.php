<?php

namespace App\Http\Controllers\Admin\Waf;

use App\Http\Controllers\Controller;
use App\Models\Waf\WafSetting;
use App\Services\Waf\WafSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WafSettingsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $view = $request->routeIs('panel.admin.security*')
            ? 'panel.admin.waf.settings'
            : 'admin.waf.settings';

        if (! Schema::hasTable('waf_settings')) {
            return view($view, ['settings' => [], 'hasTable' => false]);
        }

        $active = WafSettings::load();
        $settings = [
            'mode' => $active->mode,
            'threshold_monitor' => $active->thresholdMonitor,
            'threshold_challenge' => $active->thresholdChallenge,
            'threshold_block' => $active->thresholdBlock,
            'fail_policy' => $active->isFailOpen() ? 'allow' : 'block',
            'exempt_routes' => $active->exemptRoutes,
        ];

        return view($view, [
            'settings' => $settings,
            'hasTable' => true,
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        if (! Schema::hasTable('waf_settings')) {
            return back()->with('error', 'Tabela waf_settings nao disponivel.');
        }

        $request->validate([
            'mode'                => 'required|in:detection-only,enforce',
            'threshold_monitor'   => 'required|integer|min:0|max:100',
            'threshold_challenge' => 'required|integer|min:0|max:100',
            'threshold_block'     => 'required|integer|min:0|max:100',
            'fail_policy'         => 'required|in:allow,block',
            'exempt_routes'       => 'nullable|string',
        ]);

        $thresholds = [
            'monitor' => (int) $request->input('threshold_monitor'),
            'challenge' => (int) $request->input('threshold_challenge'),
            'block' => (int) $request->input('threshold_block'),
        ];

        if (!($thresholds['monitor'] <= $thresholds['challenge'] && $thresholds['challenge'] <= $thresholds['block'])) {
            return back()
                ->withErrors(['threshold_monitor' => 'Os limiares devem seguir a ordem Monitor <= Challenge <= Block.'])
                ->withInput();
        }

        $settingsMap = [
            'waf.mode' => $request->input('mode'),
            'waf.thresholds' => $thresholds,
            'waf.fail_policy' => $request->input('fail_policy') === 'block' ? 'closed' : 'open',
            'waf.exempt_routes' => array_values(array_filter(
                array_map('trim', explode("\n", $request->input('exempt_routes', '')))
            )),
        ];

        try {
            DB::transaction(function () use ($settingsMap) {
                foreach ($settingsMap as $key => $value) {
                    WafSetting::updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $value,
                            'updated_by' => auth()->id(),
                            'updated_at' => now(),
                        ]
                    );
                }

                foreach ($settingsMap as $key => $expected) {
                    $saved = WafSetting::query()->find($key);
                    if (!$saved || $saved->value !== $expected) {
                        throw new \RuntimeException("A configuracao {$key} nao foi confirmada no banco.");
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Falha ao persistir configuracoes do WAF', [
                'actor_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'As configuracoes nao foram gravadas no banco: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->with('error', 'As configuracoes nao foram gravadas no banco. Nenhuma alteracao foi aplicada.')
                ->withInput();
        }

        try {
            Log::channel('security')->info('WAF configuracoes atualizadas', [
                'settings' => $settingsMap,
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Configuracoes gravadas e confirmadas no banco com sucesso.');
    }
}
