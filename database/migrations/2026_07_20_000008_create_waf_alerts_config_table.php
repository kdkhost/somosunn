<?php

/**
 * Sistema UNN - Migration waf_alerts_config
 *
 * Configurações de canais de alerta do WAF (e-mail, webhook interno).
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 16.1, 16.2, 16.3, 16.4, 16.5
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('waf_alerts_config', function (Blueprint $table) {
            $table->bigIncrements('id');

            // email | webhook
            $table->string('channel', 20);
            $table->string('target', 255);

            // block_spike | auto_block | critical_finding | ip_reputation
            $table->string('trigger', 40);

            // limiares específicos do gatilho:
            //   block_spike: { events_per_window: 50, window_minutes: 5 }
            //   auto_block:  {}
            //   critical_finding: {}
            $table->json('threshold')->nullable();

            $table->timestamp('silence_until')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['trigger', 'is_active'], 'idx_waf_alerts_trigger_active');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waf_alerts_config');
    }
};
