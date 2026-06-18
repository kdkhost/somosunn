@extends('layouts.app')

@section('title', $page->get('seo_title', 'Como Funciona - UNN'))

@section('content')
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto text-center">
                <h1
                    class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                    <span class="unn-title-gradient">Como</span> Funciona
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    {{ $page->get('hero_subtitle', 'Entenda como a UNN pode transformar sua rede de contatos e impulsionar seus negócios.') }}
                </p>
            </div>
        </section>

        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-5xl mx-auto">
                @php
                    $steps = $page->get('steps', [
                        ['direction' => 'row', 'title' => 'Cadastre-se na Plataforma', 'text' => 'Crie sua conta gratuitamente e preencha seu perfil completo. Quanto mais informações você compartilhar, melhores serão as conexões que a plataforma irá sugerir para você.', 'li' => ['Cadastro rápido em menos de 2 minutos', 'Perfil personalizado com suas especialidades', 'Integração com LinkedIn']],
                        ['direction' => 'row-reverse', 'title' => 'Conecte-se com Outros Membros', 'text' => 'Navegue pela comunidade, encontre empreendedores com interesses similares e inicie conversas. Nossa plataforma facilita o primeiro contato e incentiva conexões genuínas.', 'li' => ['Sistema de match inteligente', 'Chat integrado na plataforma', 'Grupos temáticos por setor']],
                        ['direction' => 'row', 'title' => 'Participe de Eventos', 'text' => 'Compareça aos nossos eventos presenciais e online. Networking acontece de verdade quando olhamos nos olhos um do outro. Nossos eventos são cuidadosamente planejados para maximizar conexões.', 'li' => ['Eventos presenciais em todo Brasil', 'Webinars semanais com especialistas', 'Mentorias em grupo']],
                        ['direction' => 'row-reverse', 'title' => 'Feche Negócios', 'text' => 'Transforme conexões em parcerias e negócios reais. Membros da UNN já geraram mais de R$ 50 milhões em negócios entre si. Sua próxima grande oportunidade pode estar a uma conexão de distância.', 'li' => ['Sistema de indicações entre membros', 'Acompanhamento de deals fechados', 'Cases de sucesso da comunidade']],
                    ]);
                @endphp

                <div class="space-y-12">
                    @foreach ($steps as $i => $step)
                        <div
                            class="flex flex-col md:flex-row{{ $step['direction'] === 'row-reverse' ? '-reverse' : '' }} gap-6 md:gap-8 items-center">
                            <div
                                class="w-16 h-16 md:w-24 md:h-24 btn-primary rounded-3xl flex items-center justify-center text-white text-2xl md:text-4xl font-black shrink-0">
                                {{ $i + 1 }}
                            </div>
                            <div class="flex-1 bg-white rounded-3xl p-6 md:p-8 shadow-lg">
                                <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ $step['title'] }}</h3>
                                <p class="text-gray-600 mb-4">{{ $step['text'] }}</p>
                                <ul class="space-y-2 text-gray-600">
                                    @foreach ($step['li'] as $item)
                                        <li><i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i> {{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-4 text-center">
                    {{ $page->get('plans_title', 'Escolha seu Plano') }}</h2>
                <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">
                    {{ $page->get('plans_subtitle', 'Temos opções para todos os estágios da sua jornada empreendedora.') }}
                </p>

                @php
                    $plans = $plans ?? collect();
                    $allPeriods = $allPeriods ?? ['mensal' => 'Mensal'];
                    $defaultPeriod = $defaultPeriod ?? (array_key_first($allPeriods) ?: 'mensal');
                    $planPriceData = $planPriceData ?? [];
                @endphp

                <div class="grid md:grid-cols-{{ min(4, max(1, $plans->count())) }} gap-8 items-start">
                    @forelse($plans as $plan)
                        @php
                            $periods = $planPriceData[$plan->id] ?? ['mensal' => (float) $plan->price];
                            $initialPeriod = !$plan->isFreeAccessPlan() && array_key_exists($defaultPeriod, $periods)
                                ? $defaultPeriod
                                : (array_key_first($periods) ?: 'mensal');
                            $initialPrice = $periods[$initialPeriod] ?? ((float) ($plan->price ?? 0));
                            $benefits = method_exists($plan, 'resolvedBenefits')
                                ? array_slice($plan->resolvedBenefits(), 0, 5)
                                : [];
                            $description = method_exists($plan, 'marketingDescription')
                                ? $plan->marketingDescription()
                                : (string) ($plan->description ?? '');
                        @endphp

                        <div class="{{ $plan->highlight ? 'bg-white shadow-2xl ring-2 -mt-4 relative' : 'bg-slate-50' }} rounded-3xl p-6 md:p-8 text-center shadow-lg"
                            style="{{ $plan->highlight ? '--tw-ring-color: var(--unn-azul-1)' : '' }}">
                            @if($plan->highlight)
                                <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 btn-primary text-white text-sm font-bold rounded-full whitespace-nowrap">
                                    POPULAR
                                </span>
                            @endif

                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>

                            @if($plan->isFreeAccessPlan())
                                <p class="text-4xl font-black text-gray-900 mb-4">R$ 0</p>
                            @else
                                <p class="text-4xl font-black mb-4" style="color: var(--unn-azul-1)">
                                    R$ {{ number_format((float) $initialPrice, 2, ',', '.') }}<span
                                        class="text-lg text-gray-500">/{{ strtolower($allPeriods[$initialPeriod] ?? $initialPeriod) }}</span>
                                </p>
                            @endif

                            <p class="text-gray-500 mb-6 text-sm">
                                {{ $description !== '' ? $description : 'Plano disponível no sistema.' }}
                            </p>

                            <ul class="text-left space-y-3 mb-8">
                                @foreach($benefits as $benefit)
                                    <li class="flex items-center gap-2 text-gray-600">
                                        <i class="fas fa-check text-green-500"></i> {{ $benefit }}
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ $plan->isFreeAccessPlan() ? route('register') : route('subscription.checkout', ['plan' => $plan->id, 'period' => $initialPeriod]) }}"
                                class="block w-full py-3 rounded-xl font-semibold transition {{ $plan->highlight ? 'btn-primary text-white shadow-lg hover:shadow-xl' : 'border-2 hover:bg-gray-100' }}"
                                style="{{ !$plan->highlight ? 'border-color: var(--unn-azul-1); color: var(--unn-azul-1)' : '' }}">
                                {{ $plan->isFreeAccessPlan() ? 'Começar grátis' : 'Assinar ' . $plan->name }}
                            </a>
                        </div>
                    @empty
                        <div class="md:col-span-3 rounded-3xl bg-slate-50 p-8 text-center text-gray-500">
                            Nenhum plano ativo disponível no momento.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="py-16 px-6 md:px-12 lg:px-24"
            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
            <div class="max-w-4xl mx-auto text-center text-white">
                <h2 class="text-3xl lg:text-4xl font-black mb-4">{{ $page->get('cta_title', 'Pronto para começar?') }}</h2>
                <p class="text-lg opacity-90 mb-8">
                    {{ $page->get('cta_subtitle', 'Crie sua conta agora e comece a fazer conexões valiosas.') }}</p>
                <a href="{{ route('register') }}"
                    class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition"
                    style="color: var(--unn-azul-1)">
                    <i class="fas fa-rocket"></i>
                    {{ $page->get('cta_btn', 'Criar conta grátis') }}
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
