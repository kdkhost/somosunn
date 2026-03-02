<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            // Origem: 'manual' (admin criou) ou 'google' (importado do Google Meu Negócio)
            $table->string('source', 20)->default('manual')->after('content');

            // ID externo do Google (para evitar duplicatas na importação)
            $table->string('external_id')->nullable()->after('source');

            // URL do avatar exibida no frontend (usada para reviews do Google)
            $table->string('avatar_url')->nullable()->after('external_id');

            // Controle de visibilidade independente de moderação
            $table->boolean('is_active')->default(true)->after('avatar_url');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['source', 'external_id', 'avatar_url', 'is_active']);
        });
    }
};
