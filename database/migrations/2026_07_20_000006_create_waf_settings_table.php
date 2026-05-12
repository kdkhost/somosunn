<?php

/**
 * Sistema UNN - Migration waf_settings
 *
 * Tabela key/value para configurações operacionais do WAF que podem
 * ser alteradas pelo painel do superadmin em tempo real.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 15.6, 22.1
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('waf_settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->json('value');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        // Seeds iniciais alinhados com config/waf.php
        $now = now();
        DB::table('waf_settings')->insert([
            [
                'key'        => 'waf.enabled',
                'value'      => json_encode(false),
                'updated_at' => $now,
            ],
            [
                'key'        => 'waf.mode',
                'value'      => json_encode('detection-only'),
                'updated_at' => $now,
            ],
            [
                'key'        => 'waf.fail_policy',
                'value'      => json_encode('open'),
                'updated_at' => $now,
            ],
            [
                'key'        => 'waf.thresholds',
                'value'      => json_encode([
                    'monitor'   => 20,
                    'challenge' => 50,
                    'block'     => 80,
                ]),
                'updated_at' => $now,
            ],
            [
                'key'        => 'waf.retention',
                'value'      => json_encode([
                    'allowed'    => 7,
                    'monitored'  => 30,
                    'challenged' => 90,
                    'blocked'    => 180,
                ]),
                'updated_at' => $now,
            ],
            [
                'key'        => 'waf.exempt_routes',
                'value'      => json_encode([
                    '#^/healthz$#',
                    '#^/health$#',
                    '#^/_debugbar(/|$)#',
                    '#^/livewire(/|$)#',
                ]),
                'updated_at' => $now,
            ],
            [
                'key'        => 'waf.rate_limits',
                'value'      => json_encode([
                    'default' => ['limit' => 300, 'window' => 60],
                    'login'   => ['limit' => 10,  'window' => 60],
                    'api'     => ['limit' => 120, 'window' => 60],
                    'webhook' => ['limit' => 600, 'window' => 60],
                ]),
                'updated_at' => $now,
            ],
            [
                'key'        => 'waf.auto_block',
                'value'      => json_encode([
                    'enabled'         => false,
                    'window_minutes'  => 15,
                    'threshold'       => 100,
                    'duration_hours'  => 24,
                ]),
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('waf_settings');
    }
};
