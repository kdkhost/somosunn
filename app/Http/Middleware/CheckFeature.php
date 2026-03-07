<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CheckFeature
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature
     */
    public function handle(Request $request, Closure $next, $feature)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->guest(route('login'));
        }

        $feature = trim((string) $feature);

        if (!$user->canAccessFeature($feature) && !$this->hasEntitlementOverride($request, $user, $feature)) {
            Log::warning('Acesso negado por feature', [
                'user_id' => $user->id,
                'feature' => $feature,
                'route' => optional($request->route())->getName(),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            $lockedRedirect = $this->redirectToLockedContent($request, $feature);
            if ($lockedRedirect) {
                return $lockedRedirect;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Funcionalidade nao incluida no seu plano.',
                    'required_feature' => $feature,
                ], 403);
            }

            return redirect()
                ->route('premium', ['feature' => $feature])
                ->with('warning', 'Seu plano atual nao inclui este recurso. Veja os planos recomendados para liberar o acesso.');
        }

        return $next($request);
    }

    private function redirectToLockedContent(Request $request, string $feature)
    {
        if ($request->expectsJson()) {
            return null;
        }

        $courseFeatures = [
            'courses_lessons_access',
            'courses_lessons_attachments_download',
        ];

        if (in_array($feature, $courseFeatures, true)) {
            $courseId = $this->resolveCourseId($request->route('course'));
            if ($courseId) {
                $course = Course::query()->find($courseId);
                if ($course) {
                    $routeParam = !empty($course->slug) ? $course->slug : $course->id;

                    return redirect()
                        ->route('courses.show', [
                            'course' => $routeParam,
                            'locked' => 1,
                        ])
                        ->with('access_blocked', [
                            'type' => 'course',
                            'course_id' => (int) $course->id,
                            'feature' => $feature,
                        ])
                        ->with('warning', 'Conteudo bloqueado. Escolha como deseja liberar o acesso.');
                }
            }
        }

        if ($feature === 'mentorships_access') {
            $mentorshipId = $this->resolveMentorshipId($request->route('mentorship'));
            if ($mentorshipId) {
                $mentorship = Mentorship::query()->find($mentorshipId);
                if ($mentorship) {
                    return redirect()
                        ->route('mentorships.show', [
                            'mentorship' => $mentorship,
                            'locked' => 1,
                        ])
                        ->with('access_blocked', [
                            'type' => 'mentorship',
                            'mentorship_id' => (int) $mentorship->id,
                            'feature' => $feature,
                        ])
                        ->with('warning', 'Conteudo bloqueado. Escolha como deseja liberar o acesso.');
                }
            }
        }

        return null;
    }

    private function hasEntitlementOverride(Request $request, $user, string $feature): bool
    {
        try {
            $courseAccessFeatures = [
                'courses_access',
                'courses_lessons_access',
                'courses_lessons_attachments_download',
            ];

            if (in_array($feature, $courseAccessFeatures, true)) {
                $courseParam = $request->route('course');

                // /courses (index) has no {course}; allow only if already purchased/enrolled in any course.
                if ($courseParam === null) {
                    return method_exists($user, 'hasPurchasedCourses') ? (bool) $user->hasPurchasedCourses() : false;
                }

                $courseId = $this->resolveCourseId($courseParam);
                if (!$courseId) {
                    return false;
                }

                return method_exists($user, 'hasCourseAccess') ? (bool) $user->hasCourseAccess($courseId) : false;
            }

            if ($feature === 'mentorships_access') {
                $mentorshipParam = $request->route('mentorship');
                if ($mentorshipParam === null) {
                    return false;
                }

                $mentorshipId = $this->resolveMentorshipId($mentorshipParam);
                if (!$mentorshipId) {
                    return false;
                }

                return method_exists($user, 'hasMentorshipAccess') ? (bool) $user->hasMentorshipAccess($mentorshipId) : false;
            }

            if ($feature === 'events_access') {
                $eventParam = $request->route('event');
                if ($eventParam === null) {
                    return false;
                }

                $eventId = $this->resolveEventId($eventParam);
                if (!$eventId) {
                    return false;
                }

                return method_exists($user, 'hasEventAccess') ? (bool) $user->hasEventAccess($eventId) : false;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function resolveCourseId($courseParam): ?int
    {
        if ($courseParam instanceof Course) {
            return (int) $courseParam->id;
        }

        $courseParam = trim((string) $courseParam);

        if ($courseParam === '') {
            return null;
        }

        if (ctype_digit($courseParam)) {
            return (int) $courseParam;
        }

        if (!Schema::hasColumn('courses', 'slug')) {
            return null;
        }

        $courseId = Course::query()->where('slug', $courseParam)->value('id');
        return $courseId ? (int) $courseId : null;
    }

    private function resolveMentorshipId($mentorshipParam): ?int
    {
        if ($mentorshipParam instanceof Mentorship) {
            return (int) $mentorshipParam->id;
        }

        $mentorshipParam = trim((string) $mentorshipParam);

        if ($mentorshipParam === '') {
            return null;
        }

        if (ctype_digit($mentorshipParam)) {
            return (int) $mentorshipParam;
        }

        return null;
    }

    private function resolveEventId($eventParam): ?int
    {
        if ($eventParam instanceof Event) {
            return (int) $eventParam->id;
        }

        $eventParam = trim((string) $eventParam);

        if ($eventParam === '') {
            return null;
        }

        if (ctype_digit($eventParam)) {
            return (int) $eventParam;
        }

        return null;
    }
}

