@extends('layouts.app')

@section('title', 'Sobre a Comunidade Somos Únicas | UNN')

@section('content')
    <div class="min-h-screen pb-16" style="background: linear-gradient(135deg, #f3e8ff 0%, #ede9fe 50%, #f5f3ff 100%);">
        {{-- Hero Section --}}
        <div class="relative bg-unicas-gradient text-white overflow-hidden shadow-inner">
            <div class="absolute inset-x-0 bottom-0 bg-white"
                style="height: 15%; clip-path: polygon(0 100%, 100% 0, 100% 100%);"></div>
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-32 relative z-10 flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <span
                        class="inline-block py-1 px-3 rounded-full bg-white/20 backdrop-blur-sm text-white font-semibold text-sm mb-4">
                        Comunidade Feminina
                    </span>
                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-6">
                        {{ $pageData['hero_title'] ?? 'Somos Únicas: o seu espaço seguro para crescer' }}
                    </h1>
                    <div class="text-lg md:text-xl text-white/90 mb-8 max-w-lg summernote-content">
                        {!! $pageData['hero_subtitle'] ?? 'Um ambiente exclusivo para mulheres, focado no desenvolvimento humano, networking estratégico e apoio mútuo.' !!}
                    </div>
                    <a href="{{ route('somos-unicas') }}"
                        class="inline-block bg-white text-unicas-main font-bold py-3 px-8 rounded-full shadow-lg hover:bg-white/90 transition transform hover:-translate-y-1">
                        Acessar a Plataforma
                    </a>
                </div>
                <div class="md:w-1/2 flex justify-center">
                    @if(isset($pageData['hero_image']) && !empty($pageData['hero_image']))
                        <img src="{{ Str::startsWith($pageData['hero_image'], ['http://', 'https://']) ? $pageData['hero_image'] : asset('storage/' . $pageData['hero_image']) }}"
                            alt="{{ $pageData['hero_title'] ?? 'Somos Únicas' }}"
                            class="rounded-3xl shadow-2xl transform rotate-3 hover:rotate-0 transition duration-500 max-h-[450px] object-cover border-4 border-white/30">
                    @else
                        <img src="https://placehold.co/500x400/{{ str_replace('#', '', ($pageData['theme_color'] ?? '#db2777')) }}/ffffff?text=Mulheres+Inspiradoras"
                            alt="Comunidade Somos Únicas"
                            class="rounded-2xl shadow-2xl transform rotate-3 hover:rotate-0 transition duration-500">
                    @endif
                </div>
            </div>
        </div>

        {{-- Missão, Visão e Valores --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 border border-gray-100">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Por que criamos a Somos Únicas?</h2>
                    <div class="w-24 h-1 bg-unicas-main mx-auto rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div class="p-6 bg-gray-50 rounded-xl hover:shadow-md transition border border-gray-100">
                        <div
                            class="w-16 h-16 bg-unicas-main/10 text-unicas-main rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Nossa Missão</h3>
                        <p class="text-gray-600">
                            Fortalecer mulheres de negócios, proporcionando acesso a mentorias, conhecimentos e um ambiente
                            acolhedor, longe dos desafios comuns do mercado tradicional.
                        </p>
                    </div>

                    <div class="p-6 bg-gray-50 rounded-xl hover:shadow-md transition border border-gray-100">
                        <div
                            class="w-16 h-16 bg-unicas-main/10 text-unicas-main rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Nossa Visão</h3>
                        <p class="text-gray-600">
                            Ser a maior e mais eficiente comunidade de networking feminino do Brasil, onde cada integrante
                            encontra os recursos necessários para escalar os seus negócios.
                        </p>
                    </div>

                    <div class="p-6 bg-gray-50 rounded-xl hover:shadow-md transition border border-gray-100">
                        <div
                            class="w-16 h-16 bg-unicas-main/10 text-unicas-main rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Nossos Valores</h3>
                        <p class="text-gray-600">
                            Respeito, empatia, colaboração e excelência. Acreditamos que o sucesso de uma beneficia a todas
                            e que juntas vamos mais longe.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- O que você encontra aqui --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-24 pb-20">
            <div class="flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="md:w-1/2">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-unicas-main/10 rounded-3xl transform -rotate-2"></div>
                        @php
                            $netImg = $pageData['networking_image'] ?? null;
                            $networkingImageUrl = $netImg
                                ? (Str::startsWith($netImg, ['http://', 'https://']) ? $netImg : asset('storage/' . $netImg))
                                : "https://placehold.co/600x500/" . str_replace('#', '', ($pageData['theme_color'] ?? '#6d28d9')) . "11/" . str_replace('#', '', ($pageData['theme_color'] ?? '#6d28d9')) . "?text=Networking+e+Apoio";
                        @endphp
                        <img src="{{ $networkingImageUrl }}" alt="Networking Feminino"
                            class="relative rounded-2xl shadow-lg w-full object-cover border-4 border-white">
                    </div>
                </div>
                <div class="md:w-1/2">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 font-black uppercase tracking-tight">Uma comunidade
                        pensada para as suas necessidades</h2>
                    <ul class="space-y-6">
                        <li class="flex items-start">
                            <div
                                class="flex-shrink-0 w-12 h-12 rounded-2xl bg-unicas-main/10 flex items-center justify-center text-unicas-main mt-1 shadow-sm">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-xl font-bold text-gray-800">Eventos Exclusivos</h4>
                                <p class="text-gray-600 mt-1">Encontros presenciais e virtuais planejados especificamente
                                    para discussões que impactam diretamente a jornada feminina no empreendedorismo.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div
                                class="flex-shrink-0 w-12 h-12 rounded-2xl bg-unicas-main/10 flex items-center justify-center text-unicas-main mt-1 shadow-sm">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-xl font-bold text-gray-800">Cursos e Trilhas</h4>
                                <p class="text-gray-600 mt-1">Conteúdos educacionais gravados por especialistas para ajudar
                                    no desenvolvimento de habilidades técnicas e socioemocionais (soft skills).</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div
                                class="flex-shrink-0 w-12 h-12 rounded-2xl bg-unicas-main/10 flex items-center justify-center text-unicas-main mt-1 shadow-sm">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-xl font-bold text-gray-800">Mentorias Dirigidas</h4>
                                <p class="text-gray-600 mt-1">Acesso a profissionais consagradas dispostas a compartilhar
                                    suas histórias, métodos e dar orientações para o seu crescimento.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --unicas-main:
                {{ $pageData['theme_color'] ?? '#db2777' }}
            ;
        }

        .text-unicas-main {
            color: var(--unicas-main) !important;
        }

        .bg-unicas-main {
            background-color: var(--unicas-main) !important;
        }

        .bg-unicas-main\/10 {
            background-color:
                {{ ($pageData['theme_color'] ?? '#db2777') }}
                1a !important;
        }

        .unicas-title-gradient {
            background: linear-gradient(90deg, #6d28d9 0%, #7c3aed 60%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
            display: inline-block;
        }

        .bg-unicas-gradient {
            background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 50%, #7c3aed 100%);
        }

        .summernote-content p {
            margin-bottom: 1rem;
        }
    </style>
@endsection