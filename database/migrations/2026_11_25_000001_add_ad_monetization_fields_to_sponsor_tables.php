<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sponsor_plans')) {
            Schema::table('sponsor_plans', function (Blueprint $table) {
                if (!Schema::hasColumn('sponsor_plans', 'duration_days')) {
                    $table->unsignedSmallInteger('duration_days')->default(30)->after('price');
                }
                if (!Schema::hasColumn('sponsor_plans', 'description')) {
                    $table->text('description')->nullable()->after('duration_days');
                }
            });
        }

        if (Schema::hasTable('sponsor_banners')) {
            Schema::table('sponsor_banners', function (Blueprint $table) {
                if (!Schema::hasColumn('sponsor_banners', 'approval_status')) {
                    $table->string('approval_status', 20)->default('approved')->after('active');
                }
                if (!Schema::hasColumn('sponsor_banners', 'impressions_count')) {
                    $table->unsignedBigInteger('impressions_count')->default(0)->after('approval_status');
                }
                if (!Schema::hasColumn('sponsor_banners', 'clicks_count')) {
                    $table->unsignedBigInteger('clicks_count')->default(0)->after('impressions_count');
                }
                if (!Schema::hasColumn('sponsor_banners', 'sponsor_plan_id')) {
                    $table->unsignedBigInteger('sponsor_plan_id')->nullable()->after('sponsor_id');
                }
                if (!Schema::hasColumn('sponsor_banners', 'order_id')) {
                    $table->unsignedBigInteger('order_id')->nullable()->after('sponsor_plan_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sponsor_banners')) {
            Schema::table('sponsor_banners', function (Blueprint $table) {
                foreach (['order_id', 'sponsor_plan_id', 'clicks_count', 'impressions_count', 'approval_status'] as $col) {
                    if (Schema::hasColumn('sponsor_banners', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('sponsor_plans')) {
            Schema::table('sponsor_plans', function (Blueprint $table) {
                foreach (['description', 'duration_days'] as $col) {
                    if (Schema::hasColumn('sponsor_plans', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
