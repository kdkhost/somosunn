@extends('layouts.app')

@section('title', 'Entrar - UNN')

@section('content')
@php
    // $logoAuth logic moved to component
@endphp
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        <!-- Coluna visual -->
        <x-auth-visual title="Bem-vindo de volta!" :show-social="true">
            Acesse seu painel, cursos, palestras e mentorias com as suas credenciais ou login social.
        </x-auth-visual>

        <!-- Coluna formulário -->
        <div class="p-10">
            <h3 class="text-3xl font-bold mb-8">Entrar na sua conta</h3>
            <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input name="email" type="email" required autocomplete="email" class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Senha</label>
                    <input name="password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" name="remember" class="rounded" /> Lembrar-me</label>
                    <a href="{{ route('password.request') }}" class="text-[#7a5af8] font-semibold">Esqueci minha senha</a>
                </div>
                <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg">Entrar</button>
            </form>

            <p class="mt-6 text-center text-sm">Não tem conta? <a href="{{ route('register') }}" class="text-[#7a5af8] font-semibold">Crie uma</a></p>
        </div>
    </div>
</div>
@endsection