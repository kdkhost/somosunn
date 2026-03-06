<?php

use App\Services\LegacyMemberPointsBackfillService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('points_rules') || !Schema::hasTable('points_logs')) {
            return;
        }

        app(LegacyMemberPointsBackfillService::class)->run();
    }

    public function down(): void
    {
        //
    }
};
