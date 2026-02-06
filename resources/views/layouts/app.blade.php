{{-- /**
* Sistema UNN - Layout principal
*
* Autor: George Marcelo (KDKHOST SOLUÇÕES)
* Telefone: +55 (21) 98132-5441
* Telegram: https://t.me/MARCELO_BRAD
*
* Copyright (c) 2026 Kdkhost Soluções. Todos os direitos reservados.
*
* AVISO LEGAL:
* Este software e seu código-fonte são propriedade intelectual de kdkhost soluções.
* É proibida a reprodução, distribuição, modificação, engenharia reversa ou uso não autorizado,
* total ou parcial, sem autorização prévia e por escrito.
*
* Contato: contato@kdkhost.com.br
* Licenciamento: Uso restrito conforme contrato/termos aplicáveis.
*/ --}}
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', \App\Models\Setting::get('seo_meta_title') ?: config('app.name', 'UNN'))</title>
    @php
        $logoFront = \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
        $logo = $logoFront ? asset(ltrim($logoFront, '/')) : asset('img/logo.svg');
        $faviconValue = \App\Models\Setting::get('favicon_image');
        $favicon = $faviconValue ? asset(ltrim($faviconValue, '/')) : asset('favicon.ico');
        $pwaEnabled = (string) \App\Models\Setting::get('pwa_enabled', '1') === '1';
        $pwaTheme = \App\Models\Setting::get('pwa_theme_color', '#1F5EDB');

        $seoDefaultTitle = \App\Models\Setting::get('seo_meta_title') ?: (\App\Models\Setting::get('app_name') ?: config('app.name', 'UNN'));
        $seoDefaultDescription = (string) (\App\Models\Setting::get('seo_meta_description') ?: '');
        $seoDefaultKeywords = (string) (\App\Models\Setting::get('seo_meta_keywords') ?: '');
        $seoRobots = (string) (\App\Models\Setting::get('seo_robots') ?: 'index,follow');
        $seoGoogleVerification = (string) (\App\Models\Setting::get('seo_google_verification') ?: '');

        $seoOgImageValue = (string) (\App\Models\Setting::get('seo_og_image') ?: '');
        $seoOgImage = $seoOgImageValue !== '' ? asset(ltrim($seoOgImageValue, '/')) : '';

        $seoTwitterImageValue = (string) (\App\Models\Setting::get('seo_twitter_image') ?: '');
        if ($seoTwitterImageValue === '') {
            $seoTwitterImageValue = $seoOgImageValue;
        }
        $seoTwitterImage = $seoTwitterImageValue !== '' ? asset(ltrim($seoTwitterImageValue, '/')) : '';

        $seoTwitterSite = (string) (\App\Models\Setting::get('seo_twitter_site') ?: '');

        $trackingHead = (string) (\App\Models\Setting::get('tracking_head') ?: '');
        $trackingBody = (string) (\App\Models\Setting::get('tracking_body') ?: '');

        $pageTitle = trim($__env->yieldContent('title'));
        if ($pageTitle === '') {
            $pageTitle = $seoDefaultTitle;
        }

        $metaTitle = trim($__env->yieldContent('meta_title'));
        if ($metaTitle === '') {
            $metaTitle = $pageTitle;
        }

        $metaDescription = trim($__env->yieldContent('meta_description'));
        if ($metaDescription === '') {
            $metaDescription = $seoDefaultDescription;
        }

        $metaKeywords = trim($__env->yieldContent('meta_keywords'));
        if ($metaKeywords === '') {
            $metaKeywords = $seoDefaultKeywords;
        }

        $metaImage = trim($__env->yieldContent('meta_image'));
        if ($metaImage === '') {
            $metaImage = $seoOgImage;
        }

        $canonical = trim($__env->yieldContent('canonical'));
        if ($canonical === '') {
            $canonical = url()->current();
        }
    @endphp

    @if($metaDescription !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if($metaKeywords !== '')
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    <meta name="robots" content="{{ trim($__env->yieldContent('meta_robots')) ?: $seoRobots }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $metaTitle }}">
    @if($metaDescription !== '')
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:type" content="{{ trim($__env->yieldContent('og_type')) ?: 'website' }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ \App\Models\Setting::get('app_name') ?: config('app.name', 'UNN') }}">
    @if($metaImage !== '')
        <meta property="og:image" content="{{ $metaImage }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ trim($__env->yieldContent('twitter_card')) ?: 'summary_large_image' }}">
    @if($seoTwitterSite !== '')
        <meta name="twitter:site" content="{{ $seoTwitterSite }}">
    @endif
    <meta name="twitter:title" content="{{ $metaTitle }}">
    @if($metaDescription !== '')
        <meta name="twitter:description" content="{{ $metaDescription }}">
    @endif
    @if($seoTwitterImage !== '')
        <meta name="twitter:image" content="{{ trim($__env->yieldContent('twitter_image')) ?: $seoTwitterImage }}">
    @endif

    @if($seoGoogleVerification !== '')
        <meta name="google-site-verification" content="{{ $seoGoogleVerification }}">
    @endif

    @if($trackingHead !== '')
        {!! $trackingHead !!}
    @endif

    <link rel="icon" href="{{ $favicon }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ $logo }}">
    @if ($pwaEnabled)
        <link rel="manifest" href="{{ route('manifest') }}">
    @endif
    <meta name="theme-color" content="{{ $pwaTheme }}">

    <!-- Fonts & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --unn-azul-1: #1F5EDB;
            /* principal */
            --unn-azul-2: #177FD6;
            /* secundário */
            --unn-azul-3: #1D3FC4;
            /* escuro */
            --unn-card: #ffffff;
            --unn-text: #0f172a;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-2) 50%, var(--unn-azul-3) 100%);
            color: #fff;
            border: none;
        }

        html,
        body {
            overflow-x: hidden;
            max-width: 100vw;
        }

        body {
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 40%);
            color: var(--unn-text);
        }

        /* Responsive fixes */
        img,
        video,
        iframe {
            max-width: 100%;
            height: auto;
        }

        .max-w-7xl,
        .max-w-6xl,
        .max-w-5xl,
        .max-w-4xl {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (max-width: 640px) {
            .text-5xl {
                font-size: 2rem;
                line-height: 1.2;
            }

            .text-6xl {
                font-size: 2.25rem;
                line-height: 1.2;
            }

            .text-4xl {
                font-size: 1.75rem;
                line-height: 1.3;
            }

            .px-10 {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen">
    @if($trackingBody !== '')
        {!! $trackingBody !!}
    @endif
    {{-- Banner de Impersonation --}}
    @if(session()->has('impersonator_id'))
        <div
            class="bg-yellow-400 text-yellow-900 px-4 py-2 text-center text-sm font-bold flex justify-center items-center gap-4 fixed w-full top-0 z-[100]">
            <span><i class="fas fa-user-secret mr-1"></i> Acessando como: {{ auth()->user()->name }}</span>
            <a href="{{ route('admin.impersonate.stop') }}"
                class="bg-yellow-900 text-yellow-100 px-3 py-1 rounded hover:bg-yellow-800 transition text-xs">
                Voltar ao Admin
            </a>
        </div>
        <div class="h-10"></div>
    @endif

    @php $showNavigation = $showNavigation ?? true; @endphp
    @if($showNavigation)
        @include('partials.header')
    @endif

    <main class="{{ $showNavigation ? 'pt-20 lg:pt-24' : 'pt-0' }} min-h-[calc(100vh-80px)]">
        @yield('content')
    </main>

    @includeWhen(true, 'partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/zxcvbn@4.4.2/dist/zxcvbn.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Input masks
            var imasks = document.querySelectorAll('input[data-mask]');
            imasks.forEach(function (el) {
                var m = el.getAttribute('data-mask');
                Inputmask(m).mask(el);
            });

            // CEP auto-complete on any input with id=cep
            var cep = document.getElementById('cep');
            if (cep) {
                cep.addEventListener('input', function (e) {
                    var v = e.target.value.replace(/\D/g, '');
                    if (v.length === 8) {
                        fetch(`https://viacep.com.br/ws/${v}/json/`).then(r => r.json()).then(data => {
                            if (!data.erro) {
                                var address = document.getElementById('address');
                                if (address) {
                                    address.value = `${data.logradouro} - ${data.bairro} - ${data.localidade}/${data.uf}`;
                                    var campos = Array.prototype.slice.call(document.querySelectorAll('input,select,textarea'));
                                    var idx = campos.indexOf(address);
                                    if (idx > -1 && campos[idx + 1]) {
                                        campos[idx + 1].focus();
                                    }
                                }
                            }
                        });
                    }
                });
            }

            // Password strength indicator
            var pwd = document.getElementById('password');
            var strengthWrap = document.getElementById('pw-strength');
            var strength = strengthWrap ? strengthWrap.querySelector('span') : null;
            if (pwd && strength) {
                pwd.addEventListener('input', function () {
                    var score = (typeof zxcvbn === 'function') ? zxcvbn(pwd.value).score : 0;
                    var texts = ['Muito fraca', 'Fraca', 'OK', 'Boa', 'Forte'];
                    strength.textContent = texts[score];
                });
            }

            // Mobile menu toggle
            var mobileToggle = document.getElementById('mobile-menu-toggle');
            var mobileMenu = document.getElementById('mobile-menu');
            var mobilePanel = document.getElementById('mobile-menu-panel');
            var mobileOverlay = document.getElementById('mobile-menu-overlay');
            var mobileClose = document.getElementById('mobile-menu-close');
            if (mobileToggle && mobileMenu && mobilePanel && mobileOverlay && mobileClose) {
                var openMenu = function () {
                    mobileMenu.classList.remove('hidden');
                    mobileMenu.setAttribute('aria-hidden', 'false');
                    mobileOverlay.classList.remove('pointer-events-none');
                    setTimeout(function () {
                        mobileOverlay.classList.add('opacity-100');
                        mobilePanel.classList.remove('-translate-x-full');
                    }, 20);
                };
                var closeMenu = function () {
                    mobileOverlay.classList.remove('opacity-100');
                    mobilePanel.classList.add('-translate-x-full');
                    mobileOverlay.classList.add('pointer-events-none');
                    setTimeout(function () {
                        mobileMenu.classList.add('hidden');
                        mobileMenu.setAttribute('aria-hidden', 'true');
                    }, 400);
                };
                mobileToggle.addEventListener('click', openMenu);
                mobileClose.addEventListener('click', closeMenu);
                mobileOverlay.addEventListener('click', closeMenu);
            }
        });

        // Global Notifications Polling
        @auth
            window.refreshNotifications = function() {
                fetch('{{ route("connection.notifications") }}')
                    .then(r => r.json())
                    .then(data => {
                        const badge = document.getElementById('connection-notification-count');
                        if (badge) {
                            if (data.count > 0) {
                                badge.textContent = data.count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    });
            };
            setInterval(window.refreshNotifications, 15000);
            window.refreshNotifications();
        @endauth
    </script>

    @stack('scripts')

    @if ($pwaEnabled)
        <script>
                    if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function () { console.log('Service Worker registrado'); })
                    .catch(function (err) { console.error('SW erro:', err); });
            }

            let deferredPrompt;

            const showInstallModal = () => {
                if (document.getElementById('pwa-install-modal')) return;

                const modal = document.createElement('div');
                modal.id = 'pwa-install-modal';
                modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm animate-fade-in';

                modal.innerHTML = `
                                <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center relative transform transition-all scale-100">
                                    <div class="flex justify-center mb-6">
                                        <img src="{{ $logo }}" alt="Logo" class="h-16 object-contain">
                                    </div>

                                    <h3 class="text-xl font-bold text-slate-900 mb-3">Instale nosso aplicativo!</h3>
                                    <p class="text-slate-600 text-sm mb-8 leading-relaxed">
                                        Tenha acesso mais rápido e use mesmo offline! Instale nosso app diretamente na sua tela inicial.
                                    </p>

                                    <div class="flex flex-col gap-3">
                                        <button id="pwa-install-btn" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:translate-y-[-2px] transition-all">
                                            Instalar Agora
                                        </button>
                                        <button id="pwa-dismiss-btn" class="w-full py-3 px-4 bg-slate-100 text-slate-600 font-medium rounded-xl hover:bg-slate-200 transition-colors">
                                            Mais tarde
                                        </button>
                                    </div>
                                </div>
                            `;

                document.body.appendChild(modal);

                // Animação de entrada
                requestAnimationFrame(() => {
                    modal.querySelector('div').classList.add('scale-100');
                    modal.querySelector('div').classList.remove('scale-95');
                });

                document.getElementById('pwa-install-btn').addEventListener('click', async () => {
                    if (!deferredPrompt) return;
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('User response to the install prompt: ' + outcome);
                    deferredPrompt = null;
                    removeModal();
                });

                document.getElementById('pwa-dismiss-btn').addEventListener('click', () => {
                    removeModal();
                    // Opcional: Salvar em cookie/localStorage para não mostrar novamente por X dias
                });

                function removeModal() {
                    modal.classList.add('opacity-0');
                    setTimeout(() => modal.remove(), 300);
                }
            };

            window.showInstallModal = showInstallModal;

            window.addEventListener('beforeinstallprompt', (e) => {
                // Impede que o Chrome mostre o prompt nativo automaticamente (para mobile principalmente)
                e.preventDefault();
                // Guarda o evento para acionar depois
                deferredPrompt = e;

                // Mostra o modal customizado
                // Pequeno delay para garantir que a página carregou
                setTimeout(showInstallModal, 2000);
            });
        </script>
    @endif
</body>

</html>
