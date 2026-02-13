@extends('layouts.app')

@php
    $cmsSlug = 'institucional_como_funciona';

    $metaImagePath = (string) \App\Models\SiteContent::getValue($cmsSlug, 'meta_image', '');
    $metaImageUrl = '';
    if (trim($metaImagePath) !== '') {
        $metaImageUrl = (str_starts_with($metaImagePath, 'http://') || str_starts_with($metaImagePath, 'https://'))
            ? $metaImagePath
            : asset('storage/' . ltrim($metaImagePath, '/'));
    }

    $twitterImagePath = (string) \App\Models\SiteContent::getValue($cmsSlug, 'twitter_image', '');
    $twitterImageUrl = '';
    if (trim($twitterImagePath) !== '') {
        $twitterImageUrl = (str_starts_with($twitterImagePath, 'http://') || str_starts_with($twitterImagePath, 'https://'))
            ? $twitterImagePath
            : asset('storage/' . ltrim($twitterImagePath, '/'));
    } elseif ($metaImageUrl !== '') {
        $twitterImageUrl = $metaImageUrl;
    }
@endphp

@section('title', \App\Models\SiteContent::getValue($cmsSlug, 'title', 'Como Funciona - UNN'))
@section('meta_title', \App\Models\SiteContent::getValue($cmsSlug, 'meta_title', ''))
@section('meta_description', \App\Models\SiteContent::getValue($cmsSlug, 'meta_description', ''))
@section('meta_keywords', \App\Models\SiteContent::getValue($cmsSlug, 'meta_keywords', ''))
@section('meta_robots', \App\Models\SiteContent::getValue($cmsSlug, 'meta_robots', ''))
@section('canonical', \App\Models\SiteContent::getValue($cmsSlug, 'canonical', ''))
@section('og_type', \App\Models\SiteContent::getValue($cmsSlug, 'og_type', ''))
@section('twitter_card', \App\Models\SiteContent::getValue($cmsSlug, 'twitter_card', ''))
@section('meta_image', $metaImageUrl)
@section('twitter_image', $twitterImageUrl)

@section('content')
    @php
        $decodeJson = function (?string $raw, array $fallback = []) {
            $raw = trim((string) $raw);
            if ($raw === '') return $fallback;
            $val = json_decode($raw, true);
            return is_array($val) ? $val : $fallback;
        };

        $normalizeHref = function (?string $value, string $fallback) {
            $value = trim((string) $value);
            if ($value === '') return $fallback;
            if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
                return $value;
            }
            return '/' . ltrim($value, '/');
        };

        $heroHighlight = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title_highlight', 'Como');
        $heroTitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title', 'Funciona');
        $heroSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_subtitle', 'Entenda como a UNN pode transformar sua rede de contatos e impulsionar seus negócios.');

        $stepsFallback = [
            [
                'title' => 'Cadastre-se na Plataforma',
                'text' => 'Crie sua conta gratuitamente e preencha seu perfil completo. Quanto mais informações você compartilhar, melhores serão as conexões sugeridas para você.',
                'bullet_1' => 'Cadastro rápido em menos de 2 minutos',
                'bullet_2' => 'Perfil personalizado com suas especialidades',
                'bullet_3' => 'Integração com LinkedIn',
            ],
            [
                'title' => 'Conecte-se com Outros Membros',
                'text' => 'Navegue pela comunidade, encontre empreendedores com interesses similares e inicie conversas. Nossa plataforma incentiva conexões genuínas.',
                'bullet_1' => 'Sistema de match inteligente',
                'bullet_2' => 'Chat integrado na plataforma',
                'bullet_3' => 'Grupos temáticos por setor',
            ],
            [
                'title' => 'Participe de Eventos',
                'text' => 'Compareça aos nossos eventos presenciais e online. Networking acontece de verdade quando olhamos nos olhos um do outro.',
                'bullet_1' => 'Eventos presenciais em todo Brasil',
                'bullet_2' => 'Webinars semanais com especialistas',
                'bullet_3' => 'Mentorias em grupo',
            ],
            [
                'title' => 'Feche Negócios',
                'text' => 'Transforme conexões em parcerias e negócios reais. Sua próxima grande oportunidade pode estar a uma conexão de distância.',
                'bullet_1' => 'Sistema de indicações entre membros',
                'bullet_2' => 'Acompanhamento de deals fechados',
                'bullet_3' => 'Cases de sucesso da comunidade',
            ],
        ];
        $steps = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'steps_items'), $stepsFallback);

        $plansTitle = \App\Models\SiteContent::getValue($cmsSlug, 'plans_title', 'Escolha seu Plano');
        $plansSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'plans_subtitle', 'Temos opções para todos os estágios da sua jornada empreendedora.');

        $plansFallback = [
            [
                'title' => 'Gratuito',
                'price' => 'R$ 0',
                'period' => '',
                'tagline' => 'Para começar',
                'feature_1' => 'Perfil na comunidade',
                'feature_2' => 'Feed social',
                'feature_3' => '5 conexões/mês',
                'feature_4' => '',
                'button_text' => 'Começar grátis',
                'button_url' => '/register',
                'featured' => 0,
                'badge' => '',
            ],
            [
                'title' => 'Premium',
                'price' => 'R$ 97',
                'period' => '/mês',
                'tagline' => 'Para crescer',
                'feature_1' => 'Tudo do Gratuito',
                'feature_2' => 'Conexões ilimitadas',
                'feature_3' => 'Eventos exclusivos',
                'feature_4' => 'Cursos e mentorias',
                'button_text' => 'Assinar Premium',
                'button_url' => '/premium',
                'featured' => 1,
                'badge' => 'POPULAR',
            ],
            [
                'title' => 'Business',
                'price' => 'R$ 297',
                'period' => '/mês',
                'tagline' => 'Para empresas',
                'feature_1' => 'Tudo do Premium',
                'feature_2' => '5 usuários inclusos',
                'feature_3' => 'Consultoria mensal',
                'feature_4' => 'Suporte prioritário',
                'button_text' => 'Falar com vendas',
                'button_url' => '/contato',
                'featured' => 0,
                'badge' => '',
            ],
        ];
        $plans = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'plans_items'), $plansFallback);

        $ctaTitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_title', 'Pronto para começar?');
        $ctaSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_subtitle', 'Crie sua conta agora e comece a fazer conexões valiosas.');
        $ctaButtonText = \App\Models\SiteContent::getValue($cmsSlug, 'cta_button_text', 'Criar conta grátis');
        $ctaButtonUrl = $normalizeHref(\App\Models\SiteContent::getValue($cmsSlug, 'cta_button_url'), route('register'));
    @endphp

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                    <span class="unn-title-gradient">{{ $heroHighlight }}</span> {{ $heroTitle }}
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    {{ $heroSubtitle }}
                </p>
            </div>
        </section>

        <!-- Steps -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-5xl mx-auto">
                <div class="space-y-12">
                    @foreach($steps as $idx => $step)
                        @php
                            $step = is_array($step) ? $step : [];
                            $reverse = ($idx % 2) === 1;
                            $wrapClass = $reverse ? 'md:flex-row-reverse' : 'md:flex-row';
                        @endphp
                        <div class="flex flex-col {{ $wrapClass }} gap-6 md:gap-8 items-center">
                            <div class="w-16 h-16 md:w-24 md:h-24 btn-primary rounded-3xl flex items-center justify-center text-white text-2xl md:text-4xl font-black shrink-0">
                                {{ $idx + 1 }}
                            </div>
                            <div class="flex-1 bg-white rounded-3xl p-6 md:p-8 shadow-lg">
                                <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ $step['title'] ?? '' }}</h3>
                                <p class="text-gray-600 mb-4">
                                    {{ $step['text'] ?? '' }}
                                </p>
                                <ul class="space-y-2 text-gray-600">
                                    @foreach(['bullet_1','bullet_2','bullet_3'] as $bKey)
                                        @if(!empty($step[$bKey]))
                                            <li>
                                                <i class="fas fa-check mr-2" style="color: var(--unn-azul-1)"></i>
                                                {{ $step[$bKey] }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Planos -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-4 text-center">{{ $plansTitle }}</h2>
                <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">
                    {{ $plansSubtitle }}
                </p>

                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($plans as $plan)
                        @php
                            $plan = is_array($plan) ? $plan : [];
                            $featured = !empty($plan['featured']);
                            $boxClass = $featured ? 'bg-white shadow-2xl ring-2 relative' : 'bg-slate-50';
                            $buttonUrl = $normalizeHref($plan['button_url'] ?? '', route('register'));
                            $price = (string) ($plan['price'] ?? '');
                            $period = (string) ($plan['period'] ?? '');
                        @endphp
                        <div class="rounded-3xl p-6 md:p-8 text-center {{ $boxClass }}" @if($featured) style="--tw-ring-color: var(--unn-azul-1)" @endif>
                            @if($featured && !empty($plan['badge']))
                                <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 btn-primary text-white text-sm font-bold rounded-full">
                                    {{ $plan['badge'] }}
                                </span>
                            @endif
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan['title'] ?? '' }}</h3>
                            <p class="text-4xl font-black mb-4" style="color: {{ $featured ? 'var(--unn-azul-1)' : 'inherit' }}">
                                {{ $price }}@if($period !== '')<span class="text-lg text-gray-500">{{ $period }}</span>@endif
                            </p>
                            <p class="text-gray-500 mb-6">{{ $plan['tagline'] ?? '' }}</p>
                            <ul class="text-left space-y-3 mb-8">
                                @foreach(['feature_1','feature_2','feature_3','feature_4'] as $fKey)
                                    @if(!empty($plan[$fKey]))
                                        <li class="flex items-center gap-2 text-gray-600">
                                            <i class="fas fa-check text-green-500"></i> {{ $plan[$fKey] }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            @php
                                $btnClass = $featured
                                    ? 'block w-full py-3 btn-primary text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition'
                                    : 'block w-full py-3 border-2 rounded-xl font-semibold transition hover:bg-gray-100';
                            @endphp
                            <a href="{{ $buttonUrl }}" class="{{ $btnClass }}" @if(!$featured) style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)" @endif>
                                {{ $plan['button_text'] ?? 'Saiba mais' }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 px-6 md:px-12 lg:px-24"
            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
            <div class="max-w-4xl mx-auto text-center text-white">
                <h2 class="text-3xl lg:text-4xl font-black mb-4">{{ $ctaTitle }}</h2>
                <p class="text-lg opacity-90 mb-8">{{ $ctaSubtitle }}</p>
                <a href="{{ $ctaButtonUrl }}"
                    class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition"
                    style="color: var(--unn-azul-1)">
                    <i class="fas fa-rocket"></i>
                    {{ $ctaButtonText }}
                </a>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    @include('site.institucional.partials.common-styles')
@endpush
