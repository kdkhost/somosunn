@extends('layouts.app')

@section('title', 'Criar conta - UNN')

@section('content')
@php
    // Logic inside component
@endphp
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 !px-0">
        <x-auth-visual title="Crie sua conta">
            Faça parte da comunidade e tenha acesso às mentorias e eventos exclusivos.
        </x-auth-visual>
        <div class="p-10">
            <h3 class="text-3xl font-bold mb-8">Criar conta</h3>
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome completo</label>
                    <input name="name" type="text" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input name="email" type="email" required autocomplete="email" class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Senha</label>
                    <input name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirmar senha</label>
                    <input name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg">Criar conta</button>
            </form>
            <p class="mt-6 text-center text-sm">Já tem conta? <a href="{{ route('login') }}" class="text-[#7a5af8] font-semibold">Entrar</a></p>
        </div>
    </div>
</div>
@endsection