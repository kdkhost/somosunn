<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FEATURES = [
        'community',
        'chat',
        'courses',
        'events',
        'mentorships',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('plans') || !Schema::hasColumn('plans', 'permissions')) {
            return;
        }

        $hasLegacyPivot = Schema::hasTable('permission_plan') && Schema::hasTable('permissions');

        $plans = DB::table('plans')->select(['id', 'permissions'])->get();

        foreach ($plans as $plan) {
            $features = $this->normalizeFeatures($plan->permissions);

            if ($hasLegacyPivot) {
                $pivotPerms = DB::table('permission_plan')
                    ->join('permissions', 'permissions.id', '=', 'permission_plan.permission_id')
                    ->where('permission_plan.plan_id', $plan->id)
                    ->pluck('permissions.name')
                    ->all();

                $features = array_merge($features, $this->normalizeFeatures($pivotPerms));
            }

            $features = array_values(array_unique(array_values(array_filter($features, fn ($v) => in_array($v, self::FEATURES, true)))));

            DB::table('plans')
                ->where('id', $plan->id)
                ->update(['permissions' => json_encode($features)]);
        }
    }

    public function down(): void
    {
        // no-op (safety)
    }

    private function normalizeFeatures($raw): array
    {
        $values = [];

        if (is_array($raw)) {
            $values = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $values = $decoded;
            }
        }

        $out = [];

        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ($value === '') {
                continue;
            }

            // Already a feature key
            if (in_array($value, self::FEATURES, true)) {
                $out[] = $value;
                continue;
            }

            // Legacy admin-like perms -> feature
            if (str_starts_with($value, 'courses.')) {
                $out[] = 'courses';
                continue;
            }
            if (str_starts_with($value, 'events.')) {
                $out[] = 'events';
                continue;
            }
            if (str_starts_with($value, 'mentorships.')) {
                $out[] = 'mentorships';
                continue;
            }
        }

        return $out;
    }
};

