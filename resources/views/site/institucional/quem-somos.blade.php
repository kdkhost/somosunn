@extends('layouts.app')

@section('title', $page->get('seo_title', 'Quem Somos - Equipe UNN'))

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                <span class="unn-title-gradient">Quem</span> Somos
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                {{ $page->get('hero_subtitle', 'Conheça as pessoas por trás da maior comunidade de networking do Brasil.') }}
            </p>
        </div>
    </section>

    <!-- Fundadores -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-12 text-center">{{ $page->get('founders_title', 'Fundadores') }}</h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                    $fundadores = $page->get('founders', [
                        ['name' => 'Ricardo Andrade', 'role' => 'CEO & Co-Fundador', 'bio' => 'Empreendedor serial com exits em 3 startups. Acredita no poder transformador das conexões humanas.', 'initials' => 'RA'],
                        ['name' => 'Patrícia Lima', 'role' => 'COO & Co-Fundadora', 'bio' => 'Especialista em operações e escalabilidade. Ex-executiva de grandes corporações.', 'initials' => 'PL'],
                        ['name' => 'Marcos Teixeira', 'role' => 'CTO & Co-Fundador', 'bio' => 'Engenheiro de software com 20 anos de experiência. Apaixonado por tecnologia e inovação.', 'initials' => 'MT'],
                    ]);
                @endphp
                
                @foreach($fundadores as $fundador)
                <div class="bg-white rounded-3xl shadow-lg overflow-hidden text-center">
                    <div class="h-24 btn-primary"></div>
                    <div class="flex justify-center -mt-12">
                        <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg btn-primary flex items-center justify-center text-white text-2xl font-bold">
                            {{ $fundador['initials'] }}
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $fundador['name'] }}</h3>
                        <p class="text-sm mb-3" style="color: var(--unn-azul-1)">{{ $fundador['role'] }}</p>
                        <p class="text-gray-600 text-sm">{{ $fundador['bio'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Equipe -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-12 text-center">{{ $page->get('team_title', 'Nossa Equipe') }}</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @php
                    $equipe = $page->get('team', [
                        ['name' => 'Camila Rocha', 'role' => 'Head de Comunidade', 'initials' => 'CR'],
                        ['name' => 'Bruno Dias', 'role' => 'Head de Eventos', 'initials' => 'BD'],
                        ['name' => 'Larissa Costa', 'role' => 'Head de Marketing', 'initials' => 'LC'],
                        ['name' => 'Gabriel Santos', 'role' => 'Head de Parcerias', 'initials' => 'GS'],
                        ['name' => 'Fernanda Alves', 'role' => 'Head de Conteúdo', 'initials' => 'FA'],
                        ['name' => 'Lucas Pereira', 'role' => 'Head de Tecnologia', 'initials' => 'LP'],
                    ]);
                @endphp
                
                @foreach($equipe as $membro)
                <div class="text-center">
                    <div class="w-20 h-20 rounded-full btn-primary flex items-center justify-center text-white text-xl font-bold mx-auto mb-3">
                        {{ $membro['initials'] }}
                    </div>
                    <h4 class="font-bold text-gray-900 text-sm">{{ $membro['name'] }}</h4>
                    <p class="text-xs text-gray-500">{{ $membro['role'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Números -->
    <section class="py-16 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 md:mb-12 text-center">{{ $page->get('stats_title', 'UNN em Números') }}</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                    <p class="text-3xl md:text-5xl font-black" style="color: var(--unn-azul-1)">{{ $page->get('stat_1_value', '15') }}</p>
                    <p class="text-xs md:text-base text-gray-500 mt-2">{{ $page->get('stat_1_label', 'Colaboradores') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                    <p class="text-3xl md:text-5xl font-black" style="color: var(--unn-azul-1)">{{ $page->get('stat_2_value', '4') }}</p>
                    <p class="text-xs md:text-base text-gray-500 mt-2">{{ $page->get('stat_2_label', 'Anos de história') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                    <p class="text-3xl md:text-5xl font-black" style="color: var(--unn-azul-1)">{{ $page->get('stat_3_value', '5k+') }}</p>
                    <p class="text-xs md:text-base text-gray-500 mt-2">{{ $page->get('stat_3_label', 'Membros atendidos') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                    <p class="text-3xl md:text-5xl font-black truncate" style="color: var(--unn-azul-1)">{{ $page->get('stat_4_value', '100%') }}</p>
                    <p class="text-xs md:text-base text-gray-500 mt-2">{{ $page->get('stat_4_label', 'Dedicação') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">{{ $page->get('cta_title', 'Quer fazer parte do time?') }}</h2>
            <p class="text-lg opacity-90 mb-8">{{ $page->get('cta_subtitle', 'Estamos sempre em busca de talentos que compartilham nossa visão.') }}</p>
            <a href="{{ route('contato') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-envelope"></i>
                {{ $page->get('cta_btn', 'Entre em contato') }}
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
