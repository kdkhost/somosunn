<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define default features for types of plans
        // Adjust names based on what exists in DB. 
        // Assuming we have 'Iniciante', 'VIP', 'Mentor', etc. based on previous context.

        $basicFeatures = ['courses', 'events', 'community', 'social.feed'];
        $premiumFeatures = ['courses', 'events', 'community', 'social.feed', 'mentorships', 'chat'];

        $plans = Plan::all();

        foreach ($plans as $plan) {
            $features = [];
            $name = strtolower($plan->name);

            if (str_contains($name, 'vip') || str_contains($name, 'pro') || str_contains($name, 'mentor') || str_contains($name, 'empresário')) {
                $features = $premiumFeatures;
            } else {
                // Default / Free / Basic
                $features = $basicFeatures;
            }

            // Preserve existing permissions if they differ from default empty array logic
            // But here we want to enforce the new system.
            $plan->permissions = Plan::normalizeCommercialPermissions(
                $features,
                (bool) ($plan->is_free ?? false),
                (float) ($plan->price ?? 0)
            );
            $plan->save();

            $this->command->info("Updated Plan: {$plan->name} with features: " . implode(', ', $features));
        }

        if ($plans->isEmpty()) {
            $this->command->warn("No plans found to update.");
        }
    }
}
