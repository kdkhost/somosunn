@extends('layouts.app')

@section('title', 'Resetar senha - UNN')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 !px-0">
        
        <x-auth-visual title="Nova senha">
            Defina uma senha forte e segura para proteger sua conta e seus dados.
        </x-auth-visual>

        <div class="p-10 flex flex-col justify-center">
            <h3 class="text-3xl font-bold mb-8 text-slate-900">Redefinir Senha</h3>
            
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token ?? '' }}">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input name="email" type="email" value="{{ $email ?? old('email') }}" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#7a5af8] transition-all" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nova senha</label>
                    <input name="password" type="password" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" placeholder="Mínimo 8 caracteres" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirmar senha</label>
                    <input name="password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                
                <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">Alterar senha</button>
            </form>
        </div>
    </div>
</div>
@endsection
