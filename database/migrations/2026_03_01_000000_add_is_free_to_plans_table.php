<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_free')->default(false)->after('is_active')
                ->comment('Plano gratuito padrão — atribuído automaticamente a novos usuários');
        });

        // Marca o plano com slug 'cliente' como is_free (se existir)
        \DB::table('plans')->where('slug', 'cliente')->update(['is_free' => true]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('is_free');
        });
    }
};
