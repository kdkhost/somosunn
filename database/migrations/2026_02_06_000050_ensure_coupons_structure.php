<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coupons')) {
            return;
        }

        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'name')) {
                $table->string('name')->nullable()->after('code');
            }
            if (!Schema::hasColumn('coupons', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            // New schema fields (kept even if legacy columns exist)
            if (!Schema::hasColumn('coupons', 'discount_type')) {
                $table->string('discount_type', 20)->default('percent')->after('description'); // percent|fixed
            }
            if (!Schema::hasColumn('coupons', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('coupons', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('discount_value');
            }

            if (!Schema::hasColumn('coupons', 'applies_to')) {
                $table->string('applies_to', 20)->default('all')->after('is_active'); // all|event|course|mentorship|plan
            }
            if (!Schema::hasColumn('coupons', 'applies_to_id')) {
                $table->unsignedBigInteger('applies_to_id')->nullable()->after('applies_to');
            }

            if (!Schema::hasColumn('coupons', 'min_amount')) {
                $table->decimal('min_amount', 10, 2)->nullable()->after('applies_to_id');
            }

            if (!Schema::hasColumn('coupons', 'max_uses')) {
                $table->unsignedInteger('max_uses')->nullable()->after('min_amount');
            }
            if (!Schema::hasColumn('coupons', 'max_uses_per_user')) {
                $table->unsignedInteger('max_uses_per_user')->nullable()->after('max_uses');
            }

            if (!Schema::hasColumn('coupons', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('max_uses_per_user');
            }
            if (!Schema::hasColumn('coupons', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after('starts_at');
            }

            // Ensure timestamps exist (legacy tables usually have)
            if (!Schema::hasColumn('coupons', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('coupons', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // Backfill from legacy columns (if present)
        $hasLegacyType = Schema::hasColumn('coupons', 'type');
        $hasLegacyValue = Schema::hasColumn('coupons', 'value');
        $hasLegacyActive = Schema::hasColumn('coupons', 'active');
        $hasLegacyTargetType = Schema::hasColumn('coupons', 'target_type');
        $hasLegacyTargetId = Schema::hasColumn('coupons', 'target_id');

        if ($hasLegacyType) {
            DB::table('coupons')
                ->whereNull('discount_type')
                ->orWhere('discount_type', '')
                ->update(['discount_type' => DB::raw('`type`')]);
        }

        if ($hasLegacyValue) {
            DB::table('coupons')
                ->whereNull('discount_value')
                ->update(['discount_value' => DB::raw('`value`')]);
        }

        if ($hasLegacyActive) {
            DB::table('coupons')
                ->whereNull('is_active')
                ->update(['is_active' => DB::raw('`active`')]);
        }

        if ($hasLegacyTargetType) {
            // Map legacy "target_type" -> new "applies_to"
            // legacy: plan, course, event, mentorship, global
            // new: all, event, course, mentorship, plan
            DB::table('coupons')
                ->whereNull('applies_to')
                ->orWhere('applies_to', '')
                ->update([
                    'applies_to' => DB::raw("CASE
                        WHEN `target_type` IS NULL OR `target_type` = '' OR `target_type` = 'global' THEN 'all'
                        WHEN `target_type` = 'event' THEN 'event'
                        WHEN `target_type` = 'course' THEN 'course'
                        WHEN `target_type` = 'mentorship' THEN 'mentorship'
                        WHEN `target_type` = 'plan' THEN 'plan'
                        ELSE 'all'
                    END"),
                ]);
        }

        if ($hasLegacyTargetId) {
            DB::table('coupons')
                ->whereNull('applies_to_id')
                ->update(['applies_to_id' => DB::raw('`target_id`')]);
        }

        // Ensure defaults for required fields (avoid null issues in code)
        DB::table('coupons')
            ->whereNull('discount_type')
            ->orWhere('discount_type', '')
            ->update(['discount_type' => 'percent']);

        DB::table('coupons')
            ->whereNull('discount_value')
            ->update(['discount_value' => 0]);

        DB::table('coupons')
            ->whereNull('is_active')
            ->update(['is_active' => 1]);

        DB::table('coupons')
            ->whereNull('applies_to')
            ->orWhere('applies_to', '')
            ->update(['applies_to' => 'all']);

        // Helpful indexes (ignore if already exist)
        try {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index(['is_active', 'code'], 'coupons_active_code_idx');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('coupons', function (Blueprint $table) {
                $table->index(['applies_to', 'applies_to_id'], 'coupons_applies_idx');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('coupons', function (Blueprint $table) {
                $table->unique(['code'], 'coupons_code_unique');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        // Safety: keep schema (no-op)
    }
};
