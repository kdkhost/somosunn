<?php

namespace App\Http\Controllers\Admin\Waf;

use App\Http\Controllers\Controller;
use App\Models\Waf\WafSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WafSettingsController extends Controller
{
    public function index()
    {
        if (! Schema::hasTable('waf_settings')) {
            return view('admin.waf.settings', ['settings' => [], 'hasTable' => false]);
        }

        $settings = [
            'mode'              => WafSetting::getValue('waf.mode', 'detection-only'),
            'threshold_monitor' => WafSetting::getValue('waf.threshold.monitor', 20),
            'threshold_challenge' => WafSetting::getValue('waf.threshold.challenge', 50),
            'threshold_block'   => WafSetting::getValue('waf.threshold.block', 80),
            'fail_policy'       => WafSetting::getValue('waf.fail_policy', 'allow'),
            'exempt_routes'     => WafSetting::getValue('waf.exempt_routes', []),
        ];

        return view('admin.waf.settings', [
            'settings' => $settings,
            'hasTable' => true,
        ]);
    }

    public function update(Request $request)
    {
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

        $settingsMap = [
            'waf.mode'                => $request->input('mode'),
            'waf.threshold.monitor'   => (int) $request->input('threshold_monitor'),
            'waf.threshold.challenge' => (int) $request->input('threshold_challenge'),
            'waf.threshold.block'     => (int) $request->input('threshold_block'),
            'waf.fail_policy'         => $request->input('fail_policy'),
            'waf.exempt_routes'       => array_filter(
                array_map('trim', explode("\n", $request->input('exempt_routes', '')))
            ),
        ];

        foreach ($settingsMap as $key => $value) {
            WafSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value'      => $value,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]
            );
        }

        try {
            Log::channel('security')->info('WAF configuracoes atualizadas', [
                'settings' => $settingsMap,
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Configuracoes salvas com sucesso.');
    }
}
