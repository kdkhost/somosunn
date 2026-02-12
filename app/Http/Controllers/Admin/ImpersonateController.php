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
        // Verifica se quem está tentando logar é Admin ou SuperAdmin
        $currentUser = Auth::user();

        // Permite admin e superadmin
        if (!$currentUser->isAdmin()) {
            return redirect()->back()->with('error', 'Apenas Administradores podem acessar outras contas.');
        }

        $userToImpersonate = User::findOrFail($id);

        // Previne impersonar outro admin/superadmin (apenas superadmin pode)
        if ($userToImpersonate->isAdmin()) {
            if ($currentUser->role !== 'superadmin') {
                return redirect()->back()->with('error', 'Apenas Super Administradores podem acessar contas de outros admins.');
            }

            // Superadmin não pode impersonate outro superadmin
            if ($userToImpersonate->role === 'superadmin' && $currentUser->id !== $userToImpersonate->id) {
                return redirect()->back()->with('error', 'Não é possível acessar conta de outro Super Admin.');
            }
        }

        // Guarda o ID original na sessão
        session()->put('impersonator_id', $currentUser->id);
        session()->put('impersonator_is_admin', true);
        session()->put('impersonator_name', (string) ($currentUser->name ?? ''));

        // Loga como o novo usuário
        Auth::login($userToImpersonate);

        return redirect()->route('panel.dashboard')->with('success', "Você está acessando como {$userToImpersonate->name}");
    }

    public function stop()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('panel.dashboard');
        }

        $originalId = session()->pull('impersonator_id');
        session()->forget(['impersonator_is_admin', 'impersonator_name']);
        $originalUser = User::find($originalId);

        if ($originalUser) {
            Auth::login($originalUser);
            return redirect()->route('admin.users.index')->with('success', 'Voltou para sua conta original.');
        }

        Auth::logout();
        return redirect()->route('login');
    }
}
