@extends('layouts.app')

@section('title', 'Sobre a Comunidade Somos Únicas | UNN')

@section('content')
    <div class="bg-pink-50 min-h-screen pb-16">
        {{-- Hero Section --}}
        <div class="relative bg-gradient-to-r from-pink-500 to-pink-700 text-white overflow-hidden">
            <div class="absolute inset-x-0 bottom-0 bg-white"
                style="height: 15%; clip-path: polygon(0 100%, 100% 0, 100% 100%);"></div>
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-32 relative z-10 flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <span class="inline-block py-1 px-3 rounded-full bg-pink-100 text-pink-600 font-semibold text-sm mb-4">
                        Comunidade Feminina
                    </span>
                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-6">
                        Somos Únicas: o seu espaço seguro para crescer
                    </h1>
                    <p class="text-lg md:text-xl text-pink-100 mb-8 max-w-lg">
                        Um ambiente exclusivo para mulheres, focado no desenvolvimento humano, networking estratégico e
                        apoio mútuo.
                    </p>
                    <a href="{{ route('somos-unicas') }}"
                        class="inline-block bg-white text-pink-600 font-bold py-3 px-8 rounded-full shadow-lg hover:bg-pink-50 transition transform hover:-translate-y-1">
                        Acessar a Plataforma
                    </a>
                </div>
                <div class="md:w-1/2 flex justify-center">
                    {{-- Mockup image illustration --}}
                    <img src="https://placehold.co/500x400/ec4899/ffffff?text=Mulheres+Inspiradoras"
                        alt="Comunidade Somos Únicas"
                        class="rounded-2xl shadow-2xl transform rotate-3 hover:rotate-0 transition duration-500">
                </div>
            </div>
        </div>

        {{-- Missão, Visão e Valores --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Por que criamos a Somos Únicas?</h2>
                    <div class="w-24 h-1 bg-pink-500 mx-auto rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div class="p-6 bg-pink-50 rounded-xl hover:shadow-md transition">
                        <div
                            class="w-16 h-16 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Nossa Missão</h3>
                        <p class="text-gray-600">
                            Fortalecer mulheres de negócios, proporcionando acesso a mentorias, conhecimentos e um ambiente
                            acolhedor, longe dos desafios comuns do mercado tradicional.
                        </p>
                    </div>

                    <div class="p-6 bg-pink-50 rounded-xl hover:shadow-md transition">
                        <div
                            class="w-16 h-16 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Nossa Visão</h3>
                        <p class="text-gray-600">
                            Ser a maior e mais eficiente comunidade de networking feminino do Brasil, onde cada integrante
                            encontra os recursos necessários para escalar os seus negócios.
                        </p>
                    </div>

                    <div class="p-6 bg-pink-50 rounded-xl hover:shadow-md transition">
                        <div
                            class="w-16 h-16 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-24">
            <div class="flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="md:w-1/2">
                    <img src="https://placehold.co/600x500/fdf2f8/ec4899?text=Networking+e+Apoio" alt="Networking Feminino"
                        class="rounded-2xl shadow-lg">
                </div>
                <div class="md:w-1/2">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Uma comunidade pensada para as suas necessidades</h2>
                    <ul class="space-y-6">
                        <li class="flex items-start">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 mt-1">
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
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 mt-1">
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
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 mt-1">
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
@endsection