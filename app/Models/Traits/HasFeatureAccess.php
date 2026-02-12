<?php

namespace App\Models\Traits;

use App\Models\Course;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

trait HasFeatureAccess
{
    use HasPackageAccess; // Inherit course specific logic

    /**
     * Check if user has access to a specific feature based on their Active Plan
     * or individual extra_features granted by admin.
     *
     * @param string $feature e.g. 'whatsapp', 'mentorships', 'events'
     * @return bool
     */
    public function canAccessFeature($feature)
    {
        $feature = trim((string) $feature);
        if ($feature === '') {
            return false;
        }

        // 1. Admin Bypass
        if ($this->isAdmin()) {
            return true;
        }

        // 2. Check individual extra_features (granted by admin/superadmin)
        $extraFeatures = $this->extra_features ?? [];
        if (is_array($extraFeatures)) {
            if (in_array($feature, $extraFeatures, true)) {
                return true;
            }

            foreach (Plan::aliasesForFeature($feature) as $alias) {
                if (in_array($alias, $extraFeatures, true)) {
                    return true;
                }
            }
        }

        // 3. Check Active Plan
        $plan = $this->activePlan();

        if (!$plan) {
            return false;
        }

        return method_exists($plan, 'hasFeature') ? $plan->hasFeature($feature) : false;
    }

    /**
     * Check if user has a specific feature via individual grants (ignoring plan).
     *
     * @param string $feature
     * @return bool
     */
    public function hasExtraFeature($feature)
    {
        $extraFeatures = $this->extra_features ?? [];
        if (!is_array($extraFeatures)) {
            return false;
        }

        $feature = trim((string) $feature);
        if ($feature === '') {
            return false;
        }

        if (in_array($feature, $extraFeatures, true)) {
            return true;
        }

        foreach (Plan::aliasesForFeature($feature) as $alias) {
            if (in_array($alias, $extraFeatures, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all features available to the user (plan + extra).
     *
     * @return array
     */
    public function allFeatures()
    {
        $features = [];

        // From plan
        $plan = $this->activePlan();
        if ($plan && is_array($plan->permissions ?? null)) {
            $features = array_merge($features, $plan->permissions);
        }

        // Extra individual features
        $extraFeatures = $this->extra_features ?? [];
        if (is_array($extraFeatures)) {
            $features = array_merge($features, $extraFeatures);
        }

        return array_unique($features);
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
                $plan = $this->plan;
                if ($plan) {
                    return $plan;
                }
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
