<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seller_stores')) {
            return;
        }

        if (!Schema::hasColumn('seller_stores', 'is_platform_store')) {
            Schema::table('seller_stores', function (Blueprint $table) {
                $table->boolean('is_platform_store')->default(false);
            });
        }

        if (!Schema::hasTable('users')) {
            return;
        }

        $superAdminIds = DB::table('users')
            ->where('role', 'superadmin')
            ->orWhere('level', 'superadmin')
            ->pluck('id');

        if ($superAdminIds->isNotEmpty()) {
            DB::table('seller_stores')
                ->whereIn('user_id', $superAdminIds->all())
                ->update(['is_platform_store' => true]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('seller_stores') || !Schema::hasColumn('seller_stores', 'is_platform_store')) {
            return;
        }

        Schema::table('seller_stores', function (Blueprint $table) {
            $table->dropColumn('is_platform_store');
        });
    }
};
