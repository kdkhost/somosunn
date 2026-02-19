<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated users and non-GET requests (usually actions)
        if (Auth::check() && !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            try {
                $description = $this->resolveDescription($request);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => $request->method() . ' ' . $request->path(),
                    'description' => $description,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'properties' => $request->all(), // Be careful with sensitive data!
                ]);
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        return $response;
    }

    private function resolveDescription(Request $request)
    {
        $path = $request->path();
        $method = $request->method();

        // Mapeamento de rotas para descrições legíveis
        $maps = [
            'login' => 'Realizou login',
            'logout' => 'Realizou logout',
            'admin/users' => [
                'POST' => 'Criou novo usuário',
                'PUT' => 'Atualizou usuário',
                'DELETE' => 'Removeu usuário',
            ],
            'admin/settings' => [
                'POST' => 'Atualizou configurações do sistema',
            ],
            'admin/courses' => [
                'POST' => 'Criou novo curso',
                'PUT' => 'Atualizou curso',
                'DELETE' => 'Removeu curso',
            ],
            'admin/mentorships' => [
                'POST' => 'Criou nova mentoria',
                'PUT' => 'Atualizou mentoria',
                'DELETE' => 'Removeu mentoria',
            ],
            'admin/events' => [
                'POST' => 'Criou novo evento',
                'PUT' => 'Atualizou evento',
                'DELETE' => 'Removeu evento',
            ],
            'admin/cron' => [
                'POST' => 'Criou tarefa agendada',
                'PUT' => 'Atualizou tarefa agendada',
                'DELETE' => 'Removeu tarefa agendada',
            ],
            'admin/mailtemplates' => [
                'POST' => 'Criou modelo de e-mail',
                'PUT' => 'Atualizou modelo de e-mail',
                'DELETE' => 'Removeu modelo de e-mail',
            ],
            'admin/notifications' => [
                'DELETE' => 'Removeu notificação',
            ],
        ];

        foreach ($maps as $key => $value) {
            if (str_contains($path, $key)) {
                if (is_array($value)) {
                    return $value[$method] ?? 'Realizou ação em ' . $key;
                }
                return $value;
            }
        }

        // Fallback genérico traduzido
        return match ($method) {
            'POST' => 'Criou ou Salvou registro',
            'PUT', 'PATCH' => 'Atualizou registro',
            'DELETE' => 'Removeu registro',
            default => 'Realizou uma ação',
        };
    }
}
