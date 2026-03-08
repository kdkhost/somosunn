<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        'rankings' => 'Visualizar ranking de membros',
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
        $query = User::latest();

        // Admin não pode ver superadmins
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
                return redirect()->back()->withInput()->with('error', 'Você não tem permissão para criar um Super Admin.');
            }
            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso'; // Fallback para o nível mais alto permitido
            }
        }

        $data['password'] = Hash::make($data['password']);
        $data['extra_features'] = $data['extra_features'] ?? [];
        User::create($data);
        return redirect()->route('admin.users.index')->with('success', 'Usuário criado.');
    }

    public function edit(User $user)
    {
        // Admin não pode editar superadmin
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->route('admin.users.index')->with('error', 'Você não tem permissão para editar este usuário.');
        }

        return view('admin.users.form', [
            'user' => $user,
            'userFeatures' => self::USER_FEATURES,
            'canSetSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Admin não pode editar superadmin
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->route('admin.users.index')->with('error', 'Você não tem permissão para editar este usuário.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,' . $user->id,
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
                return redirect()->back()->withInput()->with('error', 'Você não tem permissão para definir este papel.');
            }
            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso'; // Fallback para o nível mais alto permitido
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['extra_features'] = $data['extra_features'] ?? [];
        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado.');
    }

    public function destroy(User $user)
    {
        // Admin não pode excluir superadmin
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return response()->json(['message' => 'Você não pode excluir um Super Admin.'], 403);
        }
        $user->delete();
        return response()->json(['ok' => true]);
    }
}
