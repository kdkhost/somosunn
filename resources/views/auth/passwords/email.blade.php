@extends('layouts.app')

@section('title', 'Recuperar senha - UNN')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-xl bg-white rounded-2xl shadow-lg p-8">
        <div class="mb-6 text-center">
            <h3 class="text-2xl font-extrabold text-slate-900">Esqueceu sua senha?</h3>
            <p class="text-sm text-slate-500 mt-2">Informe o e-mail cadastrado e enviaremos um link para redefinição.</p>
        </div>
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                <input name="email" type="email" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-300" />
            </div>
            <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg">Enviar link</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-500">Lembrou a senha? <a href="{{ route('login') }}" class="text-purple-600 font-semibold">Voltar ao login</a></p>
    </div>
</div>
@endsection
