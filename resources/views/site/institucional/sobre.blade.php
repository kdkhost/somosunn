@extends('layouts.app')

@section('title', 'Sobre a UNN - União Nacional de Networking')

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-8 md:gap-12 items-center">
                <div>
                    <!-- HERO TITLE -->
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                        Conheça a <span class="unn-title-gradient">UNN</span>
                    </h1>
                    <p class="text-xl text-gray-600 leading-relaxed mb-8">
                        A União Nacional de Networking é a maior comunidade de empreendedores do Brasil, 
                        conectando pessoas que querem crescer juntas através de parcerias estratégicas e negócios colaborativos.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('register') }}" class="btn-primary text-white px-8 py-4 rounded-full font-bold inline-flex items-center gap-2 shadow-lg hover:shadow-xl transition">
                            <i class="fas fa-rocket"></i> Fazer parte
                        </a>
                        <a href="{{ route('manifesto') }}" class="bg-white text-gray-700 px-8 py-4 rounded-full font-bold inline-flex items-center gap-2 shadow-lg hover:shadow-xl transition">
                            <i class="fas fa-book-open"></i> Nosso Manifesto
                        </a>
                    </div>
                </div>
                <div class="relative mt-8 lg:mt-0">
                    <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-8">
                        <div class="grid grid-cols-2 gap-3 md:gap-6">
                            <div class="text-center p-3 md:p-6 bg-slate-50 rounded-2xl">
                                <p class="text-2xl sm:text-3xl lg:text-4xl font-black" style="color: var(--unn-azul-1)">5k+</p>
                                <p class="text-xs md:text-sm text-gray-500 mt-1">Empreendedores</p>
                            </div>
                            <div class="text-center p-3 md:p-6 bg-slate-50 rounded-2xl">
                                <p class="text-2xl sm:text-3xl lg:text-4xl font-black" style="color: var(--unn-azul-1)">27</p>
                                <p class="text-xs md:text-sm text-gray-500 mt-1">Estados</p>
                            </div>
                            <div class="text-center p-3 md:p-6 bg-slate-50 rounded-2xl">
                                <p class="text-2xl sm:text-3xl lg:text-4xl font-black break-words" style="color: var(--unn-azul-1)">R$ 50M+</p>
                                <p class="text-xs md:text-sm text-gray-500 mt-1">Negócios gerados</p>
                            </div>
                            <div class="text-center p-3 md:p-6 bg-slate-50 rounded-2xl">
                                <p class="text-2xl sm:text-3xl lg:text-4xl font-black" style="color: var(--unn-azul-1)">200+</p>
                                <p class="text-xs md:text-sm text-gray-500 mt-1">Eventos realizados</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nossa História -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-8 text-center">Nossa História</h2>
            
            <div class="prose prose-lg max-w-none text-gray-600">
                <p class="lead text-xl mb-6">
                    A UNN nasceu em 2020 com uma missão clara: democratizar o acesso ao networking de qualidade no Brasil.
                </p>
                <p class="mb-6">
                    Fundada por um grupo de empreendedores que acreditavam no poder das conexões humanas, a União Nacional de Networking 
                    começou como pequenos encontros presenciais em São Paulo. Em poucos meses, a comunidade cresceu exponencialmente, 
                    alcançando empreendedores em todos os estados brasileiros.
                </p>
                <p class="mb-6">
                    Hoje, somos a maior plataforma de networking empresarial do país, com milhares de membros ativos que geram 
                    negócios, parcerias e amizades duradouras através da nossa metodologia exclusiva de conexões.
                </p>
            </div>
        </div>
    </section>

    <!-- O que nos diferencia -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-12 text-center">O que nos diferencia</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                    <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Comunidade Selecionada</h3>
                    <p class="text-gray-600">Todos os membros passam por uma curadoria para garantir a qualidade das conexões.</p>
                </div>
                <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                    <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-handshake text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Conexões Reais</h3>
                    <p class="text-gray-600">Eventos presenciais e online que geram relacionamentos genuínos e duradouros.</p>
                </div>
                <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                    <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Resultados Mensuráveis</h3>
                    <p class="text-gray-600">Acompanhamos e celebramos cada negócio fechado entre nossos membros.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">Pronto para crescer com a gente?</h2>
            <p class="text-lg opacity-90 mb-8">Junte-se a milhares de empreendedores que já transformaram suas carreiras.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-user-plus"></i>
                Começar agora
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
