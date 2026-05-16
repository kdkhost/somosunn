<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class LogUserActivity
{
    /**
     * Paths que NAO devem ser registrados em activity_logs apos
     * processados — evita ruido de housekeeping/heartbeat e impede
     * que o registro do "clear" suje a tabela imediatamente apos
     * o usuario solicitar a limpeza.
     */
    private const SKIP_PATHS = [
        'admin/activity-logs/clear',
        'painel/admin/logs/clear',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Apenas para usuarios autenticados e em metodos modificadores
        if (! Auth::check() || in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $response;
        }

        $path = ltrim($request->path(), '/');
        foreach (self::SKIP_PATHS as $skip) {
            if ($path === $skip || str_starts_with($path, $skip)) {
                return $response;
            }
        }

        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $this->getTranslatedAction($request->method(), $request->path()),
                'description' => $this->getTranslatedDescription($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => $request->except(['password', 'password_confirmation', '_token']),
            ]);
        } catch (\Throwable $e) {
            // Fail-safe: nunca quebrar a request original por falha no logger
        }

        return $response;
    }

    private function getTranslatedAction($method, $path)
    {
        $path = ltrim($path, '/');

        $mappings = [
            'login' => 'Login',
            'logout' => 'Logout',
            'admin/settings' => 'Configurações',
            'admin/users' => 'Usuários',
            'admin/courses' => 'Cursos',
            'admin/mentorships' => 'Mentorias',
            'admin/events' => 'Eventos',
            'admin/plans' => 'Planos',
            'admin/coupons' => 'Cupons',
            'admin/mailtemplates' => 'Templates de E-mail',
            'admin/activity-logs/clear' => 'Limpeza de Logs',
            'painel/admin/logs' => 'Logs de Atividade',
        ];

        $action = 'Ação';
        switch (strtoupper($method)) {
            case 'POST':
                $action = 'Criou/Enviou';
                break;
            case 'PUT':
            case 'PATCH':
                $action = 'Atualizou';
                break;
            case 'DELETE':
                $action = 'Removeu';
                break;
        }

        foreach ($mappings as $key => $translated) {
            if (str_starts_with($path, $key)) {
                return $action . ' ' . $translated;
            }
        }

        return $method . ' ' . $path;
    }

    private function getTranslatedDescription(Request $request)
    {
        $path = $request->path();

        if (str_contains($path, 'settings'))
            return 'Atualizou as configurações do sistema.';
        if (str_contains($path, 'login'))
            return 'Acessou a plataforma.';
        if (str_contains($path, 'logout'))
            return 'Saiu da plataforma.';
        if (str_contains($path, 'users'))
            return 'Gerenciou registro de usuário.';
        if (str_contains($path, 'courses'))
            return 'Gerenciou conteúdo de curso.';
        if (str_contains($path, 'mentorships'))
            return 'Gerenciou agendamento/mentoria.';
        if (str_contains($path, 'clear'))
            return 'Limpou o histórico de logs de atividade.';

        return 'O usuário realizou uma operação no sistema.';
    }
}
