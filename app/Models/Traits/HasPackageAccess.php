<?php

namespace App\Models\Traits;

use App\Models\Course;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Enrollment;
use App\Models\Mentorship;
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

    /**
     * Check if the user has access to a specific mentorship.
     *
     * Access is granted if:
     * 1. User is Admin/Superadmin
     * 2. User is the mentor/owner of the mentorship
     * 3. User has an active Enrollment for the mentorship
     * 4. User has a completed Order for the mentorship
     *
     * @param int|Mentorship $mentorship
     * @return bool
     */
    public function hasMentorshipAccess($mentorship): bool
    {
        $mentorshipId = $mentorship instanceof Mentorship ? $mentorship->id : $mentorship;

        if ($this->isAdmin()) {
            return true;
        }

        if ($mentorship instanceof Mentorship && (int) $mentorship->mentor_id === (int) $this->id) {
            return true;
        }

        $hasEnrollment = $this->enrollments()
            ->where('enrollable_type', Mentorship::class)
            ->where('enrollable_id', $mentorshipId)
            ->where('status', 'active')
            ->exists();

        if ($hasEnrollment) {
            return true;
        }

        return $this->orders()
            ->where('status', 'paid')
            ->whereHas('items', function ($q) use ($mentorshipId) {
                $q->where('item_type', 'mentorship')
                    ->where('item_id', $mentorshipId);
            })
            ->exists();
    }

    /**
     * Check if the user has access to a specific event.
     *
     * Access is granted if:
     * 1. User is Admin/Superadmin
     * 2. User is the owner/creator of the event
     * 3. User has a paid/confirmed EventRegistration for the event
     * 4. User has a completed Order for the event
     *
     * @param int|Event $event
     * @return bool
     */
    public function hasEventAccess($event): bool
    {
        $eventId = $event instanceof Event ? $event->id : $event;

        if ($this->isAdmin()) {
            return true;
        }

        if ($event instanceof Event && (int) $event->user_id === (int) $this->id) {
            return true;
        }

        $hasRegistration = EventRegistration::query()
            ->where('user_id', (int) $this->id)
            ->where('event_id', (int) $eventId)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->exists();

        if ($hasRegistration) {
            return true;
        }

        return $this->orders()
            ->where('status', 'paid')
            ->whereHas('items', function ($q) use ($eventId) {
                $q->where('item_type', 'event')
                    ->where('item_id', $eventId);
            })
            ->exists();
    }
    
    // Helper to call from User model if needed
    public function orders() {
        return $this->hasMany(Order::class);
    }
}
