<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function impersonate($id)
    {
        // Verifica se quem está tentando logar é SuperAdmin
        $currentUser = Auth::user();
        
        // Lógica simples de verificação (pode ser movida para Gate/Policy)
        $isSuper = in_array($currentUser->role, ['superadmin']) || in_array($currentUser->level, ['superadmin','sucesso']);

        if (!$isSuper) {
            return redirect()->back()->with('error', 'Apenas Super Administradores podem acessar outras contas.');
        }

        $userToImpersonate = User::findOrFail($id);

        // Previne impersonar outro superadmin (segurança opcional, mas recomendada)
        if ($userToImpersonate->role === 'superadmin' && $currentUser->id !== $userToImpersonate->id) {
            // return redirect()->back()->with('error', 'Não é possível acessar conta de outro Super Admin.');
        }

        // Guarda o ID original na sessão
        session()->put('impersonator_id', $currentUser->id);
        
        // Loga como o novo usuário
        Auth::login($userToImpersonate);

        return redirect()->route('admin.dashboard')->with('success', "Você está acessando como {$userToImpersonate->name}");
    }

    public function stop()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('admin.dashboard');
        }

        $originalId = session()->pull('impersonator_id');
        $originalUser = User::find($originalId);

        if ($originalUser) {
            Auth::login($originalUser);
            return redirect()->route('admin.users.index')->with('success', 'Voltou para sua conta original.');
        }

        Auth::logout();
        return redirect()->route('login');
    }
}
