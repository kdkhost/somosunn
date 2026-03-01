<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Faça login para acessar o painel administrativo.');
        }

        $user = auth()->user();
        $role = $user->role ?? 'member';
        $level = $user->level ?? null;

        // Flags de permissão
        $isSuper = in_array($role, ['superadmin']) || in_array($level, ['superadmin', 'sucesso'], true);
        $isAdmin = $isSuper; // Somente superadmin é admin do painel antigo
        $isMember = !$isAdmin; // Todos os demais são membros

        // Compartilha variáveis globais com todas as views do admin
        view()->share('isSuper', $isSuper);
        view()->share('isAdmin', $isAdmin);
        view()->share('currentUser', $user);

        // Se nenhum admin existir ainda, promove o primeiro usuário autenticado para superadmin
        if (!$isAdmin) {
            $hasSuper = \App\Models\User::whereIn('role', ['superadmin'])
                ->orWhereIn('level', ['superadmin', 'sucesso'])->exists();
            if (!$hasSuper) {
                $user->role = 'superadmin';
                $user->save();
                $isAdmin = $isSuper = true;
                $isMember = false;
            }
        }

        // Bloqueia todos exceto superadmin
        if (!$isSuper) {
            return redirect()->route('panel.dashboard')->with('warning', 'Apenas o superadministrador pode acessar este painel. Use o novo painel.');
        }
        // É superadmin, permite acesso
        return $next($request);
    }
}