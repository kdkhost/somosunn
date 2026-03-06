<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Plan::query()
            ->where(function ($query) {
                $query->where('is_free', false)
                    ->orWhereNull('is_free');
            })
            ->where('price', '>', 0)
            ->chunkById(100, function ($plans): void {
                foreach ($plans as $plan) {
                    $permissions = is_array($plan->permissions) ? $plan->permissions : [];
                    $plan->permissions = Plan::normalizeCommercialPermissions(
                        $permissions,
                        (bool) $plan->is_free,
                        (float) $plan->price
                    );
                    $plan->save();
                }
            });
    }

    public function down(): void
    {
    }
};
