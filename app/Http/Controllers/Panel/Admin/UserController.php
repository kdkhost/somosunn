<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Recursos disponíveis para liberar individualmente para usuários.
     * Esses controlam o que o MEMBRO pode acessar no site.
     */
    private const USER_FEATURES = [
        // Acesso básico
        'community' => 'Comunidade (perfil/feed)',
        'chat' => 'Chat (mensagens)',
        'connections' => 'Networking (conexões)',
        'connections.unlimited' => 'Conexões ilimitadas',

        // Cursos
        'courses' => 'Acesso a cursos',
        'courses.create' => 'Criar cursos',
        'courses.edit' => 'Editar cursos',
        'courses.delete' => 'Excluir cursos',
        'courses.certificates' => 'Certificados de cursos',
        'courses.downloads' => 'Downloads de materiais',

        // Eventos
        'events' => 'Acesso a eventos',
        'events.create' => 'Criar eventos',
        'events.edit' => 'Editar eventos',
        'events.delete' => 'Excluir eventos',
        'events.recordings' => 'Gravações de eventos',
        'events.vip' => 'Eventos VIP/exclusivos',

        // Mentorias
        'mentorships' => 'Acesso a mentorias',
        'mentorships.create' => 'Criar mentorias',
        'mentorships.edit' => 'Editar mentorias',
        'mentorships.delete' => 'Excluir mentorias',
        'mentorships.group' => 'Mentorias em grupo',
        'mentorships.individual' => 'Mentorias individuais',

        // Marketplace
        'marketplace' => 'Acesso ao marketplace',
        'marketplace.sales' => 'Ver histórico de vendas',
        'marketplace.buy' => 'Comprar produtos/serviços',
        'marketplace.sell' => 'Vender no marketplace',

        // Extras
        'rankings' => 'Participar do ranking',
        'support.priority' => 'Suporte prioritário',
        'early.access' => 'Acesso antecipado a novidades',

        // Admin
        'admin.panel' => 'Acesso ao painel admin',
    ];

    /**
     * Check if current user is superadmin.
     */
    private function isSuperadmin(): bool
    {
        return auth()->check() && auth()->user()->role === 'superadmin';
    }

    public function index()
    {
        $query = User::with('plan')->latest();

        // Search
        if (request()->has('search') && request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if (request()->has('role') && request('role') !== 'todos') {
            $query->where('role', request('role'));
        }

        // Admin não pode ver superadmins
        if (!$this->isSuperadmin()) {
            $query->where('role', '!=', 'superadmin');
        }

        $users = $query->paginate(20)->withQueryString();

        return view('panel.admin.users.index', compact('users'));
    }

    public function create()
    {
        $user = new User();
        $plans = Plan::where('is_active', true)->get();

        return view('panel.admin.users.form', [
            'user' => $user,
            'plans' => $plans,
            'userFeatures' => self::USER_FEATURES,
            'canSetSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'nullable|string',
            'level' => 'nullable|string',
            'plan_id' => 'nullable|exists:plans,id',
            'plan_expires_at' => 'nullable|date',
            'extra_features' => 'nullable|array',
            'extra_features.*' => 'string|in:' . implode(',', array_keys(self::USER_FEATURES)),
        ]);

        // Admin não pode criar superadmin (role ou level)
        if (!$this->isSuperadmin()) {
            if (($data['role'] ?? '') === 'superadmin') {
                return back()->withInput()->with('error', 'Você não tem permissão para criar um Super Admin.');
            }
            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso'; // Fallback
            }
        }

        $data['password'] = Hash::make($data['password']);
        $data['extra_features'] = $data['extra_features'] ?? [];

        User::create($data);

        return redirect()->route('panel.admin.users.index')->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(User $user)
    {
        // Admin não pode editar superadmin
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->route('panel.admin.users.index')->with('error', 'Você não tem permissão para editar este usuário.');
        }

        $plans = Plan::where('is_active', true)->get();

        return view('panel.admin.users.form', [
            'user' => $user,
            'plans' => $plans,
            'userFeatures' => self::USER_FEATURES,
            'canSetSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Admin não pode editar superadmin
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->route('panel.admin.users.index')->with('error', 'Você não tem permissão para editar este usuário.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:6',
            'role' => 'nullable|string',
            'level' => 'nullable|string',
            'plan_id' => 'nullable|exists:plans,id',
            'plan_expires_at' => 'nullable|date',
            'extra_features' => 'nullable|array',
            'extra_features.*' => 'string|in:' . implode(',', array_keys(self::USER_FEATURES)),
        ]);

        // Admin não pode promover alguém a superadmin (role ou level)
        if (!$this->isSuperadmin()) {
            if (($data['role'] ?? '') === 'superadmin') {
                return back()->withInput()->with('error', 'Você não tem permissão para definir este papel.');
            }
            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso'; // Fallback
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['extra_features'] = $data['extra_features'] ?? [];

        $user->update($data);

        return redirect()->route('panel.admin.users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user)
    {
        // Admin não pode excluir superadmin
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return back()->with('error', 'Você não pode excluir um Super Admin.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        $user->delete();

        return redirect()->route('panel.admin.users.index')->with('success', 'Usuário removido com sucesso.');
    }
}
