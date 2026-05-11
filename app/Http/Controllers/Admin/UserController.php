<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function isSuperadmin(): bool
    {
        return auth()->check() && auth()->user()->role === 'superadmin';
    }

    private function userFeatures(): array
    {
        return Plan::siteFeatureLabels();
    }

    public function index()
    {
        $query = User::latest();

        if (!$this->isSuperadmin()) {
            $query->where('role', '!=', 'superadmin');
        }

        $users = $query->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $user = new User();

        return view('admin.users.form', [
            'user' => $user,
            'userFeatures' => $this->userFeatures(),
            'canSetSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function store(Request $request)
    {
        $featureKeys = array_keys($this->userFeatures());

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'nullable|string',
            'level' => 'nullable|string',
            'plan_id' => 'nullable|exists:plans,id',
            'plan_expires_at' => 'nullable|date',
            'extra_features' => 'nullable|array',
            'extra_features.*' => 'string|in:' . implode(',', $featureKeys),
        ]);

        if (!$this->isSuperadmin()) {
            if (($data['role'] ?? '') === 'superadmin') {
                return response()->json(['message' => 'Voce nao tem permissao para criar um Super Admin.'], 422);
            }

            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso';
            }
        }

        $data['password'] = Hash::make($data['password']);
        $data['extra_features'] = $data['extra_features'] ?? [];

        User::create($data);

        return response()->json(['redirect' => route('admin.users.index'), 'message' => 'Usuario criado.']);
    }

    public function edit(User $user)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->route('admin.users.index')->with('error', 'Voce nao tem permissao para editar este usuario.');
        }

        return view('admin.users.form', [
            'user' => $user,
            'userFeatures' => $this->userFeatures(),
            'canSetSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return response()->json(['message' => 'Voce nao tem permissao para editar este usuario.'], 403);
        }

        $featureKeys = array_keys($this->userFeatures());

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'nullable|string',
            'level' => 'nullable|string',
            'plan_id' => 'nullable|exists:plans,id',
            'plan_expires_at' => 'nullable|date',
            'extra_features' => 'nullable|array',
            'extra_features.*' => 'string|in:' . implode(',', $featureKeys),
        ]);

        if (!$this->isSuperadmin()) {
            if (($data['role'] ?? '') === 'superadmin') {
                return response()->json(['message' => 'Voce nao tem permissao para definir este papel.'], 422);
            }

            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso';
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['extra_features'] = $data['extra_features'] ?? [];

        $user->update($data);

        return response()->json(['redirect' => route('admin.users.index'), 'message' => 'Usuario atualizado.']);
    }

    public function destroy(User $user)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return response()->json(['message' => 'Voce nao pode excluir um Super Admin.'], 403);
        }

        $user->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Define (ou remove) o usuario como Responsavel de Marketing da plataforma.
     * Somente admins/superadmins podem alterar essa configuracao.
     * O responsavel de marketing recebera 10% de cada venda concluida.
     */
    public function setMarketingManager(Request $request, User $user)
    {
        if (!auth()->user()?->isAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $action = $request->input('action', 'set');
        $currentId = (int) \App\Models\Setting::get('platform_marketing_user_id', 0);

        // Features que o marketing manager precisa para vender/instruir/certificar
        $marketingFeatures = [
            'marketplace.sell',
            'courses.create',
            'events.create',
            'mentorships.create',
            'courses_access',
            'events_access',
            'mentorships_access',
            'certificates_access',
        ];

        if ($action === 'unset' || $currentId === $user->id) {
            \App\Models\Setting::set('platform_marketing_user_id', '');

            // Remover APENAS features que o plano do usuario NAO concede
            if ($currentId === $user->id) {
                $currentUser = User::find($currentId);
                if ($currentUser) {
                    $this->revokeMarketingFeatures($currentUser, $marketingFeatures);
                }
            }

            return response()->json([
                'success' => true,
                'assigned' => false,
                'message' => 'Responsavel de Marketing removido.',
            ]);
        }

        // Remover do anterior se houver (respeitando plano)
        if ($currentId > 0 && $currentId !== $user->id) {
            $previousUser = User::find($currentId);
            if ($previousUser) {
                $this->revokeMarketingFeatures($previousUser, $marketingFeatures);
            }
        }

        \App\Models\Setting::set('platform_marketing_user_id', (string) $user->id);

        // Conceder features de vendedor/instrutor/certificado ao novo marketing manager
        $extra = $user->extra_features ?? [];
        $extra = array_unique(array_merge($extra, $marketingFeatures));
        $user->update(['extra_features' => array_values($extra)]);

        try {
            $user->notify(new \App\Notifications\MarketingManagerAssigned());
            \App\Notifications\MarketingManagerAssigned::sendMail($user);
        } catch (\Throwable $e) {
            \Log::warning('Falha ao enviar notificacao de Marketing Manager: ' . $e->getMessage());
        }

        return response()->json([
            'success'  => true,
            'assigned' => true,
            'user_id'  => $user->id,
            'message'  => 'Usuario definido como Responsavel de Marketing com acesso de vendedor/instrutor. Notificacao enviada.',
        ]);
    }

    /**
     * Remove features de marketing do usuario, mas preserva as que o plano dele ja concede.
     */
    private function revokeMarketingFeatures(User $user, array $marketingFeatures): void
    {
        $extra = $user->extra_features ?? [];

        // Verificar quais features o plano do usuario ja concede
        $plan = $user->activePlan();
        $planFeatures = [];
        if ($plan && method_exists($plan, 'resolvedPermissions')) {
            $planFeatures = $plan->resolvedPermissions();
        } elseif ($plan && isset($plan->permissions)) {
            $planFeatures = (array) $plan->permissions;
        }

        // So remover features que o plano NAO concede
        $featuresToRemove = array_diff($marketingFeatures, $planFeatures);
        $extra = array_values(array_diff($extra, $featuresToRemove));

        $user->update(['extra_features' => $extra]);
    }
}
