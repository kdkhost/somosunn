<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CheckFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $feature)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->canAccessFeature($feature) && !$this->hasEntitlementOverride($request, $user, (string) $feature)) {
            // Se for AJAX, retorna JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Funcionalidade não incluída no seu plano.'], 403);
            }

            // Se for normal, redireciona com mensagem ou aborta
            // Redirecionar para 'upgrade' ou 'planos' seria ideal
            return redirect()->route('portal')->with('error', 'Esta funcionalidade não está disponível no seu plano atual. Faça um upgrade!');
        }

        return $next($request);
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

                // /courses (index) não tem {course}; libera somente se já comprou/enrolou em algum curso.
                if ($courseParam === null) {
                    return false;
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
