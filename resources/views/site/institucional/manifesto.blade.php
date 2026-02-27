@extends('layouts.app')

@section('title', 'Manifesto UNN - Nossa Visão')

@section('content')
@php
    $manifestoContent = \App\Models\SiteContent::getValue('about', 'manifesto');
@endphp
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                Nosso <span class="unn-title-gradient">Manifesto</span>
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 leading-relaxed">
                O que acreditamos e por que existimos.
            </p>
        </div>
    </section>

    <!-- Manifesto Content -->
    <section class="pb-16 px-4 md:px-12 lg:px-24">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-3xl shadow-2xl p-6 md:p-12">
                <article class="prose prose-lg max-w-none">
                    @if(!empty($manifestoContent))
                        {!! $manifestoContent !!}
                    @else
                    <p class="text-2xl font-bold mb-8" style="color: var(--unn-azul-1)">
                        "Acreditamos que ninguém cresce sozinho."
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">Sobre Colaboração</h2>
                    <p class="text-gray-600 mb-6">
                        Em um mundo que celebra o individualismo, nós escolhemos o caminho da colaboração. 
                        Sabemos que os maiores negócios nascem de parcerias sólidas, construídas sobre confiança e propósito compartilhado.
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">Sobre Abundância</h2>
                    <p class="text-gray-600 mb-6">
                        Rejeitamos a mentalidade de escassez. Há espaço para todos crescerem. 
                        Quando um membro prospera, a comunidade inteira se fortalece. 
                        O sucesso do outro não é ameaça — é inspiração.
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">Sobre Autenticidade</h2>
                    <p class="text-gray-600 mb-6">
                        Valorizamos pessoas reais, com histórias reais. Aqui não há espaço para máscaras ou personagens. 
                        As conexões mais poderosas nascem quando nos mostramos vulneráveis e autênticos.
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">Sobre Impacto</h2>
                    <p class="text-gray-600 mb-6">
                        Não buscamos apenas lucro. Acreditamos que empreendedores têm o poder de transformar a sociedade. 
                        Cada negócio bem-sucedido gera empregos, melhora vidas e inspira outros a seguirem o mesmo caminho.
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">Nossa Promessa</h2>
                    <p class="text-gray-600 mb-6">
                        Prometemos criar o ambiente ideal para que você encontre as pessoas certas, no momento certo. 
                        Prometemos ser facilitadores de conexões genuínas que geram valor real. 
                        Prometemos nunca perder a essência do que nos fez começar: a crença inabalável no poder das pessoas.
                    </p>

                    <div class="mt-12 p-8 rounded-2xl text-center" style="background: linear-gradient(135deg, var(--unn-azul-1)10, var(--unn-azul-3)10)">
                        <p class="text-xl font-bold text-gray-900 mb-2">
                            "Sozinhos vamos mais rápido. Juntos vamos mais longe."
                        </p>
                        <p class="text-gray-500">— Filosofia UNN</p>
                    </div>
                @endif
                </article>
            </div>
        </div>
    </section>

    <!-- Values Preview -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto text-center">
            <h2 class="text-3xl font-black text-gray-900 mb-8">Nossos Pilares</h2>
            <div class="grid md:grid-cols-4 gap-6">
                <div class="p-6">
                    <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-heart text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">Confiança</h3>
                </div>
                <div class="p-6">
                    <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-hands-helping text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">Generosidade</h3>
                </div>
                <div class="p-6">
                    <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lightbulb text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">Inovação</h3>
                </div>
                <div class="p-6">
                    <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trophy text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">Excelência</h3>
                </div>
            </div>
            <a href="{{ route('valores') }}" class="btn-primary text-white px-8 py-3 rounded-full font-semibold inline-flex items-center gap-2 mt-8">
                Conhecer nossos valores <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">Se identificou com nossa visão?</h2>
            <p class="text-lg opacity-90 mb-8">Faça parte de uma comunidade que pensa como você.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-rocket"></i>
                Quero fazer parte
            </a>
        </div>
    </section>
</div>

<style>
.text-gradient {
    background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.unn-title-gradient {
    background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
}
.unn-title-max {
    max-width: 700px;
    word-break: break-word;
    margin-left: auto;
    margin-right: auto;
}
@media (max-width: 640px) {
    .unn-title-max {
        font-size: 2.2rem !important;
        max-width: 95vw;
    }
}
</style>
@endsection
