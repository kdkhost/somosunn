@extends('layouts.app')

@section('title', $page->get('seo_title', 'Quem Somos - Equipe UNN'))

@php
    use App\Support\UploadStorage;

    $coverPath = (string) ($page->get('cover_image') ?? '');
    $coverUrl = $coverPath !== '' ? UploadStorage::url($coverPath) : null;

    $fundadores = $page->get('founders', [
        ['name' => 'Ricardo Andrade', 'role' => 'CEO & Co-Fundador', 'bio' => 'Empreendedor serial com exits em 3 startups. Acredita no poder transformador das conexoes humanas.', 'initials' => 'RA', 'image' => ''],
        ['name' => 'Patricia Lima', 'role' => 'COO & Co-Fundadora', 'bio' => 'Especialista em operacoes e escalabilidade. Ex-executiva de grandes corporacoes.', 'initials' => 'PL', 'image' => ''],
        ['name' => 'Marcos Teixeira', 'role' => 'CTO & Co-Fundador', 'bio' => 'Engenheiro de software com 20 anos de experiencia. Apaixonado por tecnologia e inovacao.', 'initials' => 'MT', 'image' => ''],
    ]);

    $equipe = $page->get('team', [
        ['name' => 'Camila Rocha', 'role' => 'Head de Comunidade', 'initials' => 'CR', 'image' => ''],
        ['name' => 'Bruno Dias', 'role' => 'Head de Eventos', 'initials' => 'BD', 'image' => ''],
        ['name' => 'Larissa Costa', 'role' => 'Head de Marketing', 'initials' => 'LC', 'image' => ''],
        ['name' => 'Gabriel Santos', 'role' => 'Head de Parcerias', 'initials' => 'GS', 'image' => ''],
        ['name' => 'Fernanda Alves', 'role' => 'Head de Conteudo', 'initials' => 'FA', 'image' => ''],
        ['name' => 'Lucas Pereira', 'role' => 'Head de Tecnologia', 'initials' => 'LP', 'image' => ''],
    ]);
@endphp

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <section class="pt-8 md:pt-16 pb-10 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                <span class="unn-title-gradient">Quem</span> Somos
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                {{ $page->get('hero_subtitle', 'Conheca as pessoas por tras da maior comunidade de networking do Brasil.') }}
            </p>
            @if($coverUrl)
                <div class="mt-8 md:mt-10">
                    <img src="{{ $coverUrl }}"
                         alt="Quem Somos - UNN"
                         class="w-full max-w-5xl mx-auto rounded-3xl shadow-2xl object-cover border border-white/40"
                         style="max-height: 340px; display: block;">
                </div>
            @endif
        </div>
    </section>

    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-12 text-center">{{ $page->get('founders_title', 'Fundadores') }}</h2>

            <div class="flex flex-wrap justify-center gap-8">
                @foreach($fundadores as $fundador)
                @php
                    $founderImagePath = (string) ($fundador['image'] ?? '');
                    $founderImageUrl = $founderImagePath !== '' ? UploadStorage::url($founderImagePath) : null;
                    $founderInitials = strtoupper(substr((string) ($fundador['initials'] ?? 'F'), 0, 2));
                @endphp
                <div class="w-full max-w-[420px] md:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.34rem)] bg-white rounded-3xl shadow-lg overflow-hidden text-center h-[360px] border border-slate-100 flex flex-col">
                    <div class="h-24 btn-primary"></div>
                    <div class="flex justify-center -mt-12">
                        <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-slate-100 flex items-center justify-center text-white text-2xl font-bold overflow-hidden">
                            @if($founderImageUrl)
                                <img src="{{ $founderImageUrl }}" alt="{{ $fundador['name'] ?? 'Fundador' }}" class="w-full h-full object-cover">
                            @else
                                <span class="w-full h-full btn-primary flex items-center justify-center">{{ $founderInitials }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $fundador['name'] ?? '-' }}</h3>
                        <p class="text-sm mb-3 font-semibold founder-role" style="color: var(--unn-azul-1)">{{ $fundador['role'] ?? '-' }}</p>
                        <p class="text-gray-600 text-sm leading-6 founder-bio">{{ $fundador['bio'] ?? '' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-12 text-center">{{ $page->get('team_title', 'Nossa Equipe') }}</h2>

            <div class="flex flex-wrap justify-center gap-6">
                @foreach($equipe as $membro)
                @php
                    $memberImagePath = (string) ($membro['image'] ?? '');
                    $memberImageUrl = $memberImagePath !== '' ? UploadStorage::url($memberImagePath) : null;
                    $memberInitials = strtoupper(substr((string) ($membro['initials'] ?? 'M'), 0, 2));
                @endphp
                <div class="w-[148px] md:w-[160px] text-center bg-white rounded-2xl border border-slate-100 shadow-sm p-4 h-[200px] flex flex-col items-center">
                    <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center text-white text-xl font-bold mx-auto mb-3 overflow-hidden">
                        @if($memberImageUrl)
                            <img src="{{ $memberImageUrl }}" alt="{{ $membro['name'] ?? 'Membro' }}" class="w-full h-full object-cover">
                        @else
                            <span class="w-full h-full btn-primary flex items-center justify-center">{{ $memberInitials }}</span>
                        @endif
                    </div>
                    <h4 class="font-bold text-gray-900 text-sm leading-5 team-name">{{ $membro['name'] ?? '-' }}</h4>
                    <p class="text-xs text-gray-500 leading-5 team-role">{{ $membro['role'] ?? '-' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 md:mb-12 text-center">{{ $page->get('stats_title', 'UNN em Numeros') }}</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                    <p class="text-3xl md:text-5xl font-black" style="color: var(--unn-azul-1)">{{ $page->get('stat_1_value', '15') }}</p>
                    <p class="text-xs md:text-base text-gray-500 mt-2">{{ $page->get('stat_1_label', 'Colaboradores') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                    <p class="text-3xl md:text-5xl font-black" style="color: var(--unn-azul-1)">{{ $page->get('stat_2_value', '4') }}</p>
                    <p class="text-xs md:text-base text-gray-500 mt-2">{{ $page->get('stat_2_label', 'Anos de historia') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                    <p class="text-3xl md:text-5xl font-black" style="color: var(--unn-azul-1)">{{ $page->get('stat_3_value', '5k+') }}</p>
                    <p class="text-xs md:text-base text-gray-500 mt-2">{{ $page->get('stat_3_label', 'Membros atendidos') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-8 text-center shadow-lg">
                    <p class="text-3xl md:text-5xl font-black truncate" style="color: var(--unn-azul-1)">{{ $page->get('stat_4_value', '100%') }}</p>
                    <p class="text-xs md:text-base text-gray-500 mt-2">{{ $page->get('stat_4_label', 'Dedicacao') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">{{ $page->get('cta_title', 'Quer fazer parte do time?') }}</h2>
            <p class="text-lg opacity-90 mb-8">{{ $page->get('cta_subtitle', 'Estamos sempre em busca de talentos que compartilham nossa visao.') }}</p>
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
.founder-role {
    min-height: 28px;
}
.founder-bio {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.team-name {
    min-height: 40px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.team-role {
    min-height: 38px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
