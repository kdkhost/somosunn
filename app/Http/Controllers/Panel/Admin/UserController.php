<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            $query->where('role', request('role'));
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
                return back()->withInput()->with('error', 'Voce nao tem permissao para criar um Super Admin.');
            }

            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso';
            }
        }

        $data['password'] = Hash::make($data['password']);
        $data['extra_features'] = $data['extra_features'] ?? [];

        User::create($data);

        return redirect()->route('panel.admin.users.index')->with('success', 'Usuario criado com sucesso.');
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

    public function update(Request $request, User $user)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return redirect()->route('panel.admin.users.index')->with('error', 'Voce nao tem permissao para editar este usuario.');
        }

        $featureKeys = array_keys($this->userFeatures());

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
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
                return back()->withInput()->with('error', 'Voce nao tem permissao para definir este papel.');
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

        return redirect()->route('panel.admin.users.index')->with('success', 'Usuario atualizado com sucesso.');
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
