<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'platform_fee_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('platform_fee_enabled')->default(true)->after('pix_key');
            });
        }

        $marketingUserId = (int) Setting::get('platform_marketing_user_id', 0);

        DB::table('users')
            ->where(function ($query) use ($marketingUserId) {
                $query->where('role', 'admin')
                    ->orWhere('role', 'superadmin')
                    ->orWhere('level', 'superadmin');

                if ($marketingUserId > 0) {
                    $query->orWhere('id', $marketingUserId);
                }
            })
            ->update(['platform_fee_enabled' => false]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'platform_fee_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('platform_fee_enabled');
            });
        }
    }
};
