@extends('layouts.app')

@section('title', 'Contato - UNN')

@section('content')
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

        // Safety: avoid javascript: payloads in admin-configured links.
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

        // If looks like a domain/path, prefix https://
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

    $recaptchaSiteKey = (string) config('services.recaptcha.site_key', '');
@endphp
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6">
                Fale <span class="text-gradient">Conosco</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Estamos aqui para ajudar. Entre em contato por qualquer um dos canais abaixo.
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            @if(session('error'))
                <div class="max-w-3xl mx-auto bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-8">
                    <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="max-w-3xl mx-auto bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-8">
                    <i class="fas fa-circle-check mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="grid lg:grid-cols-2 gap-8 md:gap-12">
                <!-- Contact Info -->
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
                                    {{ $companyAddress }}{{ $companyNumber ? ', '.$companyNumber : '' }}@if($companyComplement) - {{ $companyComplement }}@endif
                                </p>
                                <p class="text-gray-600">{{ $companyDistrict }}, {{ $companyCity }} - {{ $companyState }}</p>
                                <p class="text-gray-600">CEP: {{ $companyZip }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="mt-8 bg-white rounded-2xl p-6 shadow-lg text-center md:text-left">
                        @if(!empty($socialLinks))
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
                        @endif
                    </div>
                </div>

                <!-- Contact Form -->
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mensagem</label>
                            <textarea name="message" rows="5" required 
                                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition resize-none" 
                                      style="--tw-ring-color: var(--unn-azul-1)"
                                      placeholder="Como podemos ajudar?">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane"></i>
                            Enviar mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Nossa Localização</h2>
            <div class="rounded-3xl overflow-hidden shadow-2xl h-[400px]">
                <iframe 
                    src="https://www.openstreetmap.org/export/embed.html?bbox=-46.6600,-23.5650,-46.6500,-23.5550&layer=mapnik&marker=-23.5600,-46.6550"
                    class="w-full h-full border-0"
                    loading="lazy"
                    title="Localização UNN"
                ></iframe>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <x-faq-section context="contact" />
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

@push('scripts')
@php($recaptchaSiteKey = (string) config('services.recaptcha.site_key', ''))
@if($recaptchaSiteKey !== '')
    <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('contact-form');
            const tokenInput = document.getElementById('recaptcha_token');
            const siteKey = @json($recaptchaSiteKey);

            if (!form || !tokenInput || !siteKey || typeof grecaptcha === 'undefined') {
                return;
            }

            form.addEventListener('submit', function (e) {
                if (form.dataset.recaptchaReady === '1') return;

                e.preventDefault();
                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, { action: 'contact' }).then(function (token) {
                        tokenInput.value = token;
                        form.dataset.recaptchaReady = '1';
                        form.submit();
                    });
                });
            });
        });
    </script>
@endif
@endpush
