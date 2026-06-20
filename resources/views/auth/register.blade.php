@extends('layouts.app')

@section('title', 'Criar conta - UNN')

@section('content')
    @php
        $currentReferralCode = trim((string) request()->query('ref', session('affiliate_tracking.current.referral_code', session('social_ref', ''))));
    @endphp
    <div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
        <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 !px-0">
            <x-auth-visual title="Crie sua conta" :show-social="true" context="register">
                Faça parte da comunidade e tenha acesso às mentorias e eventos exclusivos.
            </x-auth-visual>
            <div class="p-10">
                <h3 class="text-3xl font-bold mb-8">Criar conta</h3>

                <x-social-auth-buttons class="mb-8" />

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf
                    @if($currentReferralCode !== '')
                        <input type="hidden" name="ref" value="{{ e($currentReferralCode) }}">
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome completo</label>
                        <input name="name" type="text" required
                            value="{{ old('name') }}" autocomplete="name"
                            class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input name="email" type="email" required autocomplete="email"
                            value="{{ old('email') }}" inputmode="email" spellcheck="false"
                            class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gênero</label>
                        <select name="gender"
                            class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]">
                            <option value="">Selecione...</option>
                            <option value="male">Masculino</option>
                            <option value="female">Feminino</option>
                            <option value="other">Outro</option>
                            <option value="prefer_not_to_say">Prefiro não dizer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Senha</label>
                        <input name="password" type="password" required autocomplete="new-password"
                            class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirmar senha</label>
                        <input name="password_confirmation" type="password" required autocomplete="new-password"
                            class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                    </div>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                        <input name="terms" value="1" type="checkbox" required @checked(old('terms'))
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>
                            Li e aceito os <a href="{{ route('site.termos') }}" target="_blank" class="font-semibold text-blue-700 underline">Termos de Uso</a>,
                            a <a href="{{ route('site.privacidade') }}" target="_blank" class="font-semibold text-blue-700 underline">Política de Privacidade</a>
                            e o <a href="{{ route('site.lgpd') }}" target="_blank" class="font-semibold text-blue-700 underline">Consentimento LGPD</a>.
                        </span>
                    </label>
                    @error('terms')<p class="-mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
                    <button type="submit"
                        class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg">Criar conta</button>
                </form>
                <p class="mt-6 text-center text-sm">Já tem conta? <a href="{{ route('login') }}"
                        class="text-[#7a5af8] font-semibold">Entrar</a></p>
            </div>
        </div>
    </div>
@endsection
