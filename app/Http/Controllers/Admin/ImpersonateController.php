<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    public function impersonate(Request $request, $id)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isAdmin()) {
            return redirect()->back()->with('error', 'Apenas administradores podem acessar outras contas.');
        }

        if ($request->session()->has('impersonator_id')) {
            return redirect()->route('panel.dashboard')
                ->with('toastr_warning', 'Encerre o acesso supervisionado atual antes de acessar outra conta.');
        }

        $userToImpersonate = User::findOrFail($id);

        if ($currentUser->is($userToImpersonate)) {
            return redirect()->back()->with('error', 'Voce ja esta autenticado nesta conta.');
        }

        if ($userToImpersonate->isAdmin()) {
            if (!$currentUser->isSuperAdmin()) {
                return redirect()->back()->with('error', 'Apenas superadministradores podem acessar contas de outros administradores.');
            }

            if ($userToImpersonate->isSuperAdmin()) {
                return redirect()->back()->with('error', 'Nao e possivel acessar a conta de outro superadministrador.');
            }
        }

        try {
            Log::channel('security')->info('Acesso supervisionado iniciado', [
                'impersonator_id' => $currentUser->id,
                'impersonator_name' => $currentUser->name,
                'impersonator_role' => $currentUser->role,
                'target_id' => $userToImpersonate->id,
                'target_name' => $userToImpersonate->name,
                'target_role' => $userToImpersonate->role,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
        }

        // A conta acessada recebe uma sessao totalmente limpa. Isso impede
        // vazamento de formularios, erros e estado visual do supervisor.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::login($userToImpersonate);

        $request->session()->put([
            'impersonator_id' => $currentUser->id,
            'impersonator_name' => (string) $currentUser->name,
            'impersonator_role' => (string) $currentUser->role,
            'impersonated_user_id' => $userToImpersonate->id,
            'impersonated_user_name' => (string) $userToImpersonate->name,
            'impersonation_started_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('panel.dashboard')
            ->with('toastr_info', "Acesso supervisionado ativo por {$currentUser->name} na conta de {$userToImpersonate->name}.");
    }

    public function stop(Request $request)
    {
        if (!$request->session()->has('impersonator_id')) {
            return redirect()->route('panel.dashboard');
        }

        $originalId = (int) $request->session()->get('impersonator_id');
        $supervisorName = (string) $request->session()->get('impersonator_name', 'Supervisor');
        $impersonatedUser = Auth::user();
        $impersonatedName = $impersonatedUser?->name ?? 'usuario';
        $originalUser = User::find($originalId);

        try {
            Log::channel('security')->info('Acesso supervisionado encerrado', [
                'impersonator_id' => $originalId,
                'impersonator_name' => $supervisorName,
                'target_id' => $impersonatedUser?->id,
                'target_name' => $impersonatedName,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($originalUser) {
            Auth::login($originalUser);

            $redirectRoute = $originalUser->isSuperAdmin()
                ? route('admin.dashboard')
                : route('panel.admin.dashboard');

            return redirect($redirectRoute)
                ->with('toastr_success', "Acesso supervisionado encerrado. Voce voltou para a conta de {$supervisorName}.");
        }

        return redirect()->route('login');
    }
}
