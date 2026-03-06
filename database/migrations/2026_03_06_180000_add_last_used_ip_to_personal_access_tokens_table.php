<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('personal_access_tokens') || Schema::hasColumn('personal_access_tokens', 'last_used_ip')) {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('last_used_ip', 45)->nullable()->after('last_used_at');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('personal_access_tokens') || !Schema::hasColumn('personal_access_tokens', 'last_used_ip')) {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('last_used_ip');
        });
    }
};
