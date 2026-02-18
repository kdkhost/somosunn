<?php
// UTF-8 sem BOM
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ServiceVisit;
use Illuminate\Support\Facades\Auth;

class TrackServiceVisit
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // Identifica tipo de serviço pela rota
        $route = $request->route();
        $user = Auth::user();
        $serviceType = null;
        $serviceId = null;

        if ($route) {
            $name = $route->getName();
            if (str_starts_with($name, 'courses.')) {
                $serviceType = 'curso';
                $serviceId = $this->resolveId($route->parameter('course'), \App\Models\Course::class);
            } elseif (str_starts_with($name, 'events.')) {
                $serviceType = 'evento';
                $serviceId = $this->resolveId($route->parameter('event'), \App\Models\Event::class);
            } elseif (str_starts_with($name, 'mentorships.')) {
                $serviceType = 'mentoria';
                $serviceId = $this->resolveId($route->parameter('mentorship'), \App\Models\Mentorship::class);
            } elseif (str_starts_with($name, 'talks.')) {
                $serviceType = 'palestra';
                $serviceId = $this->resolveId($route->parameter('talk'), null);
            } elseif ($request->is('/')) {
                $serviceType = 'site';
            }
        }

        if ($serviceType) {
            try {
                ServiceVisit::create([
                    'service_type' => $serviceType,
                    'service_id' => $serviceId,
                    'user_id' => $user ? $user->id : null,
                    'visited_at' => now(),
                ]);
                // Contagem atualizada
                $count = ServiceVisit::where('service_type', $serviceType)
                    ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
                    ->count();
                event(new \App\Events\ServiceVisitRegistered($serviceType, $serviceId, $count));
            } catch (\Exception $e) {
                \Log::error("TrackServiceVisit error: " . $e->getMessage());
            }
        }
        return $response;
    }

    /**
     * Resolve ID from route parameter (Object, Numeric String or Slug)
     */
    private function resolveId($p, $modelClass)
    {
        if (is_object($p)) {
            return $p->id ?? null;
        }

        if (!$p) {
            return null;
        }

        if (is_numeric($p)) {
            return (int) $p;
        }

        // Se for string (slug), tenta resolver se houver modelClass
        if ($modelClass && class_exists($modelClass)) {
            try {
                $found = $modelClass::where('slug', $p)->first();
                return $found ? $found->id : null;
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }
}
