<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'doc' => 'nullable|string',
            'phone' => 'nullable|string',
            'cep' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'doc' => $data['doc'] ?? null,
            'phone' => $data['phone'] ?? null,
            'cep' => $data['cep'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        // Vincula pacote inicial (cliente) para liberar o Painel do Membro imediatamente
        try {
            $defaultPlan = Plan::query()->where('slug', 'cliente')->first() ?? Plan::query()->orderBy('price')->orderBy('id')->first();
            if ($defaultPlan) {
                $user->plan_id = (int) $defaultPlan->id;
                $user->plan_expires_at = null;
                $user->save();
            }
        } catch (\Throwable $e) {
            // ignore (fallback: usuário escolhe plano no /premium)
        }

        // Award signup points (if rules exist)
        try {
            $ps = new \App\Services\PointsService();
            $ps->award($user, 'signup');
        } catch (\Throwable $e) {
            \Log::error('Points award error: ' . $e->getMessage());
        }

        Auth::login($user);
        return redirect()->route('panel.dashboard')
            ->with('success', 'Seja bem-vindo à SOMOS UNN! Você já pode acessar seu painel. Se quiser, faça um upgrade de plano para liberar mais recursos.');
    }
}
