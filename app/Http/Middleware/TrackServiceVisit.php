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
                $serviceId = $route->parameter('course') ?? null;
            } elseif (str_starts_with($name, 'events.')) {
                $serviceType = 'evento';
                $serviceId = $route->parameter('event') ?? null;
            } elseif (str_starts_with($name, 'mentorships.')) {
                $serviceType = 'mentoria';
                $serviceId = $route->parameter('mentorship') ?? null;
            } elseif (str_starts_with($name, 'talks.')) {
                $serviceType = 'palestra';
                $serviceId = $route->parameter('talk') ?? null;
            } elseif ($request->is('/')) {
                $serviceType = 'site';
            }
        }
        if ($serviceType) {
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
        }
        return $response;
    }
}
