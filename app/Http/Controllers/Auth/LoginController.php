<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate(['email'=>'required|email','password'=>'required']);
        if(Auth::attempt($credentials, $request->filled('remember'))){
            $request->session()->regenerate();

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
            $defaultRoute = ($user && method_exists($user, 'isAdmin') && $user->isAdmin())
                ? route('panel.admin.dashboard')
                : route('panel.dashboard');

            return redirect()->intended($defaultRoute);
        }
        return back()->withErrors(['email' => 'Credenciais inválidas']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
