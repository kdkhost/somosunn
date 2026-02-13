@extends('layouts.app')

@php
    $cmsSlug = 'institucional_manifesto';

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

@section('title', \App\Models\SiteContent::getValue($cmsSlug, 'title', 'Manifesto UNN - Nossa Visão'))
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

        $heroTitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title', 'Nosso');
        $heroHighlight = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title_highlight', 'Manifesto');
        $heroSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_subtitle', 'O que acreditamos e por que existimos.');

        $manifestoQuote = \App\Models\SiteContent::getValue($cmsSlug, 'manifesto_quote', '"Acreditamos que ninguém cresce sozinho."');
        $manifestoBody = \App\Models\SiteContent::getValue($cmsSlug, 'manifesto_body');
        $manifestoBodyFallback = '<h2>Sobre Colaboração</h2><p>Em um mundo que celebra o individualismo, nós escolhemos o caminho da colaboração. Sabemos que os maiores negócios nascem de parcerias sólidas, construídas sobre confiança e propósito compartilhado.</p><h2>Sobre Abundância</h2><p>Rejeitamos a mentalidade de escassez. Há espaço para todos crescerem. Quando um membro prospera, a comunidade inteira se fortalece. O sucesso do outro não é ameaça — é inspiração.</p><h2>Sobre Autenticidade</h2><p>Valorizamos pessoas reais, com histórias reais. Aqui não há espaço para máscaras. As conexões mais poderosas nascem quando nos mostramos autênticos.</p><h2>Sobre Impacto</h2><p>Não buscamos apenas lucro. Acreditamos que empreendedores têm o poder de transformar a sociedade. Cada negócio bem-sucedido gera empregos, melhora vidas e inspira outros a seguirem o mesmo caminho.</p><h2>Nossa Promessa</h2><p>Prometemos criar o ambiente ideal para que você encontre as pessoas certas, no momento certo. Prometemos facilitar conexões genuínas que geram valor real.</p>';

        $highlightQuote = \App\Models\SiteContent::getValue($cmsSlug, 'highlight_quote', '"Sozinhos vamos mais rápido. Juntos vamos mais longe."');
        $highlightAuthor = \App\Models\SiteContent::getValue($cmsSlug, 'highlight_author', '— Filosofia UNN');

        $pillarsTitle = \App\Models\SiteContent::getValue($cmsSlug, 'pillars_title', 'Nossos Pilares');
        $pillarsFallback = [
            ['icon' => 'fas fa-heart', 'title' => 'Confiança'],
            ['icon' => 'fas fa-hands-helping', 'title' => 'Generosidade'],
            ['icon' => 'fas fa-lightbulb', 'title' => 'Inovação'],
            ['icon' => 'fas fa-trophy', 'title' => 'Excelência'],
        ];
        $pillarsItems = $decodeJson(\App\Models\SiteContent::getValue($cmsSlug, 'pillars_items'), $pillarsFallback);

        $ctaTitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_title', 'Compartilha desses valores?');
        $ctaSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'cta_subtitle', 'Você está no lugar certo. Faça parte da nossa comunidade.');
        $ctaButtonText = \App\Models\SiteContent::getValue($cmsSlug, 'cta_button_text', 'Fazer parte');
        $ctaButtonUrl = \App\Models\SiteContent::getValue($cmsSlug, 'cta_button_url', route('register'));
    @endphp

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                    {{ $heroTitle }} <span class="unn-title-gradient">{{ $heroHighlight }}</span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 leading-relaxed">
                    {{ $heroSubtitle }}
                </p>
            </div>
        </section>

        <!-- Manifesto Content -->
        <section class="pb-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-3xl shadow-2xl p-6 md:p-12">
                    <article class="prose prose-lg max-w-none">
                        <p class="text-2xl font-bold mb-8" style="color: var(--unn-azul-1)">
                            {{ $manifestoQuote }}
                        </p>

                        {!! trim((string) $manifestoBody) !== '' ? $manifestoBody : $manifestoBodyFallback !!}

                        <div class="mt-12 p-8 rounded-2xl text-center"
                            style="background: linear-gradient(135deg, var(--unn-azul-1)10, var(--unn-azul-3)10)">
                            <p class="text-xl font-bold text-gray-900 mb-2">
                                {{ $highlightQuote }}
                            </p>
                            <p class="text-gray-500">{{ $highlightAuthor }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Pilares -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto text-center">
                <h2 class="text-3xl font-black text-gray-900 mb-8">{{ $pillarsTitle }}</h2>
                <div class="grid md:grid-cols-4 gap-6">
                    @foreach($pillarsItems as $pillar)
                        <div class="p-6">
                            <div class="w-16 h-16 btn-primary rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="{{ $pillar['icon'] ?? 'fas fa-star' }} text-white text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-900">{{ $pillar['title'] ?? '' }}</h3>
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
                    <i class="fas fa-handshake"></i>
                    {{ $ctaButtonText }}
                </a>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    @include('site.institucional.partials.common-styles')
@endpush
