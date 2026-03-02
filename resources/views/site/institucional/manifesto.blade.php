@extends('layouts.app')

@section('title', $page->get('seo_title', 'Manifesto UNN - Nossa VisÃ£o'))

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                {{ $page->get('hero_title', 'Nosso') }} <span class="unn-title-gradient">{{ $page->get('hero_title_highlight', 'Manifesto') }}</span>
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 leading-relaxed">
                {{ $page->get('hero_subtitle', 'O que acreditamos e por que existimos.') }}
            </p>
        </div>
    </section>

    <!-- Manifesto Content -->
    <section class="pb-16 px-4 md:px-12 lg:px-24">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-3xl shadow-2xl p-6 md:p-12">
                <article class="prose prose-lg max-w-none">
                    @if($page->get('body'))
                        {!! $page->get('body') !!}
                    @else
                    <p class="text-2xl font-bold mb-8" style="color: var(--unn-azul-1)">
                        "{{ $page->get('quote_top', 'Acreditamos que ninguÃ©m cresce sozinho.') }}"
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">{{ $page->get('section_1_title', 'Sobre ColaboraÃ§Ã£o') }}</h2>
                    <p class="text-gray-600 mb-6">
                        {{ $page->get('section_1_text', 'Em um mundo que celebra o individualismo, nÃ³s escolhemos o caminho da colaboraÃ§Ã£o. Sabemos que os maiores negÃ³cios nascem de parcerias sÃ³lidas, construÃ­das sobre confianÃ§a e propÃ³sito compartilhado.') }}
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">{{ $page->get('section_2_title', 'Sobre AbundÃ¢ncia') }}</h2>
                    <p class="text-gray-600 mb-6">
                        {{ $page->get('section_2_text', 'Rejeitamos a mentalidade de escassez. HÃ¡ espaÃ§o para todos crescerem. Quando um membro prospera, a comunidade inteira se fortalece. O sucesso do outro nÃ£o Ã© ameaÃ§a â€” Ã© inspiraÃ§Ã£o.') }}
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">{{ $page->get('section_3_title', 'Sobre Autenticidade') }}</h2>
                    <p class="text-gray-600 mb-6">
                        {{ $page->get('section_3_text', 'Valorizamos pessoas reais, com histÃ³rias reais. Aqui nÃ£o hÃ¡ espaÃ§o para mÃ¡scaras ou personagens. As conexÃµes mais poderosas nascem quando nos mostramos vulnerÃ¡veis e autÃªnticos.') }}
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">{{ $page->get('section_4_title', 'Sobre Impacto') }}</h2>
                    <p class="text-gray-600 mb-6">
                        {{ $page->get('section_4_text', 'NÃ£o buscamos apenas lucro. Acreditamos que empreendedores tÃªm o poder de transformar a sociedade. Cada negÃ³cio bem-sucedido gera empregos, melhora vidas e inspira outros a seguirem o mesmo caminho.') }}
                    </p>

                    <h2 class="text-2xl font-black text-gray-900 mt-8 mb-4">{{ $page->get('section_5_title', 'Nossa Promessa') }}</h2>
                    <p class="text-gray-600 mb-6">
                        {{ $page->get('section_5_text', 'Prometemos criar o ambiente ideal para que vocÃª encontre as pessoas certas, no momento certo. Prometemos ser facilitadores de conexÃµes genuÃ­nas que geram valor real. Prometemos nunca perder a essÃªncia do que nos fez comeÃ§ar: a crenÃ§a inabalÃ¡vel no poder das pessoas.') }}
                    </p>

                    <div class="mt-12 p-8 rounded-2xl text-center" style="background: linear-gradient(135deg, var(--unn-azul-1)10, var(--unn-azul-3)10)">
                        <p class="text-xl font-bold text-gray-900 mb-2">
                            "{{ $page->get('quote_bottom', 'Sozinhos vamos mais rÃ¡pido. Juntos vamos mais longe.') }}"
                        </p>
                        <p class="text-gray-500">{{ $page->get('quote_author', 'â€” Filosofia UNN') }}</p>
                    </div>
                    @endif
                </article>
            </div>
        </div>
    </section>

    <!-- Values Preview -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto text-center">
            <h2 class="text-3xl font-black text-gray-900 mb-8">{{ $page->get('pillars_title', 'Nossos Pilares') }}</h2>
            <div class="grid md:grid-cols-4 gap-6">
                <div class="p-6">
                    <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-heart text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">{{ $page->get('pillar_1_title', 'ConfianÃ§a') }}</h3>
                </div>
                <div class="p-6">
                    <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-hands-helping text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">{{ $page->get('pillar_2_title', 'Generosidade') }}</h3>
                </div>
                <div class="p-6">
                    <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lightbulb text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">{{ $page->get('pillar_3_title', 'InovaÃ§Ã£o') }}</h3>
                </div>
                <div class="p-6">
                    <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trophy text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">{{ $page->get('pillar_4_title', 'ExcelÃªncia') }}</h3>
                </div>
            </div>
            <a href="{{ route('valores') }}" class="btn-primary text-white px-8 py-3 rounded-full font-semibold inline-flex items-center gap-2 mt-8">
                {{ $page->get('pillars_link_text', 'Conhecer nossos valores') }} <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">{{ $page->get('cta_title', 'Se identificou com nossa visÃ£o?') }}</h2>
            <p class="text-lg opacity-90 mb-8">{{ $page->get('cta_subtitle', 'FaÃ§a parte de uma comunidade que pensa como vocÃª.') }}</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-rocket"></i>
                {{ $page->get('cta_btn', 'Quero fazer parte') }}
            </a>
        </div>
    </section>
</div>

<style>
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
