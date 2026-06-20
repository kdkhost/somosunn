<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\AdminUserService;

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
        $query = User::with('plan')->latest();

        if (request()->has('search') && request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (request()->has('role') && request('role') !== 'todos') {
            $role = request('role');
            if (in_array($role, ['member', 'membro'], true)) {
                $query->whereIn('role', ['member', 'membro']);
            } else {
                $query->where('role', $role);
            }
        }

        if (!$this->isSuperadmin()) {
            $query->where('role', '!=', 'superadmin');
        }

        $users = $query->withCount([
            'eventRegistrations as total_tickets_count' => function ($q) {
                $q->whereIn('status', [\App\Models\EventRegistration::STATUS_PAID, \App\Models\EventRegistration::STATUS_CONFIRMED]);
            },
            'eventRegistrations as checked_in_tickets_count' => function ($q) {
                $q->whereNotNull('check_in_at');
            },
        ])->get();

        return view('panel.admin.users.index', compact('users'));
    }

    public function create()
    {
        $user = new User();
        $plans = Plan::where('is_active', true)->get();

        return view('panel.admin.users.form', [
            'user' => $user,
            'plans' => $plans,
            'userFeatures' => $this->userFeatures(),
            'canSetSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function store(UserRequest $request, AdminUserService $userService)
    {
        $data = $request->validated();

        if (!$this->isSuperadmin()) {
            if (($data['role'] ?? '') === 'superadmin') {
                return back()->withInput()->with('error', 'Voce nao tem permissao para criar um Super Admin.');
            }

            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso';
            }
        }

        $userService->create($data);

        return redirect()->route('panel.admin.users.index')->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(User $user)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->route('panel.admin.users.index')->with('error', 'Voce nao tem permissao para editar este usuario.');
        }

        $plans = Plan::where('is_active', true)->get();

        return view('panel.admin.users.form', [
            'user' => $user,
            'plans' => $plans,
            'userFeatures' => $this->userFeatures(),
            'canSetSuperadmin' => $this->isSuperadmin(),
        ]);
    }

    public function update(UserRequest $request, User $user, AdminUserService $userService)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->route('panel.admin.users.index')->with('error', 'Voce nao tem permissao para editar este usuario.');
        }

        $data = $request->validated();

        if (!$this->isSuperadmin()) {
            if (($data['role'] ?? '') === 'superadmin') {
                return back()->withInput()->with('error', 'Voce nao tem permissao para definir este papel.');
            }

            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso';
            }
        }

        $userService->update($user, $data);

        return redirect()->route('panel.admin.users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function verifyEmail(User $user, AdminUserService $userService)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->back()->with('error', 'Você não tem permissão para alterar este usuário.');
        }

        $verified = $userService->verifyEmail($user);

        return redirect()->back()->with(
            'success',
            $verified ? 'E-mail validado manualmente.' : 'O e-mail já estava validado.'
        );
    }

    public function destroy(User $user)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return back()->with('error', 'Voce nao pode excluir um Super Admin.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Voce nao pode excluir sua propria conta.');
        }

        $user->delete();

        return redirect()->route('panel.admin.users.index')->with('success', 'Usuario removido com sucesso.');
    }
}
