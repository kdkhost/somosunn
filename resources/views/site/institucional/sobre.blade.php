@extends('layouts.app')

@php
    $cmsSlug = 'institucional_sobre';

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

@section('title', \App\Models\SiteContent::getValue($cmsSlug, 'title', 'Sobre a UNN - União Nacional de Networking'))
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

        $heroTitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title', 'Conheça a');
        $heroHighlight = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title_highlight', 'UNN');
        $heroSubtitle = \App\Models\SiteContent::getValue(
            $cmsSlug,
            'hero_subtitle',
            'A União Nacional de Networking é a maior comunidade de empreendedores do Brasil, conectando pessoas que querem crescer juntas através de parcerias estratégicas e negócios colaborativos.'
        );

        $primaryText = \App\Models\SiteContent::getValue($cmsSlug, 'hero_primary_button_text', 'Fazer parte');
        $primaryUrl = $normalizeHref(\App\Models\SiteContent::getValue($cmsSlug, 'hero_primary_button_url'), route('register'));
        $secondaryText = \App\Models\SiteContent::getValue($cmsSlug, 'hero_secondary_button_text', 'Nosso Manifesto');
        $secondaryUrl = $normalizeHref(\App\Models\SiteContent::getValue($cmsSlug, 'hero_secondary_button_url'), route('manifesto'));

        $statsFallback = [
            ['value' => '5k+', 'label' => 'Empreendedores'],
            ['value' => '27', 'label' => 'Estados'],
            ['value' => 'R$ 50M+', 'label' => 'Negócios gerados'],
            ['value' => '200+', 'label' => 'Eventos realizados'],
        ];
        $heroStats = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'hero_stats'), $statsFallback);

        $historyTitle = \App\Models\SiteContent::getValue($cmsSlug, 'history_title', 'Nossa História');
        $historyBody = \App\Models\SiteContent::getValue($cmsSlug, 'history_body');
        $historyBodyFallback = '<p class="lead text-xl mb-6">A UNN nasceu em 2020 com uma missão clara: democratizar o acesso ao networking de qualidade no Brasil.</p><p class="mb-6">Fundada por um grupo de empreendedores que acreditavam no poder das conexões humanas, a União Nacional de Networking começou como pequenos encontros presenciais em São Paulo. Em poucos meses, a comunidade cresceu exponencialmente, alcançando empreendedores em todos os estados brasileiros.</p><p class="mb-6">Hoje, somos uma das maiores plataformas de networking empresarial do país, com milhares de membros ativos que geram negócios, parcerias e amizades duradouras através da nossa metodologia exclusiva de conexões.</p>';

        $diffTitle = \App\Models\SiteContent::getValue($cmsSlug, 'diff_title', 'O que nos diferencia');
        $diffFallback = [
            ['icon' => 'fas fa-users', 'title' => 'Comunidade Selecionada', 'text' => 'Todos os membros passam por uma curadoria para garantir a qualidade das conexões.'],
            ['icon' => 'fas fa-handshake', 'title' => 'Conexões Reais', 'text' => 'Eventos presenciais e online que geram relacionamentos genuínos e duradouros.'],
            ['icon' => 'fas fa-chart-line', 'title' => 'Resultados Mensuráveis', 'text' => 'Acompanhamos e celebramos cada negócio fechado entre nossos membros.'],
        ];
        $diffItems = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'diff_items'), $diffFallback);

        $ctaTitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_title', 'Pronto para crescer com a gente?');
        $ctaSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_subtitle', 'Junte-se a milhares de empreendedores que já transformaram suas carreiras.');
        $ctaButtonText = \App\Models\SiteContent::getValue($cmsSlug, 'cta_button_text', 'Começar agora');
        $ctaButtonUrl = $normalizeHref(\App\Models\SiteContent::getValue($cmsSlug, 'cta_button_url'), route('register'));
    @endphp

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-8 md:gap-12 items-center">
                    <div>
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                            {{ $heroTitle }} <span class="unn-title-gradient">{{ $heroHighlight }}</span>
                        </h1>
                        <p class="text-xl text-gray-600 leading-relaxed mb-8">
                            {{ $heroSubtitle }}
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ $primaryUrl }}"
                                class="btn-primary text-white px-8 py-4 rounded-full font-bold inline-flex items-center gap-2 shadow-lg hover:shadow-xl transition">
                                <i class="fas fa-rocket"></i> {{ $primaryText }}
                            </a>
                            <a href="{{ $secondaryUrl }}"
                                class="bg-white text-gray-700 px-8 py-4 rounded-full font-bold inline-flex items-center gap-2 shadow-lg hover:shadow-xl transition">
                                <i class="fas fa-book-open"></i> {{ $secondaryText }}
                            </a>
                        </div>
                    </div>

                    <div class="relative mt-8 lg:mt-0">
                        <div class="bg-white rounded-3xl shadow-2xl p-4 md:p-8">
                            <div class="grid grid-cols-2 gap-3 md:gap-6">
                                @foreach($heroStats as $stat)
                                    <div class="text-center p-3 md:p-6 bg-slate-50 rounded-2xl">
                                        <p class="text-2xl sm:text-3xl lg:text-4xl font-black break-words" style="color: var(--unn-azul-1)">
                                            {{ $stat['value'] ?? '' }}
                                        </p>
                                        <p class="text-xs md:text-sm text-gray-500 mt-1">{{ $stat['label'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Nossa História -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-8 text-center">{{ $historyTitle }}</h2>

                <div class="prose prose-lg max-w-none text-gray-600">
                    {!! trim((string) $historyBody) !== '' ? $historyBody : $historyBodyFallback !!}
                </div>
            </div>
        </section>

        <!-- Diferenciais -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl lg:text-4xl font-black text-gray-900 mb-12 text-center">{{ $diffTitle }}</h2>

                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($diffItems as $item)
                        <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                            <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <i class="{{ $item['icon'] ?? 'fas fa-star' }} text-white text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $item['title'] ?? '' }}</h3>
                            <p class="text-gray-600">{{ $item['text'] ?? '' }}</p>
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
                    <i class="fas fa-user-plus"></i>
                    {{ $ctaButtonText }}
                </a>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    @include('site.institucional.partials.common-styles')
@endpush
