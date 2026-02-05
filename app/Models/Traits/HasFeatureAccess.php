<?php

namespace App\Models\Traits;

use App\Models\Course;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

trait HasFeatureAccess
{
    use HasPackageAccess; // Inherit course specific logic

    /**
     * Check if user has access to a specific feature based on their Active Plan.
     *
     * @param string $feature e.g. 'whatsapp', 'mentorships', 'events'
     * @return bool
     */
    public function canAccessFeature($feature)
    {
        // 1. Admin Bypass
        if ($this->isAdmin()) {
            return true;
        }

        // 2. Check Active Plan
        $plan = $this->activePlan();

        if (!$plan) {
            return false;
        }

        // 3. Check Plan Features (stored in 'permissions' json column)
        $features = $plan->permissions ?? [];

        // If features is null or not array, default to empty
        if (!is_array($features)) {
            $features = [];
        }

        // Check for specific feature or wildcards
        return in_array($feature, $features) || in_array('*', $features);
    }

    /**
     * Get the active plan for the user.
     * Checks manual plan_id first, then subscriptions table.
     *
     * @return Plan|null
     */
    public function activePlan()
    {
        // 1. Check manual assignment (plan_id on users table)
        if ($this->plan_id) {
            if (!$this->plan_expires_at || $this->plan_expires_at->isFuture()) {
                return $this->plan;
            }
        }

        // 2. Check subscriptions table
        $subscription = $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();

        return $subscription ? $subscription->plan : null;
    }
}
