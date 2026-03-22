@extends('layouts.app')

@section('title', $page->get('seo_title', 'Sobre a UNN - União Nacional de Networking'))

@section('content')
@php
    $stats = [
        [
            'value' => $page->get('stat_1_value', '5k+'),
            'label' => $page->get('stat_1_label', 'Empreendedores'),
            'icon' => 'fa-users',
        ],
        [
            'value' => $page->get('stat_3_value', 'R$ 50M+'),
            'label' => $page->get('stat_3_label', 'Negócios gerados'),
            'icon' => 'fa-chart-line',
        ],
        [
            'value' => $page->get('stat_4_value', '200+'),
            'label' => $page->get('stat_4_label', 'Eventos realizados'),
            'icon' => 'fa-calendar-check',
        ],
        [
            'value' => $page->get('stat_2_value', '27'),
            'label' => $page->get('stat_2_label', 'Estados'),
            'icon' => 'fa-map-marked-alt',
        ],
    ];
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50">
    <section class="px-4 pt-10 pb-12 md:px-12 md:pt-24 lg:px-24">
        <div class="mx-auto max-w-7xl">
            <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-12">
                <div>
                    <h1 class="unn-title-gradient unn-title-max mb-4 text-3xl font-black leading-tight sm:text-4xl md:mb-6 md:text-5xl lg:text-6xl">
                        {{ $page->get('hero_title', 'Conheça a') }} <span class="unn-title-gradient">UNN</span>
                    </h1>

                    <p class="mb-8 text-xl leading-relaxed text-gray-600">
                        {{ $page->get('vision', 'A União Nacional de Networking é a maior comunidade de empreendedores do Brasil, conectando pessoas que querem crescer juntas através de parcerias estratégicas e negócios colaborativos.') }}
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('register') }}" class="btn-primary inline-flex items-center gap-2 rounded-full px-8 py-4 font-bold text-white shadow-lg transition hover:shadow-xl">
                            <i class="fas fa-rocket"></i>
                            {{ $page->get('cta_btn_primary', 'Fazer parte') }}
                        </a>

                        <a href="{{ route('manifesto') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-bold text-gray-700 shadow-lg transition hover:shadow-xl">
                            <i class="fas fa-book-open"></i>
                            {{ $page->get('cta_btn_secondary', 'Nosso Manifesto') }}
                        </a>
                    </div>
                </div>

                <div class="relative mt-12 lg:mt-0 flex flex-col justify-center">

                        <div class="mb-8 flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.28em] text-sky-500">UNN em números</p>
                                <h2 class="mt-3 text-3xl font-black text-slate-900 md:text-4xl">Crescimento real em escala nacional</h2>
                                <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-500">
                                    Uma comunidade construída para transformar relacionamento em oportunidade, presença e resultado.
                                </p>
                            </div>

                            <div class="hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#2E3192] to-[#29ABE2] text-white shadow-lg md:flex">
                                <i class="fas fa-chart-pie text-xl"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            @foreach ($stats as $stat)
                                <div class="group rounded-[2rem] border border-slate-200/80 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-sky-200 hover:shadow-xl md:p-10">
                                    <div class="mb-6 flex items-center justify-between gap-3">
                                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-[#2E3192] to-[#29ABE2] text-white shadow-lg shadow-sky-200/60">
                                            <i class="fas {{ $stat['icon'] }} text-lg"></i>
                                        </span>
                                        <span class="h-px flex-1 bg-gradient-to-r from-sky-200 via-slate-200 to-transparent"></span>
                                    </div>

                                    <p class="break-words text-4xl font-black leading-none tracking-tight sm:text-5xl" style="color: var(--unn-azul-1)">
                                        {{ $stat['value'] }}
                                    </p>

                                    <p class="mt-4 text-sm font-medium leading-snug text-slate-500 md:text-base">
                                        {{ $stat['label'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white px-6 py-16 md:px-12 lg:px-24">
        <div class="mx-auto max-w-4xl">
            <h2 class="mb-8 text-center text-3xl font-black text-gray-900 lg:text-4xl">
                {{ $page->get('history_title', 'Nossa História') }}
            </h2>

            <div class="prose prose-lg max-w-none text-gray-600">
                <p class="lead mb-6 text-xl">
                    {{ $page->get('history_lead', 'A UNN nasceu em 2020 com uma missão clara: democratizar o acesso ao networking de qualidade no Brasil.') }}
                </p>
                <p class="mb-6">
                    {{ $page->get('history_p1', 'Fundada por um grupo de empreendedores que acreditavam no poder das conexões humanas, a União Nacional de Networking começou como pequenos encontros presenciais em São Paulo. Em poucos meses, a comunidade cresceu exponencialmente, alcançando empreendedores em todos os estados brasileiros.') }}
                </p>
                <p class="mb-6">
                    {{ $page->get('history_p2', 'Hoje, somos a maior plataforma de networking empresarial do país, com milhares de membros ativos que geram negócios, parcerias e amizades duradouras através da nossa metodologia exclusiva de conexões.') }}
                </p>
            </div>
        </div>
    </section>

    <section class="px-6 py-16 md:px-12 lg:px-24">
        <div class="mx-auto max-w-7xl">
            <h2 class="mb-12 text-center text-3xl font-black text-gray-900 lg:text-4xl">
                {{ $page->get('diff_title', 'O que nos diferencia') }}
            </h2>

            <div class="grid gap-8 md:grid-cols-3">
                <div class="rounded-3xl bg-white p-8 text-center shadow-lg">
                    <div class="btn-primary mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl text-white">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">
                        {{ $page->get('diff_card_1_title', 'Comunidade Selecionada') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ $page->get('diff_card_1_text', 'Todos os membros passam por uma curadoria para garantir a qualidade das conexões.') }}
                    </p>
                </div>

                <div class="rounded-3xl bg-white p-8 text-center shadow-lg">
                    <div class="btn-primary mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl text-white">
                        <i class="fas fa-handshake text-2xl"></i>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">
                        {{ $page->get('diff_card_2_title', 'Conexões Reais') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ $page->get('diff_card_2_text', 'Eventos presenciais e online que geram relacionamentos genuínos e duradouros.') }}
                    </p>
                </div>

                <div class="rounded-3xl bg-white p-8 text-center shadow-lg">
                    <div class="btn-primary mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl text-white">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900">
                        {{ $page->get('diff_card_3_title', 'Resultados Mensuráveis') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ $page->get('diff_card_3_text', 'Acompanhamos e celebramos cada negócio fechado entre nossos membros.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="px-6 py-16 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="mx-auto max-w-4xl text-center text-white">
            <h2 class="mb-4 text-3xl font-black lg:text-4xl">
                {{ $page->get('cta_title', 'Pronto para crescer com a gente?') }}
            </h2>
            <p class="mb-8 text-lg opacity-90">
                {{ $page->get('cta_subtitle', 'Junte-se a milhares de empreendedores que já transformaram suas carreiras.') }}
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-bold transition hover:bg-blue-50" style="color: var(--unn-azul-1)">
                <i class="fas fa-user-plus"></i>
                {{ $page->get('cta_btn', 'Começar agora') }}
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
