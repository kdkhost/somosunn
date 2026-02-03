{{-- /**
 * Sistema UNN - Layout principal
 *
 * Autor: George Marcelo (KDKHOST SOLUÃ‡Ã•ES)
 * Telefone: +55 (21) 98132-5441
 * Telegram: https://t.me/MARCELO_BRAD
 *
 * Copyright (c) 2026 Kdkhost Soluções. Todos os direitos reservados.
 *
 * AVISO LEGAL:
 * Este software e seu código-fonte sÃ£o propriedade intelectual de kdkhost soluções.
 * É proibida a reproduÃ§Ã£o, distribuiÃ§Ã£o, modificação, engenharia reversa ou uso não autorizado,
 * total ou parcial, sem autorização prÃ©via e por escrito.
 *
 * Contato: contato@kdkhost.com.br
 * Licenciamento: Uso restrito conforme contrato/termos aplicáveis.
 */ --}}
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'UNN'))</title>
    @php
        $logoFront = \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
        $logo = $logoFront ? asset(ltrim($logoFront, '/')) : asset('img/logo.svg');
        $faviconValue = \App\Models\Setting::get('favicon_image');
        $favicon = $faviconValue ? asset(ltrim($faviconValue, '/')) : asset('favicon.ico');
        $pwaEnabled = (string)\App\Models\Setting::get('pwa_enabled', '1') === '1';
        $pwaTheme = \App\Models\Setting::get('pwa_theme_color', '#1F5EDB');
    @endphp
    <link rel="icon" href="{{ $favicon }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ $logo }}">
    <meta name="theme-color" content="{{ $pwaTheme }}">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')

    <style>
        * { font-family: 'Inter', sans-serif; }
        :root{
            --unn-azul-1:#1F5EDB; /* principal */
            --unn-azul-2:#177FD6; /* secundário */
            --unn-azul-3:#1D3FC4; /* escuro */
            --unn-card:#ffffff;
            --unn-text:#0f172a;
        }
        .btn-primary{
            background: linear-gradient(135deg,var(--unn-azul-1) 0%,var(--unn-azul-2) 50%,var(--unn-azul-3) 100%);
            color:#fff;
            border: none;
        }
        body {
            background: linear-gradient(180deg,#f8fbff 0%, #ffffff 40%);
            color: var(--unn-text);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

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

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            // Input masks
            var imasks = document.querySelectorAll('input[data-mask]');
            imasks.forEach(function(el){
                var m = el.getAttribute('data-mask');
                Inputmask(m).mask(el);
            });

            // CEP auto-complete on any input with id=cep
            var cep = document.getElementById('cep');
            if(cep){
                cep.addEventListener('input', function(e){
                var v = e.target.value.replace(/\D/g,'');
                if(v.length === 8){
                    fetch(`https://viacep.com.br/ws/${v}/json/`).then(r=>r.json()).then(data=>{
                        if(!data.erro){
                            var address = document.getElementById('address');
                            if(address){
                                address.value = `${data.logradouro} - ${data.bairro} - ${data.localidade}/${data.uf}`;
                                var campos = Array.prototype.slice.call(document.querySelectorAll('input,select,textarea'));
                                var idx = campos.indexOf(address);
                                if(idx > -1 && campos[idx + 1]) {
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
            if(pwd && strength){
                pwd.addEventListener('input', function(){
                    var score = (typeof zxcvbn === 'function') ? zxcvbn(pwd.value).score : 0;
                    var texts = ['Muito fraca','Fraca','OK','Boa','Forte'];
                    strength.textContent = texts[score];
                });
            }

            // Mobile menu toggle
            var mobileToggle = document.getElementById('mobile-menu-toggle');
            var mobileMenu = document.getElementById('mobile-menu');
            var mobilePanel = document.getElementById('mobile-menu-panel');
            var mobileOverlay = document.getElementById('mobile-menu-overlay');
            var mobileClose = document.getElementById('mobile-menu-close');
            if(mobileToggle && mobileMenu && mobilePanel && mobileOverlay && mobileClose){
                var openMenu = function(){
                    mobileMenu.classList.remove('hidden');
                    mobileMenu.setAttribute('aria-hidden','false');
                    mobileOverlay.classList.remove('pointer-events-none');
                    setTimeout(function(){
                        mobileOverlay.classList.add('opacity-100');
                        mobilePanel.classList.remove('-translate-x-full');
                    }, 20);
                };
                var closeMenu = function(){
                    mobileOverlay.classList.remove('opacity-100');
                    mobilePanel.classList.add('-translate-x-full');
                    mobileOverlay.classList.add('pointer-events-none');
                    setTimeout(function(){
                        mobileMenu.classList.add('hidden');
                        mobileMenu.setAttribute('aria-hidden','true');
                    }, 400);
                };
                mobileToggle.addEventListener('click', openMenu);
                mobileClose.addEventListener('click', closeMenu);
                mobileOverlay.addEventListener('click', closeMenu);
            }
        });
    </script>

    @stack('scripts')

    @if ($pwaEnabled)
        <link rel="manifest" href="/manifest.webmanifest">
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function(){ console.log('Service Worker registrado'); })
                    .catch(function(err){ console.error('SW erro:', err); });
            }

            let deferredPrompt;
            const showInstallBanner = () => {
                if (document.getElementById('pwa-install-banner')) return;
                const banner = document.createElement('div');
                banner.id = 'pwa-install-banner';
                banner.className = 'fixed bottom-6 right-6 bg-white border border-slate-200 shadow-xl rounded-2xl px-5 py-4 flex items-center gap-4';
                banner.style.zIndex = 9999;
                banner.innerHTML = `
                    <div class="text-sm">
                        <p class="font-semibold text-slate-800">Instalar aplicativo UNN</p>
                        <p class="text-slate-500">Adicione na sua tela inicial em segundos.</p>
                    </div>
                    <button id="pwa-install-btn" class="btn-primary px-4 py-2 rounded-full text-sm font-semibold">Instalar</button>
                `;
                document.body.appendChild(banner);
                document.getElementById('pwa-install-btn').addEventListener('click', async () => {
                    if (!deferredPrompt) return;
                    deferredPrompt.prompt();
                    await deferredPrompt.userChoice;
                    deferredPrompt = null;
                    banner.remove();
                });
            };

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                showInstallBanner();
            });
        </script>
    @endif
</body>
</html>
