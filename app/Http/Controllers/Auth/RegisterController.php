<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        // Award signup points (if rules exist)
        try {
            $ps = new \App\Services\PointsService();
            $ps->award($user, 'signup');
        } catch (\Throwable $e) { \Log::error('Points award error: '.$e->getMessage()); }

        Auth::login($user);
        return redirect()->intended('/');
    }
}