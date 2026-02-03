@extends('layouts.app')

@section('title', 'Recuperar senha - UNN')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        
        <x-auth-visual title="Recuperar acesso">
            Esqueceu sua senha? Não se preocupe, informe seu e-mail e nós te ajudamos a recuperar.
        </x-auth-visual>

        <div class="p-10 flex flex-col justify-center">
            <div class="mb-6">
                <h3 class="text-3xl font-bold text-slate-900 mb-2">Esqueceu a senha?</h3>
                <p class="text-slate-500">Informe o e-mail cadastrado e enviaremos um link.</p>
            </div>
            
            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input name="email" type="email" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-300 transition-all" />
                </div>
                <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">Enviar link de recuperação</button>
            </form>
            
            <p class="mt-8 text-center text-sm text-slate-500">
                Lembrou a senha? <a href="{{ route('login') }}" class="text-[#7a5af8] font-bold hover:underline">Voltar ao login</a>
            </p>
            
            <!-- Voltar ao site -->
            <div class="mt-auto pt-6 text-center">
                 <a href="/" class="text-xs text-slate-400 hover:text-slate-600 flex items-center justify-center gap-1"><i class="fas fa-arrow-left"></i> Voltar ao site</a>
            </div>
        </div>
    </div>
</div>
@endsection
