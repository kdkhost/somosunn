<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_coupons') || Schema::hasColumn('event_coupons', 'max_uses_per_user')) {
            return;
        }

        Schema::table('event_coupons', function (Blueprint $table) {
            $table->unsignedInteger('max_uses_per_user')
                ->nullable()
                ->after('max_uses');
        });
    }

    public function down(): void
    {
        // Rollback conservador: o limite pode estar vinculado a cupons em uso.
    }
};
