<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona coluna para armazenar recursos extras liberados individualmente
     * pelo admin/superadmin, independente do plano do usuário.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('extra_features')->nullable()->after('plan_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('extra_features');
        });
    }
};
