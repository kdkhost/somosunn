@extends('layouts.app')

@php
    $cmsSlug = 'institucional_contato';

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

    $companyName = \App\Models\Setting::get('company_name') ?: 'UNN';
    $companyEmail = \App\Models\Setting::get('company_email') ?: 'contato@somosunn.com.br';
    $companyPhone = \App\Models\Setting::get('company_phone') ?: '(11) 99999-9999';
    $companyZip = \App\Models\Setting::get('company_zip') ?: '01310-100';
    $companyAddress = \App\Models\Setting::get('company_address') ?: 'Av. Paulista, 1000';
    $companyNumber = \App\Models\Setting::get('company_number') ?: '1001';
    $companyComplement = \App\Models\Setting::get('company_complement') ?: null;
    $companyDistrict = \App\Models\Setting::get('company_district') ?: 'Bela Vista';
    $companyCity = \App\Models\Setting::get('company_city') ?: 'São Paulo';
    $companyState = \App\Models\Setting::get('company_state') ?: 'SP';

    $normalizeSocialUrl = function ($value, string $network): ?string {
        $value = trim((string) $value);
        if ($value === '' || $value === '#') {
            return null;
        }

        if (preg_match('/^\\s*javascript\\s*:/i', $value)) {
            return null;
        }

        if (preg_match('/^https?:\\/\\//i', $value)) {
            return $value;
        }
        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        if ($network === 'instagram' && str_starts_with($value, '@')) {
            return 'https://instagram.com/' . ltrim($value, '@');
        }

        if (preg_match('/^[a-z0-9.-]+\\.[a-z]{2,}/i', $value)) {
            return 'https://' . $value;
        }

        return $value;
    };

    $socialInstagram = $normalizeSocialUrl(\App\Models\Setting::get('social_instagram'), 'instagram');
    $socialFacebook = $normalizeSocialUrl(\App\Models\Setting::get('social_facebook'), 'facebook');
    $socialYoutube = $normalizeSocialUrl(\App\Models\Setting::get('social_youtube'), 'youtube');
    $socialLinkedin = $normalizeSocialUrl(\App\Models\Setting::get('social_linkedin'), 'linkedin');

    $socialLinks = array_values(array_filter([
        ['url' => $socialInstagram, 'icon' => 'fab fa-instagram', 'title' => 'Instagram'],
        ['url' => $socialLinkedin, 'icon' => 'fab fa-linkedin', 'title' => 'LinkedIn'],
        ['url' => $socialYoutube, 'icon' => 'fab fa-youtube', 'title' => 'YouTube'],
        ['url' => $socialFacebook, 'icon' => 'fab fa-facebook', 'title' => 'Facebook'],
    ], function ($item) {
        return !empty($item['url']);
    }));

    $fullAddress = trim(implode(', ', array_filter([
        $companyAddress . ($companyNumber ? ' ' . $companyNumber : ''),
        $companyComplement,
        $companyDistrict,
        $companyCity . ' - ' . $companyState,
        'CEP ' . $companyZip,
        'Brasil',
    ])));
    $mapQuery = $fullAddress !== '' ? rawurlencode($fullAddress) : rawurlencode('Sao Paulo, Brasil');
    $mapEmbedUrl = 'https://www.google.com/maps?q=' . $mapQuery . '&output=embed';

    $recaptchaSiteKey = (string) (\App\Models\Setting::get('recaptcha_v3_site_key') ?: config('services.recaptcha.site_key', ''));
@endphp

@section('title', \App\Models\SiteContent::getValue($cmsSlug, 'title', 'Contato - UNN'))
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
        $heroTitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title', 'Fale');
        $heroHighlight = \App\Models\SiteContent::getValue($cmsSlug, 'hero_title_highlight', 'Conosco');
        $heroSubtitle = \App\Models\SiteContent::getValue($cmsSlug, 'hero_subtitle', 'Estamos aqui para ajudar. Entre em contato por qualquer um dos canais abaixo.');

        $mapTitle = \App\Models\SiteContent::getValue($cmsSlug, 'map_title', 'Nossa Localização');
        $mapEmbedUrlOverride = (string) \App\Models\SiteContent::getValue($cmsSlug, 'map_embed_url', '');
        $mapEmbedUrlOverride = trim($mapEmbedUrlOverride);
        if ($mapEmbedUrlOverride !== '' && preg_match('/^\\s*javascript\\s*:/i', $mapEmbedUrlOverride)) {
            $mapEmbedUrlOverride = '';
        }

        $finalMapEmbedUrl = $mapEmbedUrlOverride !== '' ? $mapEmbedUrlOverride : $mapEmbedUrl;
    @endphp

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                    {{ $heroTitle }} <span class="text-gradient">{{ $heroHighlight }}</span>
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    {{ $heroSubtitle }}
                </p>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                @include('site.institucional.partials.contact-alerts')

                <div class="grid lg:grid-cols-2 gap-8 md:gap-12">
                    @include('site.institucional.partials.contact-info', [
                        'companyEmail' => $companyEmail,
                        'companyPhone' => $companyPhone,
                        'companyZip' => $companyZip,
                        'companyAddress' => $companyAddress,
                        'companyNumber' => $companyNumber,
                        'companyComplement' => $companyComplement,
                        'companyDistrict' => $companyDistrict,
                        'companyCity' => $companyCity,
                        'companyState' => $companyState,
                        'socialLinks' => $socialLinks,
                    ])

                    @include('site.institucional.partials.contact-form')
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">{{ $mapTitle }}</h2>
                <div class="rounded-3xl overflow-hidden shadow-2xl h-[400px]">
                    <iframe
                        src="{{ $finalMapEmbedUrl }}"
                        class="w-full h-full border-0"
                        loading="lazy"
                        title="{{ $mapTitle }}"
                    ></iframe>
                </div>
            </div>
        </section>

        <x-faq-section context="contact" />
    </div>
@endsection

@push('styles')
    @include('site.institucional.partials.common-styles')
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messageInput = document.getElementById('contact-message');
        const charCounter = document.getElementById('char-counter');
        const submitBtn = document.getElementById('submit-btn');
        const minChars = 10;

        function updateCounter() {
            const len = messageInput.value.length;
            charCounter.textContent = len + '/' + minChars + ' caracteres';

            if (len >= minChars) {
                charCounter.classList.remove('text-red-500');
                charCounter.classList.add('text-green-600');
                submitBtn.disabled = false;
            } else {
                charCounter.classList.remove('text-green-600');
                charCounter.classList.add('text-red-500');
                submitBtn.disabled = true;
            }
        }

        if (messageInput && charCounter && submitBtn) {
            messageInput.addEventListener('input', updateCounter);
            updateCounter();
        }
    });
</script>

@if($recaptchaSiteKey !== '')
    <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('contact-form');
            const tokenInput = document.getElementById('recaptcha_token');
            const siteKey = @json($recaptchaSiteKey);

            if (!form || !tokenInput || !siteKey) {
                return;
            }

            if (typeof grecaptcha === 'undefined') {
                console.warn('reCAPTCHA não carregou, enviando sem token');
                return;
            }

            form.addEventListener('submit', function (e) {
                if (form.dataset.recaptchaReady === '1') return;

                e.preventDefault();

                const timeout = setTimeout(function() {
                    console.warn('reCAPTCHA timeout, enviando sem token');
                    form.dataset.recaptchaReady = '1';
                    form.submit();
                }, 5000);

                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, { action: 'contact' }).then(function (token) {
                        clearTimeout(timeout);
                        tokenInput.value = token;
                        form.dataset.recaptchaReady = '1';
                        form.submit();
                    }).catch(function(err) {
                        clearTimeout(timeout);
                        console.warn('reCAPTCHA erro:', err);
                        form.dataset.recaptchaReady = '1';
                        form.submit();
                    });
                });
            });
        });
    </script>
@endif
@endpush
