{{-- /**
 * Sistema UNN - Instalador concluído
 *
 * Autor: George Marcelo (KDKHOST SOLUÇÕES)
 * Telefone: +55 (21) 98132-5441
 * Telegram: https://t.me/MARCELO_BRAD
 *
 * Copyright (c) 2026 Kdkhost Soluções. Todos os direitos reservados.
 *
 * AVISO LEGAL:
 * Este software e seu código-fonte são propriedade intelectual de kdkhost soluções.
 * É proibida a reprodução, distribuição, modificação, engenharia reversa ou uso não autorizado,
 * total ou parcial, sem autorização prévia e por escrito.
 *
 * Contato: contato@kdkhost.com.br
 * Licenciamento: Uso restrito conforme contrato/termos aplicáveis.
 */ --}}
@extends('layouts.app')

@section('title','Instalação concluída - UNN')

@section('content')
<section class="min-h-screen w-full bg-gradient-to-b from-slate-900 via-purple-900 to-black text-white overflow-hidden relative">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute h-60 w-60 bg-white/10 rounded-full blur-3xl -top-20 left-10 animate-spin-slow"></div>
        <div class="absolute h-40 w-40 bg-blue-500/20 blur-3xl bottom-10 right-16"></div>
        <div class="absolute inset-x-0 top-1/2 h-1 bg-gradient-to-r from-purple-500 via-transparent to-blue-500 opacity-60 blur-xl"></div>
    </div>

    <div class="max-w-5xl mx-auto px-6 py-24 relative z-10 space-y-10">
        <div class="text-center space-y-4">
            <p class="text-sm uppercase tracking-[0.5em] text-purple-300">Instalação completada</p>
            <h1 class="text-4xl md:text-5xl font-black leading-tight">Bem-vindo ao ecossistema UNN</h1>
            <p class="text-gray-300">O portal agora compartilha o mesmo banco de dados do site, os rankings estão prontos e o superadmin <strong>{{ $admin->email }}</strong> já tem acesso total.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            @foreach([
                ['title'=>'Pré-verificações', 'detail'=>'Requisitos mínimos registrados e ambiente preparado.', 'icon'=>'fas fa-check-circle'],
                ['title'=>'Superadmin ativo', 'detail'=>'Senha criptografada, nível sucesso e acesso ao painel.', 'icon'=>'fas fa-user-shield'],
                ['title'=>'Ranking ligado', 'detail'=>'As pesquisas agora alimentam o leaderboard automaticamente.', 'icon'=>'fas fa-medal'],
            ] as $item)
                <article class="bg-white/10 border border-white/10 rounded-3xl p-6 backdrop-blur shadow-2xl hover:border-purple-300 transition">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-600 to-blue-600 flex items-center justify-center text-lg">
                            <i class="{{ $item['icon'] }}"></i>
                        </span>
                        <h3 class="text-lg font-semibold">{{ $item['title'] }}</h3>
                    </div>
                    <p class="text-sm text-gray-300">{{ $item['detail'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="bg-white/10 border border-white/20 rounded-3xl p-8 backdrop-blur space-y-6">
            <h2 class="text-2xl font-bold">O que vem a seguir?</h2>
            <div class="grid md:grid-cols-3 gap-4 text-sm text-gray-300">
                <div class="space-y-2">
                    <p class="font-semibold text-white">Dashboard</p>
                    <p>Login com o superadmin e navegação pelos módulos de cursos, mentorias e pontos.</p>
                </div>
                <div class="space-y-2">
                    <p class="font-semibold text-white">Integrações</p>
                    <p>Teste os webhooks MercadoPago/PagSeguro e monitore os logs em storage/logs/laravel.log.</p>
                </div>
                <div class="space-y-2">
                    <p class="font-semibold text-white">Portal</p>
                    <p>Enriqueça eventos gratuitos e mentorias pagas para alimentar o novo ranking de networking digital.</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-3xl p-8 text-center shadow-2xl animate-bounce-slow">
            <p class="uppercase text-xs tracking-[0.8em] text-white/80">Passo final</p>
            <p class="text-2xl font-black">Portal + Backend unificados</p>
            <a href="/" class="inline-flex items-center gap-2 justify-center mt-4 px-6 py-3 rounded-full bg-white text-purple-700 font-semibold shadow-lg">Ir para o site <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="rounded-3xl border border-white/20 bg-black/50 p-6 text-xs text-gray-400 space-y-2">
            <p>© {{ date('Y') }} Kdkhost Soluções. Todos os direitos reservados.</p>
            <p>Desenvolvido por George Marcelo (KDKHOST SOLUÇÕES) · Contato: <a href="mailto:contato@kdkhost.com.br" class="text-white underline">contato@kdkhost.com.br</a> · Telegram: <a href="https://t.me/MARCELO_BRAD" class="text-white underline">@MARCELO_BRAD</a></p>
            <p>Uso restrito conforme contrato/termos aplicáveis. AVISO LEGAL: reprodução ou engenharia reversa não autorizada é proibida.</p>
        </div>
    </div>
</section>

<style>
    .animate-spin-slow {
        animation: spin 18s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-bounce-slow {
        animation: bounce 4s ease-in-out infinite;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }
</style>

@endsection
