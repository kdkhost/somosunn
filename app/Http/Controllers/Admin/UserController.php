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
                return redirect()->back()->withInput()->with('error', 'Voce nao tem permissao para criar um Super Admin.');
            }

            if (($data['level'] ?? '') === 'superadmin') {
                $data['level'] = 'sucesso';
            }
        }

        $data['password'] = Hash::make($data['password']);
        $data['extra_features'] = $data['extra_features'] ?? [];

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Usuario criado.');
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
            return redirect()->route('admin.users.index')->with('error', 'Voce nao tem permissao para editar este usuario.');
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
                return redirect()->back()->withInput()->with('error', 'Voce nao tem permissao para definir este papel.');
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

        return redirect()->route('admin.users.index')->with('success', 'Usuario atualizado.');
    }

    public function destroy(User $user)
    {
        if (!$this->isSuperadmin() && $user->role === 'superadmin') {
            return response()->json(['message' => 'Voce nao pode excluir um Super Admin.'], 403);
        }

        $user->delete();

        return response()->json(['ok' => true]);
    }
}
