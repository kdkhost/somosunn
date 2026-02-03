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
        $isSuper = in_array($role, ['superadmin']) || in_array($level, ['superadmin','sucesso'], true);
        $isAdmin = $isSuper || in_array($role, ['admin'], true);
        $isMember = !$isAdmin; // Por padrão, qualquer outro logado é membro com acesso restrito

        // Compartilha variáveis globais com todas as views do admin
        view()->share('isSuper', $isSuper);
        view()->share('isAdmin', $isAdmin);
        view()->share('currentUser', $user);

        // Se nenhum admin existir ainda, promove o primeiro usuário autenticado para superadmin
        if (!$isAdmin && !$isMember) {
            $hasAdmin = \App\Models\User::whereIn('role',['admin','superadmin'])
                ->orWhereIn('level',['superadmin','sucesso'])->exists();
            if (!$hasAdmin) {
                $user->role = 'superadmin';
                $user->save();
                $isAdmin = $isSuper = true;
                $isMember = false;
            }
        }
        
        // Permite acesso se for admin ou membro
        // (A proteção de rotas específicas será feita no web.php ou controllers)
        if (auth()->check()) {
            return $next($request);
        }

        return redirect()->route('login')->with('error', 'Acesso restrito.');
    }
}