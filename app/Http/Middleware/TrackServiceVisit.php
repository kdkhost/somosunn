<?php

namespace App\Http\Middleware;

use App\Events\ServiceVisitRegistered;
use App\Models\ServiceVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TrackServiceVisit
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!$this->shouldTrack($request, $response)) {
            return $response;
        }

        $hasTable = false;
        try {
            $hasTable = Cache::remember('unn:schema:has_service_visits', 3600, static function (): bool {
                return Schema::hasTable('service_visits');
            });
        } catch (\Throwable) {
            $hasTable = Schema::hasTable('service_visits');
        }

        if (!$hasTable) {
            return $response;
        }

        [$serviceType, $serviceId] = $this->resolveServiceContext($request);

        if (!$serviceType) {
            return $response;
        }

        $userId = Auth::id();
        $visitedAt = now();

        // Executa o registro apos o envio da resposta para nao travar o TTFB
        app()->terminating(static function () use ($serviceType, $serviceId, $userId, $visitedAt): void {
            try {
                ServiceVisit::create([
                    'service_type' => $serviceType,
                    'service_id' => $serviceId,
                    'user_id' => $userId,
                    'visited_at' => $visitedAt,
                ]);

                $count = Cache::remember(
                    'unn:service_visit_count:' . $serviceType . ':' . ($serviceId ?? 'all'),
                    60,
                    static fn () => ServiceVisit::query()
                        ->where('service_type', $serviceType)
                        ->when($serviceId !== null, fn ($query) => $query->where('service_id', $serviceId))
                        ->count()
                );

                event(new ServiceVisitRegistered($serviceType, $serviceId, (int) $count));
            } catch (\Throwable $exception) {
                Log::error('TrackServiceVisit error: ' . $exception->getMessage());
            }
        });

        return $response;
    }

    private function shouldTrack(Request $request, mixed $response): bool
    {
        if ((bool) $request->attributes->get('unn.stateless_public', false)) {
            return false;
        }

        if (!$request->isMethod('GET') || $request->expectsJson() || $request->ajax()) {
            return false;
        }

        if ($request->is('admin*') || $request->is('painel*') || $request->is('api/*')) {
            return false;
        }

        if ($request->routeIs('admin.*') || $request->routeIs('panel.*')) {
            return false;
        }

        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 400) {
            return false;
        }

        return true;
    }

    private function resolveServiceContext(Request $request): array
    {
        $route = $request->route();

        if (!$route) {
            return $request->path() === '/' ? ['site', null] : [null, null];
        }

        $name = (string) $route->getName();

        if ($name !== '' && str_starts_with($name, 'courses.')) {
            return ['curso', $this->resolveId($route->parameter('course'), \App\Models\Course::class)];
        }

        if ($name !== '' && str_starts_with($name, 'events.')) {
            return ['evento', $this->resolveId($route->parameter('event'), \App\Models\Event::class)];
        }

        if ($name !== '' && str_starts_with($name, 'mentorships.')) {
            return ['mentoria', $this->resolveId($route->parameter('mentorship'), \App\Models\Mentorship::class)];
        }

        if ($name !== '' && str_starts_with($name, 'talks.')) {
            return ['palestra', $this->resolveId($route->parameter('talk'), null)];
        }

        return ['site', null];
    }

    private function resolveId(mixed $parameter, ?string $modelClass): ?int
    {
        if (is_object($parameter)) {
            return isset($parameter->id) ? (int) $parameter->id : null;
        }

        if ($parameter === null || $parameter === '') {
            return null;
        }

        if (is_numeric($parameter)) {
            return (int) $parameter;
        }

        if ($modelClass && class_exists($modelClass)) {
            try {
                $found = $modelClass::query()->where('slug', $parameter)->first();

                return $found?->id ? (int) $found->id : null;
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
