@extends('layouts.app')

@section('title', 'Contato - UNN')

@php
    $companyName = \App\Models\Setting::get('company_name') ?: 'UNN';
    $companyEmail = \App\Models\Setting::get('company_email') ?: 'contato@somosunn.com.br';
    $companyPhone = \App\Models\Setting::get('company_phone') ?: '(11) 99999-9999';
    $companyZip = \App\Models\Setting::get('company_zip') ?: '01310-100';
    $companyAddress = \App\Models\Setting::get('company_address') ?: 'Av. Paulista, 1000';
    $companyNumber = (string) \App\Models\Setting::get('company_number', '1001');
    $companyComplement = \App\Models\Setting::get('company_complement') ?: null;
    $companyDistrict = \App\Models\Setting::get('company_district') ?: 'Bela Vista';
    $companyCity = \App\Models\Setting::get('company_city') ?: 'São Paulo';
    $companyState = \App\Models\Setting::get('company_state') ?: 'SP';

    $normalizeSocialUrl = function ($value, string $network): ?string {
        $value = trim((string) $value);
        if ($value === '' || $value === '#') {
            return null;
        }

        if (preg_match('/^\s*javascript\s*:/i', $value)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        if ($network === 'instagram' && str_starts_with($value, '@')) {
            return 'https://instagram.com/' . ltrim($value, '@');
        }

        if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}/i', $value)) {
            return 'https://' . $value;
        }

        return $value;
    };

    $footerInstagram = \App\Models\SiteContent::getValue('footer', 'instagram_url');
    $footerFacebook = \App\Models\SiteContent::getValue('footer', 'facebook_url');
    $footerYoutube = \App\Models\SiteContent::getValue('footer', 'youtube_url');
    $footerLinkedin = \App\Models\SiteContent::getValue('footer', 'linkedin_url');

    $socialInstagram = $normalizeSocialUrl($footerInstagram ?: \App\Models\Setting::get('social_instagram'), 'instagram');
    $socialFacebook = $normalizeSocialUrl($footerFacebook ?: \App\Models\Setting::get('social_facebook'), 'facebook');
    $socialYoutube = $normalizeSocialUrl($footerYoutube ?: \App\Models\Setting::get('social_youtube'), 'youtube');
    $socialLinkedin = $normalizeSocialUrl($footerLinkedin ?: \App\Models\Setting::get('social_linkedin'), 'linkedin');

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

    $mapQuery = $fullAddress !== '' ? rawurlencode($fullAddress) : rawurlencode('São Paulo, Brasil');
    $mapOpenUrl = 'https://www.google.com/maps/search/?api=1&query=' . $mapQuery;
    $recaptchaSiteKey = (string) (\App\Models\Setting::get('recaptcha_v3_site_key') ?: config('services.recaptcha.site_key', ''));
@endphp

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 unn-title-gradient unn-title-max">
                Fale <span class="text-gradient">Conosco</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Estamos aqui para ajudar. Entre em contato por qualquer um dos canais abaixo.
            </p>
        </div>
    </section>

    <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            @if(session('error'))
                <div class="max-w-3xl mx-auto bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-8">
                    <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="grid lg:grid-cols-2 gap-8 md:gap-12">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 mb-8">Informações de Contato</h2>

                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
                            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas fa-envelope text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">E-mail</h3>
                                <p class="text-gray-600">{{ $companyEmail }}</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
                            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fab fa-whatsapp text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">WhatsApp</h3>
                                <p class="text-gray-600">{{ $companyPhone }}</p>
                                <p class="text-sm text-gray-500">Seg-Sex, 9h às 18h</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center md:items-start gap-4 text-center md:text-left">
                            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas fa-map-marker-alt text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">Endereço</h3>
                                <p class="text-gray-600">
                                    {{ $companyAddress }}{{ $companyNumber ? ', ' . $companyNumber : '' }}@if($companyComplement) - {{ $companyComplement }}@endif
                                </p>
                                <p class="text-gray-600">{{ $companyDistrict }}, {{ $companyCity }} - {{ $companyState }}</p>
                                <p class="text-gray-600">CEP: {{ $companyZip }}</p>
                            </div>
                        </div>
                    </div>

                    @if(!empty($socialLinks))
                        <div class="mt-8 bg-white rounded-2xl p-6 shadow-lg text-center md:text-left">
                            <h3 class="font-bold text-gray-900 mb-4">Redes Sociais</h3>
                            <div class="flex gap-4 justify-center md:justify-start flex-wrap">
                                @foreach($socialLinks as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                        class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center text-white hover:shadow-lg transition"
                                        aria-label="{{ $link['title'] }}">
                                        <i class="{{ $link['icon'] }} text-xl"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl">
                    <h2 class="text-2xl font-black text-gray-900 mb-6">Envie uma mensagem</h2>

                    <form id="contact-form" action="{{ route('contato.send') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nome completo</label>
                            <input type="text" name="name" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                                style="--tw-ring-color: var(--unn-azul-1)"
                                placeholder="Seu nome" value="{{ old('name') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
                            <input type="email" name="email" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                                style="--tw-ring-color: var(--unn-azul-1)"
                                placeholder="seu@email.com" value="{{ old('email') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Telefone</label>
                            <input type="tel" name="phone"
                                data-mask="(99) 9999[9]-9999"
                                inputmode="tel"
                                autocomplete="tel"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                                style="--tw-ring-color: var(--unn-azul-1)"
                                placeholder="(00) 00000-0000" value="{{ old('phone') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Assunto</label>
                            <select name="subject" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition"
                                style="--tw-ring-color: var(--unn-azul-1)">
                                <option value="">Selecione um assunto</option>
                                <option value="duvidas" {{ old('subject') === 'duvidas' ? 'selected' : '' }}>Dúvidas sobre a plataforma</option>
                                <option value="parcerias" {{ old('subject') === 'parcerias' ? 'selected' : '' }}>Propostas de parceria</option>
                                <option value="suporte" {{ old('subject') === 'suporte' ? 'selected' : '' }}>Suporte técnico</option>
                                <option value="comercial" {{ old('subject') === 'comercial' ? 'selected' : '' }}>Departamento comercial</option>
                                <option value="imprensa" {{ old('subject') === 'imprensa' ? 'selected' : '' }}>Assessoria de imprensa</option>
                                <option value="outro" {{ old('subject') === 'outro' ? 'selected' : '' }}>Outro assunto</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mensagem <span class="text-gray-400 font-normal">(mínimo 10 caracteres)</span></label>
                            <textarea name="message" id="contact-message" rows="5" required minlength="10" maxlength="4000"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition resize-none"
                                style="--tw-ring-color: var(--unn-azul-1)"
                                placeholder="Como podemos ajudar? (mínimo 10 caracteres)">{{ old('message') }}</textarea>
                            <div class="flex justify-between mt-1 text-xs text-gray-500">
                                <span id="char-counter" class="text-red-500">0/10 caracteres</span>
                                <span id="char-max">Máximo: 4000</span>
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" disabled
                            class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane"></i>
                            Enviar mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Nossa Localização</h2>
            <div class="rounded-3xl overflow-hidden shadow-2xl h-[400px]">
                <div id="contact-location-map"
                    class="w-full h-full"
                    data-address="{{ $fullAddress }}"
                    data-map-url="{{ $mapOpenUrl }}"
                    data-lat="{{ $mapCoordinates['lat'] ?? '' }}"
                    data-lng="{{ $mapCoordinates['lng'] ?? '' }}"
                    aria-label="Localização UNN"></div>
            </div>
            <div class="mt-5 flex justify-center">
                <a href="{{ $mapOpenUrl }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-xl px-5 py-3 font-semibold text-white btn-primary shadow-lg hover:shadow-xl transition">
                    <i class="fas fa-location-arrow"></i>
                    Abrir no Google Maps
                </a>
            </div>
        </div>
    </section>

    <x-faq-section context="contact" />
</div>

<style>
.leaflet-container {
    width: 100%;
    height: 100%;
    background: #e2e8f0;
}

.contact-map-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
    padding: 24px;
    text-align: center;
    color: #475569;
    background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
}

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

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mapEl = document.getElementById('contact-location-map');

        if (!mapEl) {
            return;
        }

        const address = (mapEl.dataset.address || '').trim();
        const externalUrl = (mapEl.dataset.mapUrl || '').trim();
        const lat = parseFloat(mapEl.dataset.lat || '');
        const lng = parseFloat(mapEl.dataset.lng || '');

        const renderFallback = function (message) {
            mapEl.innerHTML = `
                <div class="contact-map-fallback">
                    <i class="fas fa-map-marked-alt text-4xl" style="color: var(--unn-azul-1)"></i>
                    <p class="font-semibold">${message}</p>
                </div>
            `;
        };

        if (!address || typeof L === 'undefined') {
            renderFallback('Não foi possível carregar o mapa agora.');
            return;
        }

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            renderFallback('Não foi possível localizar o endereço no mapa.');
            return;
        }

        const map = L.map(mapEl, {
            scrollWheelZoom: false
        }).setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup(address)
            .openPopup();

        if (externalUrl) {
            mapEl.style.cursor = 'pointer';
            mapEl.addEventListener('click', function () {
                window.open(externalUrl, '_blank', 'noopener');
            });
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
                if (form.dataset.recaptchaReady === '1') {
                    return;
                }

                e.preventDefault();

                const timeout = setTimeout(function () {
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
                    }).catch(function (err) {
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
