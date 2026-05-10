<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'scanner_early_minutes')) {
                // Tempo em minutos antes do evento que o scanner já abre
                // Ex: 120 = scanner abre 2h antes do horário do evento
                $table->integer('scanner_early_minutes')->nullable()->default(0)->after('scanner_radius_meters');
            }

            if (!Schema::hasColumn('events', 'scanner_late_minutes')) {
                // Tempo em minutos após o fim do evento que o scanner ainda aceita
                // Ex: 60 = scanner aceita até 1h após o end_at (ou 23:59 se sem end_at)
                $table->integer('scanner_late_minutes')->nullable()->default(0)->after('scanner_early_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'scanner_late_minutes')) {
                $table->dropColumn('scanner_late_minutes');
            }
            if (Schema::hasColumn('events', 'scanner_early_minutes')) {
                $table->dropColumn('scanner_early_minutes');
            }
        });
    }
};
