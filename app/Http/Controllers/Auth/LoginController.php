<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\SanitizesIntendedRedirect;
use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use SanitizesIntendedRedirect;

    public function authenticate(Request $request)
    {
        $credentials = $request->validate(['email'=>'required|email','password'=>'required']);

        $loginSuccessful = Auth::attempt($credentials, $request->filled('remember'));

        // Anomaly detection: registra a tentativa de login (sucesso/falha) por IP.
        // Spec: advanced-security-performance, Requirement 11.1
        try {
            app(\App\Services\AnomalyDetectorService::class)->recordLoginAttempt(
                (string) $request->ip(),
                (bool) $loginSuccessful
            );
        } catch (\Throwable $e) { /* swallow - nao bloqueia o fluxo */ }

        if ($loginSuccessful) {
            $request->session()->regenerate();

            // Audit log: login bem-sucedido
            try {
                app(AuditLogService::class)->log(AuditLogService::ACTION_LOGIN);
            } catch (\Throwable $e) { /* silent: audit nunca quebra login */ }

            // Log de segurança: login bem-sucedido
            try {
                \Illuminate\Support\Facades\Log::channel('security')->info('Login bem-sucedido', [
                    'user_id'    => Auth::id(),
                    'email'      => $credentials['email'],
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Throwable $e) {}

            // Gamificação: login diário + streaks
            try {
                $user = Auth::user();
                if ($user) {
                    $ps = new PointsService();

                    // award() enforce max_daily=1 automaticamente via regra de pontos
                    $awarded = $ps->award($user, 'daily_login', ['date' => now()->toDateString()]);

                    // Verifica e premia streak apenas quando recebeu login do dia
                    if ($awarded) {
                        $streak = $ps->calculateLoginStreak($user);

                        // streak_30days: premia a cada múltiplo de 30 dias consecutivos
                        if ($streak > 0 && $streak % 30 === 0) {
                            $ps->award($user, 'streak_30days', ['streak' => $streak, 'date' => now()->toDateString()]);
                        }

                        // streak_7days: premia a cada múltiplo de 7 dias consecutivos
                        if ($streak > 0 && $streak % 7 === 0) {
                            $ps->award($user, 'streak_7days', ['streak' => $streak, 'date' => now()->toDateString()]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Falha ao pontuar login diário: ' . $e->getMessage());
            }

            $user = Auth::user();

            // Superadmin → painel legado (/admin)
            // Admin e demais → painel novo (/painel/admin ou /painel)
            if ($user && $user->isSuperAdmin()) {
                $defaultRoute = route('admin.dashboard');
            } elseif ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
                $defaultRoute = route('panel.admin.dashboard');
            } else {
                $defaultRoute = route('panel.dashboard');
            }

            return $this->redirectToSafeIntended($request, $defaultRoute);
        }

        // Log de segurança: login falhado
        try {
            \Illuminate\Support\Facades\Log::channel('security')->warning('Login falhado', [
                'email'      => $credentials['email'],
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {}

        return back()->withErrors(['email' => 'Credenciais inválidas']);
    }

    public function logout(Request $request)
    {
        // Audit log: logout (antes de invalidar a sessão para preservar user_id)
        try {
            app(AuditLogService::class)->log(AuditLogService::ACTION_LOGOUT);
        } catch (\Throwable $e) { /* silent: audit nunca quebra logout */ }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
