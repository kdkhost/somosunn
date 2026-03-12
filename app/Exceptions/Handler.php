<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Lista de exceções que não devem ser reportadas.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Campos sensíveis que não devem ser armazenados em sessões de erro.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // Registro adicional de callbacks pode ser feito aqui
    }

    public function render($request, Throwable $exception): Response
    {
        if (
            $this->isHttpException($exception)
            && method_exists($exception, 'getStatusCode')
            && (int) $exception->getStatusCode() === 403
            && !$request->expectsJson()
            && $request->user()
            && !$request->user()->isAdmin()
            && $this->shouldSuggestPlanUpgrade($request)
        ) {
            $feature = $this->resolveFeatureFromRequest($request);

            return redirect()
                ->route('planos', array_filter([
                    'feature' => $feature,
                ]))
                ->with('warning', 'Seu plano atual nao inclui este recurso. Veja os planos recomendados para liberar o acesso.');
        }

        return parent::render($request, $exception);
    }

    private function shouldSuggestPlanUpgrade(Request $request): bool
    {
        if ($request->routeIs('planos') || $request->routeIs('subscription.*')) {
            return false;
        }

        if (
            $request->routeIs('admin.*')
            || $request->routeIs('panel.admin.*')
            || $request->is('admin/*')
            || $request->is('painel/admin/*')
        ) {
            return false;
        }

        return true;
    }

    private function resolveFeatureFromRequest(Request $request): ?string
    {
        $route = $request->route();

        if ($route && method_exists($route, 'gatherMiddleware')) {
            foreach ((array) $route->gatherMiddleware() as $middleware) {
                if (!is_string($middleware)) {
                    continue;
                }

                if (Str::startsWith($middleware, 'check.feature:')) {
                    $feature = trim((string) Str::after($middleware, 'check.feature:'));
                    if ($feature !== '') {
                        return $feature;
                    }
                }
            }
        }

        $routeName = (string) optional($route)->getName();
        if ($routeName !== '') {
            if (Str::startsWith($routeName, 'courses.')) {
                return 'courses_access';
            }
            if (Str::startsWith($routeName, 'mentorships.')) {
                return 'mentorships_access';
            }
            if (Str::startsWith($routeName, 'events.')) {
                return 'events_access';
            }
            if (Str::startsWith($routeName, 'chat.')) {
                return 'chat';
            }
            if (Str::startsWith($routeName, 'social.')) {
                return 'community';
            }
            if (Str::startsWith($routeName, 'marketplace.')) {
                return 'marketplace.buy';
            }
        }

        $path = trim((string) $request->path(), '/');
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, 'courses/')) {
            return 'courses_access';
        }
        if (Str::startsWith($path, 'mentorships/')) {
            return 'mentorships_access';
        }
        if (Str::startsWith($path, 'eventos/') || Str::startsWith($path, 'events/')) {
            return 'events_access';
        }
        if (Str::startsWith($path, 'chat')) {
            return 'chat';
        }
        if (Str::startsWith($path, 'feed') || Str::startsWith($path, 'profile/')) {
            return 'community';
        }
        if (Str::startsWith($path, 'marketplace')) {
            return 'marketplace.buy';
        }

        return null;
    }
}
