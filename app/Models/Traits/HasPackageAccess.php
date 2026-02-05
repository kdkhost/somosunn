<?php

namespace App\Models\Traits;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;

trait HasPackageAccess
{
    /**
     * Check if the user has access to a specific course.
     *
     * Access is granted if:
     * 1. User is Admin/Superadmin (Global access)
     * 2. User is the Creator of the course
     * 3. User has an active Enrollment for the course
     * 4. User has a completed Order for the course
     * 
     * @param int|Course $course
     * @return bool
     */
    public function hasCourseAccess($course)
    {
        $courseId = $course instanceof Course ? $course->id : $course;
        
        // 1. Admin/Superadmin bypass (Global Access)
        if ($this->isAdmin()) {
            return true;
        }

        // 2. Creator bypass
        // Assuming courses()->where('id', $courseId)->exists() covers this if 'courses' relation is courses created by user.
        // But for safety, let's check the 'created_by' or 'user_id' on course if instance is passed.
        if ($course instanceof Course && $course->user_id === $this->id) {
            return true;
        }

        // 3. Active Enrollment check
        // Check if there is an enrollment for this course
        $hasEnrollment = $this->enrollments()
            ->where('enrollable_type', Course::class)
            ->where('enrollable_id', $courseId)
            ->where('status', 'active') // Assuming 'active' status exists, need to verify
            ->exists();

        if ($hasEnrollment) {
            return true;
        }

        // 4. Order check (Backup if enrollment hasn't been created yet but paid)
        $hasOrder = $this->orders() // Assuming 'orders' relation exists on User
            ->where('status', 'paid') // or 'completed'
            ->whereHas('items', function($q) use ($courseId) {
                $q->where('item_type', 'course')
                  ->where('item_id', $courseId);
            })
            ->exists();

        if ($hasOrder) {
            return true;
        }
        
        // TODO: Plan/Package checks if Plans grant access to implicit courses.
        // Currently assuming explicit course purchase or enrollment.

        return false;
    }
    
    // Helper to call from User model if needed
    public function orders() {
        return $this->hasMany(Order::class);
    }
}
