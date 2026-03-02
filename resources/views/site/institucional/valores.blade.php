@extends('layouts.app')

@section('title', $page->get('seo_title', 'Nossos Valores - UNN'))

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                Nossos <span class="unn-title-gradient">Valores</span>
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                {{ $page->get('hero_subtitle', 'Os princÃ­pios que guiam tudo o que fazemos na UNN.') }}
            </p>
        </div>
    </section>

    <!-- Values Grid -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-8">
                @php
                    $valores = $page->get('values', [
                        ['icon' => 'fa-heart',         'title' => 'ConfianÃ§a',    'text' => 'A base de qualquer relacionamento duradouro. Cultivamos um ambiente onde a palavra tem valor e os compromissos sÃ£o honrados. ConfianÃ§a nÃ£o se exige, se constrÃ³i.',                                'quote' => 'ConfianÃ§a Ã© a cola invisÃ­vel que mantÃ©m as parcerias unidas.'],
                        ['icon' => 'fa-hands-helping', 'title' => 'Generosidade', 'text' => 'O verdadeiro networking comeÃ§a quando vocÃª se pergunta: "Como posso ajudar?". Acreditamos que dar sem esperar nada em troca cria as conexÃµes mais poderosas.',                              'quote' => 'Quem planta conexÃµes, colhe oportunidades.'],
                        ['icon' => 'fa-lightbulb',     'title' => 'InovaÃ§Ã£o',     'text' => 'Nunca paramos de evoluir. Buscamos constantemente novas formas de conectar pessoas e gerar valor. A zona de conforto nÃ£o Ã© lugar para empreendedores.',                                    'quote' => 'Inovar Ã© ver o que todos veem e pensar o que ninguÃ©m pensou.'],
                        ['icon' => 'fa-trophy',        'title' => 'ExcelÃªncia',   'text' => 'Fazemos o nosso melhor em tudo. Cada evento, cada interaÃ§Ã£o, cada detalhe Ã© pensado para proporcionar a melhor experiÃªncia possÃ­vel aos nossos membros.',                                 'quote' => 'ExcelÃªncia nÃ£o Ã© um ato, Ã© um hÃ¡bito.'],
                        ['icon' => 'fa-user-shield',   'title' => 'Integridade',  'text' => 'Fazemos o que Ã© certo, mesmo quando ninguÃ©m estÃ¡ olhando. A Ã©tica nos negÃ³cios nÃ£o Ã© opcional, Ã© fundamental. Nossos membros sÃ£o selecionados por seu carÃ¡ter.',                       'quote' => 'O carÃ¡ter se revela nas pequenas decisÃµes do dia a dia.'],
                        ['icon' => 'fa-users',         'title' => 'Comunidade',   'text' => 'Somos mais fortes juntos. A UNN nÃ£o Ã© apenas uma plataforma, Ã© uma famÃ­lia de empreendedores que se apoiam mutuamente nos desafios e celebram as vitÃ³rias um do outro.', 'quote' => 'Sozinhos vamos mais rÃ¡pido. Juntos vamos mais longe.'],
                    ]);
                @endphp

                @foreach ($valores as $valor)
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-lg">
                    <div class="flex flex-col md:flex-row items-center md:items-start gap-4 md:gap-6 text-center md:text-left">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center shrink-0">
                            <i class="fas {{ $valor['icon'] }} text-white text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ $valor['title'] }}</h3>
                            <p class="text-gray-600 mb-4">{{ $valor['text'] }}</p>
                            <p class="text-sm italic" style="color: var(--unn-azul-1)">"{{ $valor['quote'] }}"</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Quote Section -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-4xl mx-auto text-center">
            <i class="fas fa-quote-left text-6xl mb-8" style="color: var(--unn-azul-1); opacity: 0.3"></i>
            <blockquote class="text-3xl font-bold text-gray-900 mb-6">
                "{{ $page->get('blockquote_text', 'Valores nÃ£o sÃ£o apenas palavras bonitas na parede. SÃ£o os critÃ©rios pelos quais tomamos cada decisÃ£o, grandes ou pequenas, todos os dias.') }}"
            </blockquote>
            <p class="text-gray-500">{{ $page->get('blockquote_author', 'â€” Equipe Fundadora UNN') }}</p>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">{{ $page->get('cta_title', 'Compartilha desses valores?') }}</h2>
            <p class="text-lg opacity-90 mb-8">{{ $page->get('cta_subtitle', 'VocÃª estÃ¡ no lugar certo. FaÃ§a parte da nossa comunidade.') }}</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-handshake"></i>
                {{ $page->get('cta_btn', 'Fazer parte') }}
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
