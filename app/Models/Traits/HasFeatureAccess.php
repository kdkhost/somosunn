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
     * Checks plan_id and expiration date.
     *
     * @return Plan|null
     */
    public function activePlan()
    {
        if (!$this->plan_id) {
            return null;
        }

        // If plan_expires_at is null, assume lifetime or indefinite? 
        // Let's assume strict expiration if set.
        if ($this->plan_expires_at && $this->plan_expires_at->isPast()) {
            return null;
        }

        return $this->plan; // Relationship defined in User model
    }
}
