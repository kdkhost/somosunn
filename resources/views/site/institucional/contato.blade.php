@extends('layouts.app')

@php
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

@section('title', \App\Models\SiteContent::getValue('institucional_contato', 'title', 'Contato - UNN'))

@section('content')
@php
    $fallbackBody = view('site.institucional._fallback.contato')->render();

    $html = app(\App\Services\Site\SitePageContentService::class)->render('institucional_contato', 'body', $fallbackBody, [
        'CONTACT_ALERTS' => view('site.institucional.partials.contact-alerts')->render(),
        'CONTACT_INFO' => view('site.institucional.partials.contact-info', [
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
        ])->render(),
        'CONTACT_FORM' => view('site.institucional.partials.contact-form')->render(),
        'CONTACT_MAP_EMBED_URL' => e($mapEmbedUrl),
        'FAQ_SECTION' => view('components.faq-section', ['context' => 'contact'])->render(),
    ]);
@endphp

{!! $html !!}
@endsection

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

