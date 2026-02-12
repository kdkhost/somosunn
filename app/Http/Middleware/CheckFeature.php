<?php

namespace App\Http\Middleware;

use App\Models\Course;
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
                    return method_exists($user, 'hasPurchasedCourses') ? (bool) $user->hasPurchasedCourses() : false;
                }

                $courseId = $this->resolveCourseId($courseParam);
                if (!$courseId) {
                    return false;
                }

                return method_exists($user, 'hasCourseAccess') ? (bool) $user->hasCourseAccess($courseId) : false;
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
}
