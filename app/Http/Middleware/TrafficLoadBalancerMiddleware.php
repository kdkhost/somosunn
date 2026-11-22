<?php

namespace App\Http\Middleware;

use App\Support\TrafficLoadDetector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrafficLoadBalancerMiddleware
{
    /**
     * Mede e ajusta o comportamento de tráfego de forma contínua e adaptativa
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Registra o hit atômico na janela de tráfego
        TrafficLoadDetector::recordHit();

        /** @var Response $response */
        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $policy = TrafficLoadDetector::getAdaptivePolicy();

        // Injeta headers de diagnóstico e escalabilidade na resposta HTTP
        $response->headers->set('X-UNN-Load-Level', $policy['level']);
        $response->headers->set('X-UNN-Response-Time', $durationMs . 'ms');

        return $response;
    }
}
