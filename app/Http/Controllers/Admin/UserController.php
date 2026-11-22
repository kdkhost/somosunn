<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $registered = trim((string) $request->input('registered', ''));
        $createdAt = trim((string) $request->input('created_at', ''));
        $query = User::latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        if ($registered === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($createdAt !== '') {
            $query->whereDate('created_at', $createdAt);
        }

        if (!$this->isSuperadmin()) {
            $query->where('role', '!=', 'superadmin');
        }

        // Otimização: KPIs calculados via banco de dados
        $totalUsers = (clone $query)->count();
        $totalAdmins = (clone $query)->whereIn('role', ['admin', 'superadmin'])->count();
        $totalMembers = $totalUsers - $totalAdmins;

        // Otimização: paginação SSR + eager loading para DataTables Server-Side sem carregar toda a base em memória
        $users = $query->with(['plan'])->withCount([
            'eventRegistrations as total_tickets_count' => function ($q) {
                $q->whereIn('status', [\App\Models\EventRegistration::STATUS_PAID, \App\Models\EventRegistration::STATUS_CONFIRMED]);
            },
            'eventRegistrations as checked_in_tickets_count' => function ($q) {
                $q->whereNotNull('check_in_at');
            }
        ])->paginate(15)->withQueryString();

        return view('admin.users.index', compact(
            'users',
            'search',
            'registered',
            'createdAt',
            'totalUsers',
            'totalAdmins',
            'totalMembers'
        ));
    }

    /**
     * Endpoint Server-Side do DataTables para listagem escalável de usuários.
     */
    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 15);
        $length = $length > 0 ? min($length, 100) : 15;

        $dtSearch = trim((string) $request->input('search.value', ''));
        $filterSearch = trim((string) $request->input('search_filter', ''));
        $search = $dtSearch !== '' ? $dtSearch : $filterSearch;
        $registered = trim((string) $request->input('registered', ''));
        $createdAt = trim((string) $request->input('created_at', ''));

        $baseQuery = User::query();
        if (!$this->isSuperadmin()) {
            $baseQuery->where('role', '!=', 'superadmin');
        }

        if ($registered === 'today') {
            $baseQuery->whereDate('created_at', today());
        } elseif ($createdAt !== '') {
            $baseQuery->whereDate('created_at', $createdAt);
        }

        $recordsTotal = (clone $baseQuery)->count();

        $query = clone $baseQuery;
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $recordsFiltered = (clone $query)->count();

        $orderColIdx = (int) $request->input('order.0.column', 0);
        $orderDir = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumns = [
            0 => 'name',
            1 => 'email',
            2 => 'role',
            3 => 'level',
            4 => 'total_tickets_count',
        ];
        $orderBy = $orderColumns[$orderColIdx] ?? 'id';

        $rows = $query->withCount([
            'eventRegistrations as total_tickets_count' => function ($q) {
                $q->whereIn('status', [\App\Models\EventRegistration::STATUS_PAID, \App\Models\EventRegistration::STATUS_CONFIRMED]);
            },
            'eventRegistrations as checked_in_tickets_count' => function ($q) {
                $q->whereNotNull('check_in_at');
            }
        ])
            ->orderBy($orderBy, $orderDir)
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get();

        $marketingUserId = (int) \App\Models\Setting::get('platform_marketing_user_id', 0);
        $currentUser = auth()->user();
        $impersonating = session()->has('impersonator_id');

        $data = $rows->map(function (User $user) use ($marketingUserId, $currentUser, $impersonating): array {
            $verified = $user->hasVerifiedEmail();
            $emailBadge = '<span class="badge d-block mt-1 ' . ($verified ? 'badge-success' : 'badge-warning') . '" style="width:max-content;">'
                . '<i class="fas ' . ($verified ? 'fa-check-circle' : 'fa-exclamation-circle') . ' mr-1"></i>'
                . ($verified ? 'E-mail validado' : 'E-mail pendente')
                . '</span>';

            if ($user->role === 'superadmin') {
                $roleHtml = '<span class="badge badge-danger px-3 py-2" style="font-size:11px;"><i class="fas fa-crown mr-1" style="font-size:9px;"></i>Super Admin</span>';
            } elseif ($user->role === 'admin') {
                $roleHtml = '<span class="badge badge-warning px-3 py-2" style="font-size:11px;"><i class="fas fa-user-shield mr-1" style="font-size:9px;"></i>Admin</span>';
            } else {
                $roleHtml = '<span class="badge badge-secondary px-3 py-2" style="font-size:11px;"><i class="fas fa-user mr-1" style="font-size:9px;"></i>Membro</span>';
            }

            $levelHtml = '<span class="badge badge-light border px-2 py-1">' . e(ucfirst($user->level ?? 'Iniciante')) . '</span>';
            $ticketsHtml = '<span class="badge badge-info px-2 py-1"><i class="fas fa-ticket-alt mr-1" style="font-size:9px;"></i>'
                . (int) $user->checked_in_tickets_count . ' / ' . (int) $user->total_tickets_count . '</span>';

            $actions = '<div class="d-inline-flex" style="gap:4px;">';
            if ($currentUser && $currentUser->isAdmin() && $user->id !== $currentUser->id && !$impersonating) {
                if (!$user->isAdmin() || $currentUser->role === 'superadmin') {
                    $actions .= '<a href="' . e(route('admin.users.impersonate', $user)) . '" class="btn btn-sm btn-outline-warning rounded-pill px-2" title="Acessar como usuário" data-pjax="false"><i class="fas fa-user-secret"></i></a>';
                }
            }
            if ($currentUser && $currentUser->isAdmin()) {
                $isMarketing = $marketingUserId === (int) $user->id;
                $actions .= '<button type="button" class="btn btn-sm rounded-pill px-2 btn-toggle-marketing ' . ($isMarketing ? 'btn-success' : 'btn-outline-info') . '"'
                    . ' title="' . ($isMarketing ? 'Remover como Responsavel de Marketing' : 'Definir como Responsavel de Marketing') . '"'
                    . ' data-url="' . e(route('admin.users.marketing-manager', $user)) . '"'
                    . ' data-user-name="' . e($user->name) . '"'
                    . ' data-has-pix="' . (filled($user->pix_key) ? '1' : '0') . '"'
                    . ' data-is-current="' . ($isMarketing ? '1' : '0') . '">'
                    . '<i class="fas fa-bullhorn"></i></button>';
            }
            if (!$verified) {
                $actions .= '<button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 btn-verify-email"'
                    . ' title="Validar e-mail manualmente"'
                    . ' data-url="' . e(route('admin.users.verify-email', $user)) . '"'
                    . ' data-name="' . e($user->name) . '"><i class="fas fa-envelope-circle-check"></i></button>';
            }
            $actions .= '<a href="' . e(route('admin.users.edit', $user)) . '" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Editar" data-pjax="true"><i class="fas fa-edit"></i></a>';
            $actions .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-delete" title="Excluir" data-action="' . e(route('admin.users.destroy', $user)) . '"><i class="fas fa-trash"></i></button>';
            $actions .= '</div>';

            return [
                'name' => '<span class="font-weight-bold text-sm">' . e($user->name) . '</span>',
                'email' => '<span class="text-muted text-sm">' . e($user->email) . '</span>' . $emailBadge,
                'role' => $roleHtml,
                'level' => $levelHtml,
                'tickets' => $ticketsHtml,
                'actions' => $actions,
            ];
        })->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
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

    public function store(UserRequest $request, AdminUserService $userService)
    {
        $data = $request->validated();

        if (!$this->isSuperadmin()) {
            if (($data['role'] ?? '') === 'superadmin') {
                return response()->json(['message' => 'Voce nao tem permissao para criar um Super Admin.'], 422);
            }

            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso';
            }
        }

        $userService->create($data);

        return response()->json(['redirect' => route('admin.users.index'), 'message' => 'Usuário criado com sucesso.']);
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

    public function update(UserRequest $request, User $user, AdminUserService $userService)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return response()->json(['message' => 'Voce nao tem permissao para editar este usuario.'], 403);
        }

        $data = $request->validated();

        if (!$this->isSuperadmin()) {
            if (($data['role'] ?? '') === 'superadmin') {
                return response()->json(['message' => 'Voce nao tem permissao para definir este papel.'], 422);
            }

            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso';
            }
        }

        $userService->update($user, $data);

        return response()->json(['redirect' => route('admin.users.index'), 'message' => 'Usuário atualizado com sucesso.']);
    }

    public function verifyEmail(User $user, AdminUserService $userService)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return response()->json(['message' => 'Você não tem permissão para alterar este usuário.'], 403);
        }

        $verified = $userService->verifyEmail($user);

        return response()->json([
            'ok' => true,
            'message' => $verified ? 'E-mail validado manualmente.' : 'O e-mail já estava validado.',
        ]);
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
                    if (!in_array($currentUser->role, ['admin', 'superadmin'], true)) {
                        $currentUser->forceFill(['platform_fee_enabled' => true])->save();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'assigned' => false,
                'message' => 'Responsavel de Marketing removido.',
            ]);
        }

        if (blank($user->pix_key)) {
            $validated = $request->validate([
                'pix_key' => ['required', 'string', 'max:255'],
            ], [
                'pix_key.required' => 'Informe a chave PIX antes de definir o responsável de marketing.',
            ]);

            $user->forceFill(['pix_key' => trim($validated['pix_key'])])->save();
        }

        // Remover do anterior se houver (respeitando plano)
        if ($currentId > 0 && $currentId !== $user->id) {
            $previousUser = User::find($currentId);
            if ($previousUser) {
                $this->revokeMarketingFeatures($previousUser, $marketingFeatures);
                if (!in_array($previousUser->role, ['admin', 'superadmin'], true)) {
                    $previousUser->forceFill(['platform_fee_enabled' => true])->save();
                }
            }
        }

        \App\Models\Setting::set('platform_marketing_user_id', (string) $user->id);

        // Conceder features de vendedor/instrutor/certificado ao novo marketing manager
        $extra = $user->extra_features ?? [];
        $extra = array_unique(array_merge($extra, $marketingFeatures));
        $user->update([
            'extra_features' => array_values($extra),
            'platform_fee_enabled' => false,
        ]);

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

    /**
     * Suspender/bloquear um usuario (punição configurável).
     * POST /admin/users/{user}/suspend (AJAX)
     */
    public function suspend(Request $request, User $user)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return response()->json(['message' => 'Nao e possivel suspender um Super Admin.'], 403);
        }

        $request->validate([
            'duration_hours' => 'required|integer|min:1|max:8760', // max 1 ano
            'reason'         => 'required|string|max:500',
            'events_suspension' => 'nullable|integer|min:0|max:100',
        ]);

        $blockedUntil = now()->addHours((int) $request->input('duration_hours'));

        $user->update([
            'blocked_until'                => $blockedUntil,
            'block_reason'                 => $request->input('reason'),
            'events_suspension_remaining'  => (int) $request->input('events_suspension', 0),
        ]);

        try {
            \Illuminate\Support\Facades\Log::channel('security')->info('Usuario suspenso pelo admin', [
                'user_id'       => $user->id,
                'user_name'     => $user->name,
                'blocked_until' => $blockedUntil->toIso8601String(),
                'reason'        => $request->input('reason'),
                'actor_id'      => auth()->id(),
                'ip'            => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'ok'      => true,
            'message' => "Usuario {$user->name} suspenso ate " . $blockedUntil->format('d/m/Y H:i') . '.',
        ]);
    }

    /**
     * Remover suspensão de um usuario.
     * POST /admin/users/{user}/unsuspend (AJAX)
     */
    public function unsuspend(Request $request, User $user)
    {
        $user->update([
            'blocked_until'                => null,
            'block_reason'                 => null,
            'events_suspension_remaining'  => 0,
        ]);

        try {
            \Illuminate\Support\Facades\Log::channel('security')->info('Suspensao removida pelo admin', [
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'actor_id'  => auth()->id(),
                'ip'        => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'ok'      => true,
            'message' => "Suspensao de {$user->name} removida.",
        ]);
    }
}
