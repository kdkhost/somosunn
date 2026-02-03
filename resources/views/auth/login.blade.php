@extends('layouts.app')

@section('title', 'Entrar - UNN')

@section('content')
@php
    $logoAuth = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
    $logoAuthSrc = $logoAuth ? asset(ltrim($logoAuth, '/')) : asset('img/logo.svg');
@endphp
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        <!-- Coluna visual -->
        <div class="hidden md:flex flex-col items-center justify-center gap-6 p-12 bg-gradient-to-br from-[#7a5af8] via-[#6a40e6] to-[#4cc3ff] text-white">
            <img src="{{ $logoAuthSrc }}" class="h-16 mb-4" alt="UNN" onerror="this.style.display='none';">
            <h2 class="text-2xl font-bold">Bem-vindo de volta!</h2>
            <p class="max-w-xs text-sm text-white/90">Acesse seu painel, cursos, palestras e mentorias com as suas credenciais ou login social.</p>
            <div class="flex gap-3 mt-4 text-sm font-semibold">
                <a href="{{ route('social.redirect','google') }}" class="bg-white/15 px-4 py-2 rounded-xl hover:bg-white/25 transition">Google</a>
                <a href="{{ route('social.redirect','facebook') }}" class="bg-white/15 px-4 py-2 rounded-xl hover:bg-white/25 transition">Facebook</a>
                <a href="{{ route('social.redirect','linkedin') }}" class="bg-white/15 px-4 py-2 rounded-xl hover:bg-white/25 transition">LinkedIn</a>
            </div>
        </div>

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