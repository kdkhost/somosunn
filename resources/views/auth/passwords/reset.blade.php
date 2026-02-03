@extends('layouts.app')

@section('title', 'Resetar senha - UNN')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8">
        <h3 class="text-2xl font-bold mb-4">Resetar senha</h3>
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token ?? '' }}">
            <div>
                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                <input name="email" type="email" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nova senha</label>
                <input name="password" type="password" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirmar senha</label>
                <input name="password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
            </div>
            <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold">Alterar senha</button>
        </form>
    </div>
</div>
@endsection
