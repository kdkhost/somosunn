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

        // Log de auditoria: início de impersonação (Requisito 19.2)
        try {
            \Illuminate\Support\Facades\Log::channel('security')->info('Impersonação iniciada', [
                'impersonator_id'   => $currentUser->id,
                'impersonator_name' => $currentUser->name,
                'impersonator_role' => $currentUser->role,
                'target_id'         => $userToImpersonate->id,
                'target_name'       => $userToImpersonate->name,
                'target_role'       => $userToImpersonate->role,
                'ip'                => request()->ip(),
                'user_agent'        => request()->userAgent(),
                'timestamp'         => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {}

        // Guarda o ID original na sessão
        session()->put('impersonator_id', $currentUser->id);
        session()->put('impersonator_is_admin', true);
        session()->put('impersonator_name', (string) ($currentUser->name ?? ''));
        session()->put('impersonation_started_at', now()->toIso8601String());

        // Loga como o novo usuário
        Auth::login($userToImpersonate);

        return redirect()->route('panel.dashboard')
            ->with('toastr_info', "Acesso supervisionado ativo. Você está como {$userToImpersonate->name}.");
    }

    public function stop()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('panel.dashboard');
        }

        $originalId = session()->pull('impersonator_id');
        $impersonatedUser = auth()->user();
        $impersonatedName = $impersonatedUser?->name ?? 'usuário';
        session()->forget(['impersonator_is_admin', 'impersonator_name', 'impersonation_started_at']);
        $originalUser = User::find($originalId);

        // Log de auditoria: fim de impersonação (Requisito 19.2)
        try {
            \Illuminate\Support\Facades\Log::channel('security')->info('Impersonação encerrada', [
                'impersonator_id'   => $originalId,
                'target_id'         => $impersonatedUser?->id,
                'target_name'       => $impersonatedName,
                'ip'                => request()->ip(),
                'user_agent'        => request()->userAgent(),
                'timestamp'         => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {}

        if ($originalUser) {
            Auth::login($originalUser);

            // Superadmin volta para o painel legado, admin para o painel novo
            $redirectRoute = $originalUser->isSuperAdmin()
                ? route('admin.dashboard')
                : route('panel.admin.dashboard');

            return redirect($redirectRoute)
                ->with('toastr_success', "Sessão supervisionada encerrada. Você estava como {$impersonatedName}.");
        }

        Auth::logout();
        return redirect()->route('login');
    }
}
