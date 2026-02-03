<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate(['email'=>'required|email','password'=>'required']);
        if(Auth::attempt($credentials, $request->filled('remember'))){
            $request->session()->regenerate();
            $user = Auth::user();
            if(($user->role ?? null) === 'admin' || ($user->level ?? null) === 'sucesso'){
                return redirect()->intended(route('admin.dashboard'));
            }
            return redirect()->intended('/');
        }
        return back()->withErrors(['email' => 'Credenciais inválidas']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
