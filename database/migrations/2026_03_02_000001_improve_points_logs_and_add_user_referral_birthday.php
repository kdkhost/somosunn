<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expande coluna meta de varchar(255) para text para evitar truncamento de JSON
        Schema::table('points_logs', function (Blueprint $table) {
            $table->text('meta')->nullable()->change();
        });

        // 2. Adiciona birth_date para o birthday_bonus e referral system
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('gender');
            }

            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 20)->nullable()->unique()->after('birth_date');
            }

            if (!Schema::hasColumn('users', 'referred_by')) {
                $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
                $table->foreign('referred_by')->references('id')->on('users')->nullOnDelete();
            }
        });

        // 3. Gera código de referência para usuários já existentes
        DB::statement("
            UPDATE users
            SET referral_code = CONCAT('UNN', LPAD(id, 7, '0'))
            WHERE referral_code IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referred_by')) {
                $table->dropForeign(['referred_by']);
                $table->dropColumn('referred_by');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
            if (Schema::hasColumn('users', 'birth_date')) {
                $table->dropColumn('birth_date');
            }
        });

        Schema::table('points_logs', function (Blueprint $table) {
            $table->string('meta')->nullable()->change();
        });
    }
};
