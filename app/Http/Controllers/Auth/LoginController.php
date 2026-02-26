<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PointsLog;
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

            // Gamificação: pontuar login diário (no máximo 1x por dia)
            try {
                $user = Auth::user();
                if ($user) {
                    $alreadyAwarded = PointsLog::query()
                        ->where('user_id', $user->id)
                        ->where('action_key', 'daily_login')
                        ->whereDate('created_at', now()->toDateString())
                        ->exists();

                    if (!$alreadyAwarded) {
                        (new PointsService())->award($user, 'daily_login', ['date' => now()->toDateString()]);
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
