@extends('layouts.app')

@section('title', 'Verifique seu e-mail - UNN')

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-[70vh] flex items-center py-12 px-4">
    <div class="unn-container">
        <div class="max-w-xl mx-auto">
            <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12 text-center border border-slate-100">
                {{-- Icone --}}
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-envelope-open-text text-3xl text-blue-600"></i>
                </div>

                <h1 class="text-3xl font-black text-slate-900 mb-4">Verifique seu e-mail</h1>
                
                <p class="text-gray-600 leading-relaxed mb-8">
                    Obrigado por se juntar à UNN! Antes de começar, por favor, clique no link que acabamos de enviar para o seu e-mail. Caso não tenha recebido, ficaremos felizes em enviar outro.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-medium animate-bounce">
                        <i class="fas fa-check-circle mr-2"></i>
                        Um novo link de verificação foi enviado para o endereço de e-mail fornecido.
                    </div>
                @endif

                <div class="flex flex-col gap-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full py-4 rounded-xl text-white font-bold shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Reenviar e-mail de confirmação
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-slate-600 text-sm font-semibold transition">
                            Sair da conta
                        </button>
                    </form>
                </div>
            </div>
            
            <p class="text-center text-slate-400 text-xs mt-8">
                &copy; {{ date('Y') }} UNN Community. Todos os direitos reservados.
            </p>
        </div>
    </div>
</div>
@endsection
