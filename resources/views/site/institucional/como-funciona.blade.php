@extends('layouts.app')

@section('title', 'Como Funciona - UNN')

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-24 pb-12 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-5xl lg:text-6xl font-black leading-tight mb-6">
                <span class="text-gradient">Como</span> Funciona
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Entenda como a UNN pode transformar sua rede de contatos e impulsionar seus negócios.
            </p>
        </div>
    </section>

    <!-- Steps -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-5xl mx-auto">
            <div class="space-y-12">
                <!-- Step 1 -->
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="w-24 h-24 btn-primary rounded-3xl flex items-center justify-center text-white text-4xl font-black shrink-0">
                        1
                    </div>
                    <div class="flex-1 bg-white rounded-3xl p-8 shadow-lg">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Cadastre-se na Plataforma</h3>
                        <p class="text-gray-600 mb-4">
                            Crie sua conta gratuitamente e preencha seu perfil completo. Quanto mais informações você compartilhar, 
                            melhores serão as conexões que a plataforma irá sugerir para você.
                        </p>
                        <ul class="space-y-2 text-gray-600">
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Cadastro rápido em menos de 2 minutos</li>
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Perfil personalizado com suas especialidades</li>
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Integração com LinkedIn</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex flex-col md:flex-row-reverse gap-8 items-center">
                    <div class="w-24 h-24 btn-primary rounded-3xl flex items-center justify-center text-white text-4xl font-black shrink-0">
                        2
                    </div>
                    <div class="flex-1 bg-white rounded-3xl p-8 shadow-lg">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Conecte-se com Outros Membros</h3>
                        <p class="text-gray-600 mb-4">
                            Navegue pela comunidade, encontre empreendedores com interesses similares e inicie conversas. 
                            Nossa plataforma facilita o primeiro contato e incentiva conexões genuínas.
                        </p>
                        <ul class="space-y-2 text-gray-600">
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Sistema de match inteligente</li>
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Chat integrado na plataforma</li>
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Grupos temáticos por setor</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="w-24 h-24 btn-primary rounded-3xl flex items-center justify-center text-white text-4xl font-black shrink-0">
                        3
                    </div>
                    <div class="flex-1 bg-white rounded-3xl p-8 shadow-lg">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Participe de Eventos</h3>
                        <p class="text-gray-600 mb-4">
                            Compareça aos nossos eventos presenciais e online. Networking acontece de verdade quando 
                            olhamos nos olhos um do outro. Nossos eventos são cuidadosamente planejados para maximizar conexões.
                        </p>
                        <ul class="space-y-2 text-gray-600">
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Eventos presenciais em todo Brasil</li>
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Webinars semanais com especialistas</li>
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Mentorias em grupo</li>
                        </ul>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="flex flex-col md:flex-row-reverse gap-8 items-center">
                    <div class="w-24 h-24 btn-primary rounded-3xl flex items-center justify-center text-white text-4xl font-black shrink-0">
                        4
                    </div>
                    <div class="flex-1 bg-white rounded-3xl p-8 shadow-lg">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Feche Negócios</h3>
                        <p class="text-gray-600 mb-4">
                            Transforme conexões em parcerias e negócios reais. Membros da UNN já geraram mais de R$ 50 milhões 
                            em negócios entre si. Sua próxima grande oportunidade pode estar a uma conexão de distância.
                        </p>
                        <ul class="space-y-2 text-gray-600">
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Sistema de indicações entre membros</li>
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Acompanhamento de deals fechados</li>
                            <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> Cases de sucesso da comunidade</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Planos -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-4 text-center">Escolha seu Plano</h2>
            <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">
                Temos opções para todos os estágios da sua jornada empreendedora.
            </p>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Free -->
                <div class="bg-slate-50 rounded-3xl p-8 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Gratuito</h3>
                    <p class="text-4xl font-black text-gray-900 mb-4">R$ 0</p>
                    <p class="text-gray-500 mb-6">Para começar</p>
                    <ul class="text-left space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Perfil na comunidade
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Feed social
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> 5 conexões/mês
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full py-3 border-2 rounded-xl font-semibold transition hover:bg-gray-100" style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                        Começar grátis
                    </a>
                </div>

                <!-- Premium -->
                <div class="bg-white rounded-3xl p-8 text-center shadow-2xl ring-2 relative" style="--tw-ring-color: var(--unn-azul-1)">
                    <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 btn-primary text-white text-sm font-bold rounded-full">
                        POPULAR
                    </span>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Premium</h3>
                    <p class="text-4xl font-black mb-4" style="color: var(--unn-azul-1)">R$ 97<span class="text-lg text-gray-500">/mês</span></p>
                    <p class="text-gray-500 mb-6">Para crescer</p>
                    <ul class="text-left space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Tudo do Gratuito
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Conexões ilimitadas
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Eventos exclusivos
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Cursos e mentorias
                        </li>
                    </ul>
                    <a href="{{ route('premium') }}" class="block w-full py-3 btn-primary text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition">
                        Assinar Premium
                    </a>
                </div>

                <!-- Business -->
                <div class="bg-slate-50 rounded-3xl p-8 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Business</h3>
                    <p class="text-4xl font-black text-gray-900 mb-4">R$ 297<span class="text-lg text-gray-500">/mês</span></p>
                    <p class="text-gray-500 mb-6">Para empresas</p>
                    <ul class="text-left space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Tudo do Premium
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> 5 usuários inclusos
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Consultoria mensal
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-check text-green-500"></i> Suporte prioritário
                        </li>
                    </ul>
                    <a href="{{ route('contato') }}" class="block w-full py-3 border-2 rounded-xl font-semibold transition hover:bg-gray-100" style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                        Falar com vendas
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">Pronto para começar?</h2>
            <p class="text-lg opacity-90 mb-8">Crie sua conta agora e comece a fazer conexões valiosas.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-rocket"></i>
                Criar conta grátis
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
</style>
@endsection
